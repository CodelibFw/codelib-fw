<?php
/**
 * Email.php
 */
namespace cl\messaging\email;
/*
 * MIT License
 *
 * Copyright Codelib Framework (https://codelibfw.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

use cl\web\CLConfig;
use PHPMailer\PHPMailer\Exception;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;


/**
 * Class Email
 * Email functionality, allows sending emails for a CL app, either directly via PHP mail, or by delegating to an
 * installed email library (see AppConfig::setEmailLibrary for more details)
 * @package cl\util
 */
class Email
{
    private $config;

    public function __construct(CLConfig $config)
    {
        $this->config = $config;
    }

    public function send(array $email) {
        $emailConfig = $this->config->getEmailConfig();
        if ($emailConfig == null || !is_array($emailConfig)) {
            error_log('Unable to send email because email configuration is missing');
            return;
        }
        if (isset($emailConfig[EMAIL_LIB])) {
            if ($emailConfig[EMAIL_LIB] == 'swiftmailer') {
                return $this->swiftSend($email, $emailConfig);
            } elseif ($emailConfig[EMAIL_LIB] == 'phpmailer') {
                return $this->phpmailerSend($email, $emailConfig);
            } else {
                // call custom email library
                call_user_func_array($emailConfig['emailSender'], $emailConfig);
            }
        }
        // no email library configured, use basic sendmail functionality
        return $this->sendMail($email, $emailConfig);
    }

    private function swiftSend($email, $emailConfig) {
        error_log('sending swiftmail');
        $transport = (new Swift_SmtpTransport($emailConfig[MAIL_HOST], 25))
            ->setUsername($emailConfig['username'])
            ->setPassword($emailConfig['password']);
        $mailer = new Swift_Mailer($transport);
        $message = (new Swift_Message($email['subject']))
            ->setFrom($email['from'])
            ->setTo($email['to'])
            ->setBody($email['message']);
        return $mailer->send($message) > 0;
    }

    private function phpmailerSend($email, $emailConfig) {
        error_log('sending phpmailer mail');
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = $emailConfig[MAIL_HOST];                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = $emailConfig['username'];                     // SMTP username
        $mail->Password   = $emailConfig['password'];                               // SMTP password
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        try {
            //Recipients
            $mail->setFrom($email['from']);
            $mail->addAddress($email['to']);     // Add a recipient
            $mail->Subject = $email['subject'];
            $mail->Body = $email['message'];
            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }

    private function sendMail($email, $emailConfig) {
        error_log('sending email via php mail...');
        $headers = array(
            'From' => $email['from'],
            'Reply-To' => $email['from'],
            'X-Mailer' => 'PHP/' . phpversion(),
            'Content-type' => 'text/html'
        );
        return mail($email['to'], $email['subject'], $email['message'], $headers);
    }
}
