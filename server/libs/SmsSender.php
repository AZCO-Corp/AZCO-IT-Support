<?php
class SmsSender {

    public static function sendUrgentTicketSMS($ticketNumber, $title, $name, $phone) {
        $numbersRaw = Setting::getSetting('telnyx-urgent-numbers')->getValue();
        if (!$numbersRaw) return;

        $msg = "URGENT TICKET #$ticketNumber\n"
             . "Title: $title\n"
             . "From: $name\n"
             . "Callback: $phone\n"
             . "https://itsupport.securusconverting.com/admin/panel/tickets/view-ticket/$ticketNumber";

        $numbers = array_map('trim', explode(',', $numbersRaw));
        foreach ($numbers as $to) {
            if ($to) self::send($to, $msg);
        }
    }

    public static function send($to, $message) {
        $apiKey = Setting::getSetting('telnyx-api-key')->getValue();
        $from = Setting::getSetting('telnyx-from-number')->getValue();
        $profileId = Setting::getSetting('telnyx-profile-id')->getValue();

        if (!$apiKey || !$from) return null;

        $ch = curl_init('https://api.telnyx.com/v2/messages');

        $payload = json_encode([
            'from' => $from,
            'to' => $to,
            'text' => $message,
            'messaging_profile_id' => $profileId,
        ]);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
