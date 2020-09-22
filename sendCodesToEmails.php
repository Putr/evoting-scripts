<?php

//
// SETUP AND CONFIGURATION
//
$config = parse_ini_file("config.ini");

$options = getopt("", ["count:", "email:", "codes:", "template:", "subject:"]);

if (!$options['count'] || !is_numeric($options['count'])) {
    die("Please run with --count= to specify the control number!");
}

if (
    !$options['email']
    || !$options['count']
    || !$options['email']
    || !$options['codes']
    || !$options['template']
    || !$options['subject']
) {
    die("Please use all required options!");
}

// Amazon sending configuration
$total = (int) $options['count'];

//
// Load dependency for sending emails
//
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//
// Load data
//
$emails = array_map(
    'cleanupArray',
    array_map(
        'str_getcsv',
        file($options['email'])
    )
);
$codes = array_map(
    'cleanupArray',
    array_map(
        'str_getcsv',
        file($options['codes'])
    )
);
$codesCount = count($codes);
$emailsCount = count($emails);
$count = (int) $options['count'];


echo sprintf("We have %s emails, %s codes with control count of %s" . PHP_EOL, $emailsCount, $codesCount, $count);

if ($codesCount !== $count && $emailsCount !== $count) {
    die('Counts are not correct!');
}

$template = file_get_contents('./emailTemplates/' . $options['template'] . '.txt');

// Randomize the codes
shuffle($codes);

foreach ($emails as $email) {
    $code = array_pop($codes);
    sendEmail(
        $email,
        $options['subject'],
        str_replace('%%CODE%%', $code, $template)
    );
    echo sprintf("Sent email to: %s", $email) . PHP_EOL;
}


//
// UTILITY
// 

/**
 * Does the actual sending to SES
 *
 * @param string $to
 * @param string $subject
 * @param string $body
 * @return void
 */
function sendEmail($to, $subject, $body)
{
    global $config;

    $mail = new PHPMailer(true);

    try {
        // Specify the SMTP settings.
        $mail->isSMTP();
        $mail->setFrom($config['SENDER'], $config['SENDER_NAME']);
        $mail->Username   = $config['SMTP_USERNAME'];
        $mail->Password   = $config['SMTP_PASS'];
        $mail->Host       = $config['SES_HOST'];
        $mail->Port       = $config['SES_PORT'];
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = 'tls';
        // $mail->addCustomHeader('X-SES-CONFIGURATION-SET', $configurationSet);

        // Specify the message recipients.
        $mail->addAddress($to);

        // Specify the content of the message.
        $mail->isHTML(false);
        $mail->Subject    = $subject;
        $mail->Body       = $body;
        $mail->AltBody    = $body;
        $mail->Send();
        return true;
    } catch (phpmailerException $e) {
        echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
    } catch (Exception $e) {
        echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
    }
}

function cleanupArray($element)
{
    return $element[0];
}
