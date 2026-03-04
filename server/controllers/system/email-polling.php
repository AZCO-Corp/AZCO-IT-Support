<?php
use Respect\Validation\Validator as DataValidator;

class EmailPollingController extends Controller {
    const PATH = '/email-polling';
    const METHOD = 'POST';

    private $mailbox;

    public function validations() {
        return [
            'permission' => 'any',
            'requestData' => [
                'token' => [
                    'validation' => DataValidator::notBlank()->length(LengthConfig::MIN_LENGTH_TOKEN, LengthConfig::MAX_LENGTH_TOKEN),
                    'error' => ERRORS::INVALID_TOKEN
                ]
            ]
        ];
    }

    public function handler() {
        $commentController = new CommentController();
        $createController = new CreateController();
        $defaultLanguage = Setting::getSetting('language')->getValue();
        $defaultDepartmentId = Department::getAll()->first()->id;


        if(Controller::request('token') !== Setting::getSetting('imap-token')->getValue())
            throw new RequestException(ERRORS::INVALID_TOKEN);


        $this->mailbox = new \PhpImap\Mailbox(
            Setting::getSetting('imap-host')->getValue(),
            Setting::getSetting('imap-user')->getValue(),
            Setting::getSetting('imap-pass')->getValue(),
            'files/'
        );

        $errors = [];
        $emails = $this->getLastEmails();

        $session = Session::getInstance();
        $oldSession = [
            'userId' => $session->getUserId(),
            'staff' => $session->getToken(),
            'token' => $session->isStaffLogged(),
        ];

        foreach($emails as $email) {
            Controller::setDataRequester(function ($key) use ($email, $defaultDepartmentId, $defaultLanguage) {
                switch ($key) {
                    case 'ticketNumber':
                        return $email->getTicketNumber();
                    case 'title':
                        return $email->getSubject();
                    case 'content':
                        return $email->getContent();
                    case 'departmentId':
                        return $defaultDepartmentId;
                    case 'language':
                        return $defaultLanguage;
                    case 'email':
                        return $email->getSender();
                    case 'name':
                        return $email->getSenderName();
                }

                return null;
            });
            // Handle email attachment by copying directly to files/ dir
            // (can't use $_FILES/uploadFile because is_uploaded_file() rejects non-HTTP files)
            $emailAttachmentName = null;
            if($email->getAttachment()) {
                $attachment = $email->getAttachment();
                $ext = pathinfo($attachment->name, PATHINFO_EXTENSION);
                $baseName = pathinfo($attachment->name, PATHINFO_FILENAME);
                $baseName = preg_replace('/[^a-zA-Z0-9\.\-]/', '_', strtolower($baseName));
                $prefix = $email->isReply() ? 't' . $email->getTicket()->ticketNumber : 't0';
                $emailAttachmentName = $prefix . '_' . substr(Hashing::generateRandomToken(), 0, 6) . '_' . $baseName . '.' . $ext;
                rename($attachment->filePath, 'files/' . $emailAttachmentName);
            }

            // Skip emails from the system itself
            if(strtolower($email->getSender()) === strtolower(Setting::getSetting("server-email")->getValue())) {
                continue;
            }

            // Skip blacklisted emails (bounce notifications, etc.)
            if($this->isBlacklisted($email)) {
                continue;
            }

            try {
                if($email->isReply()) {
                    $ticketAuthor = $email->getTicket()->authorToArray();
                    if($ticketAuthor['email'] === $email->getSender()) {
                        $session->clearSessionData();
                        $session->createSession($ticketAuthor['id'],
                                                $ticketAuthor['staff'],
                                                $email->getTicket()->ticketNumber);

                        $commentController->handler();

                        // Attach email file to the comment just created
                        if($emailAttachmentName) {
                            $ticketBean = Ticket::getByTicketNumber($email->getTicketNumber());
                            \RedBeanPHP\R::exec(
                                "UPDATE ticketevent SET file = ? WHERE ticket_id = ? ORDER BY id DESC LIMIT 1",
                                [$emailAttachmentName, $ticketBean->id]
                            );
                        }
                    }
                } else {
                    $createController->handler();

                    // Attach email file to the newly created ticket
                    if($emailAttachmentName) {
                        $newTicket = Ticket::getByTicketNumber($email->getTicketNumber());
                        if($newTicket && !$newTicket->isNull()) {
                            $newTicket->file = $emailAttachmentName;
                            $newTicket->store();
                        }
                    }
                }
            } catch(\Exception $e) {
                $errors[] = [
                    'author' => $email->getSender(),
                    'ticketNumber' => $email->getTicketNumber(),
                    'error' => $e->__toString(),
                ];
            }

        }

        $session->clearSessionData();
        $session->setSessionData($oldSession);

        $this->eraseAllEmails();

        if(count($errors)) {
            Response::respondError(ERRORS::EMAIL_POLLING, null, $errors);
        } else {
            Response::respondSuccess();
        }
    }


    private function isBlacklisted($email) {
        $blacklist = include __DIR__ . "/email-blacklist.php";

        $sender = strtolower($email->getSender());
        foreach ($blacklist["senders"] as $blocked) {
            if (strpos($sender, strtolower($blocked)) !== false) {
                return true;
            }
        }

        $subject = $email->getSubject();
        foreach ($blacklist["subject_patterns"] as $pattern) {
            if (preg_match($pattern, $subject)) {
                return true;
            }
        }

        return false;
    }

    public function getLastEmails() {
        $mailsIds = $this->mailbox->searchMailbox('ALL');
        $emails = [];
        sort($mailsIds);

        foreach($mailsIds as $mailId) {
            $mail = $this->mailbox->getMail($mailId);
            $mailHeader = $this->mailbox->getMailHeader($mailId);
            $mailAttachment = count($mail->getAttachments()) ? current($mail->getAttachments()) : null;

            $emails[] = new Email([
                'fromAddress' => $mailHeader->fromAddress,
                'fromName' => $mailHeader->fromName,
                'subject' => $mailHeader->subject,
                'content' => $mail->textPlain,
                'file' => $mailAttachment,
            ]);

            // Clean up non-primary attachments
            foreach($mail->getAttachments() as $attachment) {
                if($attachment !== $mailAttachment) {
                    unlink($attachment->filePath);
                }
            }
        }

        return $emails;
    }

    public function eraseAllEmails() {
        $mailsIds = $this->mailbox->searchMailbox('ALL');

        foreach($mailsIds as $mailId) {
            $this->mailbox->deleteMail($mailId);
        }

        $this->mailbox->expungeDeletedMails();
    }
}
