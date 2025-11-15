<?php
// /includes/usernotifications.php

require_once __DIR__ . '/emailsender.php';
require_once __DIR__ . '/../models/User.php';

/**
 * Creates a standardized HTML email template.
 * @param string $title The main heading of the email.
 * @param string $message The main content body of the email.
 * @return string The full HTML for the email.
 */
function _create_email_template($title, $message) {
    $siteDomain = "shop.dkydivyansh.com";
    $currentYear = date('Y');
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Workbench&display=swap" rel="stylesheet">
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif; background-color: #f4f4f4; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
            .header { 
                background-color: #0a0a0a; 
                color: #ffffff; 
                padding: 20px; 
                text-align: center; 
                font-family: "Workbench", sans-serif;
                font-weight: 400;
                font-size: 24px; 
            }
            .content { padding: 30px; line-height: 1.6; }
            .content h1 { font-size: 22px; color: #0a0a0a; }
            .content p { margin-bottom: 20px; }
            .footer { background-color: #f4f4f4; color: #888; text-align: center; padding: 20px; font-size: 12px; }
            .footer a { color: #0a0a0a; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">Shop - Dkydivyansh.com</div>
            <div class="content">
                <h1>{$title}</h1>
                {$message}
            </div>
            <div class="footer">
                <p>&copy; {$currentYear} {$siteDomain}. All rights reserved.</p>
                <p><a href="https://{$siteDomain}">Visit our website</a></p>
            </div>
        </div>
    </body>
    </html>
HTML;
}

/**
 * Sends a notification when a user's profile is updated.
 * @param string $authUserId The user's auth ID.
 */
function usernotificationprofileupdate($authUserId) {
    $pdo = DBConnection::get();
    $userModel = new User($pdo);
    $user = $userModel->findByAuthId($authUserId);
    $siteDomain = "shop.dkydivyansh.com";

    if ($user) {
        $subject = "Security Alert - shop - dkydivyansh.com - Profile Updated";
        $message = "<p>Hello " . htmlspecialchars($user['name']) . ",</p>";
        $message .= "<p>This is a confirmation that your profile details on {$siteDomain} were successfully updated.</p>";
        $message .= "<p>If you did not make this change, please contact our support team immediately.</p>";
        
        $body = _create_email_template("Your Profile Has Been Updated", $message);
        $emailSender = new EmailSender();
        $emailSender->sendEmail($user['email'], $subject, $body, "{$siteDomain} Security");
    }
}

/**
 * Sends notifications when a user's email is changed.
 * @param array $user The user object containing 'old_email' and 'new_email'.
 */
function notifyemailupdate($user) {
    $siteDomain = "shop.dkydivyansh.com";
    $emailSender = new EmailSender();

    // --- Notification to the NEW email address ---
    $subjectNew = "Security Alert - shop - dkydivyansh.com - Email Address Updated";
    $messageNew = "<p>Hello " . htmlspecialchars($user['name']) . ",</p>";
    $messageNew .= "<p>Your email address has been successfully updated to <strong>" . htmlspecialchars($user['email']) . "</strong>.</p>";
    $messageNew .= "<p>If you did not make this change, please contact our support team immediately.</p>";
    $bodyNew = _create_email_template("Email Address Updated", $messageNew);
    $emailSender->sendEmail($user['email'], $subjectNew, $bodyNew, "{$siteDomain} Security");

    // --- Notification to the OLD email address ---
    if (isset($user['old_email']) && filter_var($user['old_email'], FILTER_VALIDATE_EMAIL)) {
        $subjectOld = "Security Alert - shop - dkydivyansh.com - Email Changed on Your Account";
        $messageOld = "<p>Hello,</p>";
        $messageOld .= "<p>The email address for your account on {$siteDomain} was recently changed to <strong>" . htmlspecialchars($user['email']) . "</strong>.</p>";
        $messageOld .= "<p>If you made this change, you can safely disregard this email. If you did not authorize this change, please contact our support team immediately to secure your account.</p>";
        $bodyOld = _create_email_template("Security Alert: Email Changed", $messageOld);
        $emailSender->sendEmail($user['old_email'], $subjectOld, $bodyOld, "{$siteDomain} Security");
    }
}

/**
 * Sends a notification when a user's address is updated.
 * @param string $authUserId The user's auth ID.
 */
function usernotificationaddressupdate($authUserId) {
    $pdo = DBConnection::get();
    $userModel = new User($pdo);
    $user = $userModel->findByAuthId($authUserId);
    $siteDomain = "shop.dkydivyansh.com";

    if ($user) {
        $subject = "Security Alert - shop - dkydivyansh.com - Address Updated";
        $message = "<p>Hello " . htmlspecialchars($user['name']) . ",</p>";
        $message .= "<p>This is a confirmation that your address details on {$siteDomain} were successfully updated.</p>";
        $message .= "<p>If you did not make this change, please contact our support team immediately.</p>";

        $body = _create_email_template("Your Address Has Been Updated", $message);
        $emailSender = new EmailSender();
        $emailSender->sendEmail($user['email'], $subject, $body, "{$siteDomain} Security");
    }
}
