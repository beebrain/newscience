<?php

namespace App\Models\Edoc;

use CodeIgniter\Model;

class SendmailModel extends Model
{
    private $username = "datascience@uru.ac.th";
    private $detailname = "Edocument System";
    private $password = "nwseitudwzpjjanl";
    private $sendform = "datascience@uru.ac.th";

    public function __construct()
    {
        parent::__construct();

        require_once APPPATH . 'ThirdParty/PHPMailer/PHPMailer.php';
        require_once APPPATH . 'ThirdParty/PHPMailer/SMTP.php';
        require_once APPPATH . 'ThirdParty/PHPMailer/Exception.php';
    }

    /**
     * Send confirmation email to user
     */
    public function confirmUser($emailAddress, $password)
    {
        $body = "Welcome to Our System
Please keep this e-mail for your records. Your account information is as follows:
----------------------------

Username:" . $emailAddress;
        $body .= "
Password:" . $password;
        $body .= "
----------------------------
Please visit the following link in order to activate your account:
";
        $body .= base_url('user/confirm/' . base64_encode($emailAddress));
        $body .= "
Your password has been securely stored in our database and cannot be 
retrieved. In the event that it is forgotten, you will be able to reset it
using the email address associated with your account.

Thank you for registering.";

        $subject = "Welcome to Faculty Science and Technology System.";

        return $this->sendMail($emailAddress, $body, $subject);
    }

    /**
     * Standard email sending function
     */
    public function sendMail($emailAddress, $body, $subject, $bcc = "")
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->CharSet = "UTF-8";
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "ssl";
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 465;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            $mail->setFrom($this->sendform, $this->detailname);

            $mail->Subject = $subject;
            $mail->Body = $body;

            $to_array = explode(',', $emailAddress);
            foreach ($to_array as $address) {
                $mail->addAddress(trim($address), 'Web Enquiry');
            }

            if ($bcc != "") {
                $mail->addBcc($bcc, 'Secrete');
            }

            $mail->send();
            $data["message"] = "ส่งอีเมล์สำเร็จ!";
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $data["message"] = "Error: " . $mail->ErrorInfo;
            log_message('error', "Email sending failed: {$mail->ErrorInfo}");
        }

        return $data;
    }

    /**
     * Send HTML email with alternative plain text version
     */
    public function sendMailHTML($emailAddress, $htmlContent, $textContent, $subject, $bcc = "")
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->CharSet = "UTF-8";
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "ssl";
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 465;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            $mail->setFrom($this->sendform, $this->detailname);

            $mail->Subject = $subject;

            $mail->isHTML(true);
            $mail->Body = $htmlContent;
            $mail->AltBody = $textContent;

            $to_array = explode(',', $emailAddress);
            foreach ($to_array as $address) {
                $mail->addAddress(trim($address), 'Document Notification');
            }

            if ($bcc != "") {
                $mail->addBcc($bcc, 'Secrete');
            }

            $mail->send();
            $data["message"] = "ส่งอีเมล์สำเร็จ!";
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $data["message"] = "Error: " . $mail->ErrorInfo;
            log_message('error', "Email sending failed: {$mail->ErrorInfo}");
        }

        return $data;
    }
}
