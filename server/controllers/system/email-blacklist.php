<?php
/**
 * Email Blacklist Configuration
 *
 * Emails matching any of these rules will be silently skipped
 * during email polling and will not create tickets.
 *
 * - senders: exact email addresses or partial matches (case-insensitive)
 * - subject_patterns: regex patterns matched against subject lines
 */
return [
    "senders" => [
        "mailer-daemon@amazonses.com",
        "mailer-daemon@",
        "postmaster@",
    ],
    "subject_patterns" => [
        "/Delivery Status Notification/i",
        "/Undeliverable:/i",
        "/Mail delivery failed/i",
        "/Returned mail/i",
    ],
];
