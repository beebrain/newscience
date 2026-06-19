<?php

namespace App\Models\Edoc;

use CodeIgniter\Model;
use Config\Services;

class SendmailModel extends Model
{
    private string $fromName;
    private string $fromEmail;

    public function __construct()
    {
        parent::__construct();

        // From เดิม: ชื่อ "Edocument System" / อีเมล datascience@uru.ac.th
        // SMTP host/port/auth มาจาก Config\Email (อ่าน mail.* env ชุดเดียวกับ PHPMailer เดิม) — แหล่ง config เดียวทั้งระบบ
        $this->fromName  = (string) env('mail.fromName', 'Edocument System');
        $this->fromEmail = (string) env('mail.fromEmail', env('mail.smtpUsername', ''));
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
     * Standard email sending function (plain text)
     */
    public function sendMail($emailAddress, $body, $subject, $bcc = "")
    {
        $email = Services::email();
        $email->clear(true); // ponytail: ล้าง state กัน recipient ค้างข้ามการเรียกใน request เดียว (R5)
        $email->setFrom($this->fromEmail, $this->fromName);
        $email->setTo($emailAddress); // CI4 รับ comma-separated เอง (R4)
        if ($bcc !== "") {
            $email->setBCC($bcc);
        }
        $email->setSubject($subject);
        $email->setMailType('text');
        $email->setMessage($body);

        if ($email->send(false)) {
            return ["message" => "ส่งอีเมล์สำเร็จ!"]; // R1: คงสตริงเดิมเป๊ะ (GeneralController match ตรงตัว)
        }

        $err = $email->printDebugger(['headers']);
        log_message('error', "Email sending failed: {$err}");

        return ["message" => "Error: {$err}"];
    }

    /**
     * Send HTML email with alternative plain text version
     */
    public function sendMailHTML($emailAddress, $htmlContent, $textContent, $subject, $bcc = "")
    {
        $email = Services::email();
        $email->clear(true);
        $email->setFrom($this->fromEmail, $this->fromName);
        $email->setTo($emailAddress);
        if ($bcc !== "") {
            $email->setBCC($bcc);
        }
        $email->setSubject($subject);
        $email->setMailType('html');
        $email->setMessage($htmlContent);
        $email->setAltMessage($textContent); // R2: คง plain-text สำรองเดิม

        if ($email->send(false)) {
            return ["message" => "ส่งอีเมล์สำเร็จ!"];
        }

        $err = $email->printDebugger(['headers']);
        log_message('error', "Email sending failed: {$err}");

        return ["message" => "Error: {$err}"];
    }
}
