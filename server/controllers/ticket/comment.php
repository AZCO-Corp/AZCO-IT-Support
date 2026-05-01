<?php
use Respect\Validation\Validator as DataValidator;
DataValidator::with('CustomValidations', true);

/**
 * @api {post} /ticket/comment Comment ticket
 * @apiVersion 4.11.0
 *
 * @apiName Comment ticket
 *
 * @apiGroup Ticket
 *
 * @apiDescription This path comments a ticket.
 *
 * @apiPermission user
 *
 * @apiParam {String} content Content of the comment.
 * @apiParam {Number} ticketNumber The number of the ticket to comment.
 * @apiParam {Boolean} private Indicates if the comment is not shown to users.
 * @apiParam {Number} images The number of images in the content.
 * @apiParam {String} apiKey apiKey to comment a ticket.
 * @apiParam image_i The image file of index `i` (mutiple params accepted)
 * @apiParam file The file you with to upload.
 *
 * @apiUse NO_PERMISSION
 * @apiUse INVALID_CONTENT
 * @apiUse INVALID_TICKET
 * @apiUse INVALID_FILE
 *
 * @apiSuccess {Object} data Empty object
 *
 */

class CommentController extends Controller {
    const PATH = '/comment';
    const METHOD = 'POST';

    private $ticket;
    private $content;
    private $session;
    private $imagePaths;

    public function validations() {
        $validations = [
            'permission' => 'user',
            'requestData' => [
                'content' => [
                    'validation' => DataValidator::content(),
                    'error' => ERRORS::INVALID_CONTENT
                ],
                'ticketNumber' => [
                    'validation' => DataValidator::validTicketNumber(),
                    'error' => ERRORS::INVALID_TICKET
                ]
            ]
        ];
        if(Controller::request('captcha')){
            $validations['permission'] = 'any';
            $validations['requestData']['captcha'] = [
                'validation' => DataValidator::captcha(APIKey::TICKET_CREATE_PERMISSION),
                'error' => ERRORS::INVALID_CAPTCHA
            ];
        }

        return $validations;
    }

    public function handler() {
        $ticketNumber = Controller::request('ticketNumber');
        $this->ticket = Ticket::getByTicketNumber($ticketNumber);
        $this->content = Controller::request('content', true);
        $this->user = Controller::getLoggedUser();

        $ticketAuthor = $this->ticket->authorToArray();
        $isAuthor = $this->ticket->isAuthor($this->user);
        $isOwner = $this->ticket->isOwner($this->user);
        $private = Controller::request('private');
        $apiKey = APIKey::getDataStore(Controller::request('apiKey'), 'token');

        if(!$this->user->canManageTicket($this->ticket)) {
            throw new RequestException(ERRORS::NO_PERMISSION);
        }

        $this->storeComment();

        if(!$isAuthor && !$private) {
            $this->sendMail($ticketAuthor);
        }

        if($this->ticket->owner && !$isOwner) {
            $this->sendMail([
                'email' => $this->ticket->owner->email,
                'name' => $this->ticket->owner->name,
                'staff' => true
            ], $private);
        }

        $this->notifyBcc($private);

        Log::createLog('COMMENT', $this->ticket->ticketNumber);

        Response::respondSuccess();
    }

    private function storeComment() {
        $fileUploader = FileUploader::getInstance();
        $fileUploader->setPermission(FileManager::PERMISSION_TICKET, $this->ticket->ticketNumber);
        $fileUploader = $this->uploadFile(Controller::isStaffLogged());

        $comment = Ticketevent::getEvent(Ticketevent::COMMENT);
        $comment->setProperties(array(
            'content' => $this->replaceWithImagePaths($this->getImagePaths(), $this->content),
            'file' => ($fileUploader instanceof FileUploader) ? $fileUploader->getFileName() : null,
            'date' => Date::getCurrentDate(),
            'private' => (Controller::isStaffLogged() && Controller::request('private')) ? 1 : 0
        ));

        if(Controller::isStaffLogged()) {
            $this->ticket->unread = !$this->ticket->isAuthor($this->user);
            $this->ticket->unreadStaff = !$this->ticket->isOwner($this->user);
            $comment->authorStaff = $this->user;
        } else {
            $this->ticket->unreadStaff = true;
            $comment->authorUser = $this->user;
        }

        $this->ticket->addEvent($comment);
        $this->ticket->store();
    }

    private function sendMail($recipient, $isPrivate = false) {
        $mailSender = MailSender::getInstance();

        $email = $recipient['email'];
        $name = $recipient['name'];
        $isStaff = array_key_exists('staff', $recipient) && $recipient['staff'];

        $url = Setting::getSetting('url')->getValue();

        if(!Controller::isLoginMandatory() && !$isStaff){
            $url .= '/check-ticket/' . $this->ticket->ticketNumber;
            $url .= '/' . $email;
        }

        $rawContent = $this->replaceWithImagePaths($this->getImagePaths(), $this->content);
        $content = $isPrivate ? $this->wrapPrivateContent($rawContent) : $rawContent;

        $mailSender->setTemplate(MailTemplate::TICKET_RESPONDED, [
          'to' => $email,
          'name' => $name,
          'title' => $this->ticket->title,
          'ticketNumber' => $this->ticket->ticketNumber,
          'content' => $content,
          'url' => $url
        ]);

        if ($isPrivate) {
            $mailSender->mailOptions['subject'] = '[INTERNAL NOTE] Ticket #' . $this->ticket->ticketNumber . ' - ' . $this->ticket->title;
        }

        $mailSender->send();
    }
    
    private function getImagePaths() {
        if(!$this->imagePaths){
            $this->imagePaths = $this->uploadImages(Controller::isStaffLogged());
        }
        
        return $this->imagePaths;
    }

    private function notifyBcc($isPrivate = false) {
        $bccAddr = Setting::getSetting('bcc-email')->getValue();
        if (Setting::getSetting('maintenance-mode')->getValue()) {
            $override = Setting::getSetting('maintenance-bcc-override')->getValue();
            if ($override) $bccAddr = $override;
        }
        if (!$bccAddr) return;

        $commenterName = $this->user->name;

        $mailSender = MailSender::getInstance();
        $mailSender->setTemplate(MailTemplate::TICKET_RESPONDED, [
            'to' => $bccAddr,
            'name' => $commenterName,
            'title' => $this->ticket->title,
            'ticketNumber' => $this->ticket->ticketNumber,
            'content' => $isPrivate ? $this->wrapPrivateContent($this->replaceWithImagePaths($this->getImagePaths(), $this->content)) : $this->replaceWithImagePaths($this->getImagePaths(), $this->content),
            'url' => Setting::getSetting('url')->getValue()
        ]);

        if ($isPrivate) {
            $mailSender->mailOptions['subject'] = '[INTERNAL NOTE] Ticket #' . $this->ticket->ticketNumber . ' - ' . $this->ticket->title;
        }

        $mailSender->send();
    }


    private function wrapPrivateContent($content) {
        $commenterName = htmlspecialchars($this->user->name, ENT_QUOTES, 'UTF-8');
        $banner =
            '<div style="background:#fff8c4;border:1px solid #f0d000;border-radius:4px;padding:12px 16px;margin-bottom:14px;text-align:left;">'
            . '<div style="font-weight:bold;color:#8a6d00;font-size:13px;letter-spacing:0.5px;text-transform:uppercase;margin-bottom:4px;">Internal staff note &middot; not visible to customer</div>'
            . '<div style="color:#5a4900;font-size:12px;">From <strong>' . $commenterName . '</strong></div>'
            . '</div>';
        return $banner . '<div style="text-align:left;">' . $content . '</div>';
    }
}
