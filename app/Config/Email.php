<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * อ่านค่าจาก .env คีย์ email.* — ใช้กับ Services::email() และ CertificateEmailService
 *
 * @see https://codeigniter.com/user_guide/libraries/email.html
 */
class Email extends BaseConfig
{
    public string $fromEmail  = '';
    public string $fromName   = '';
    public string $recipients = '';

    /**
     * The "user agent"
     */
    public string $userAgent = 'CodeIgniter';

    /**
     * The mail sending protocol: mail, sendmail, smtp
     */
    public string $protocol = 'mail';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';

    /**
     * SMTP Server Hostname
     */
    public string $SMTPHost = '';

    /**
     * SMTP Username
     */
    public string $SMTPUser = '';

    /**
     * SMTP Password
     */
    public string $SMTPPass = '';

    /**
     * SMTP Port
     */
    public int $SMTPPort = 25;

    /**
     * SMTP Timeout (in seconds)
     */
    public int $SMTPTimeout = 5;

    /**
     * Enable persistent SMTP connections
     */
    public bool $SMTPKeepAlive = false;

    /**
     * SMTP Encryption.
     *
     * @var string '', 'tls' or 'ssl'. 'tls' will issue a STARTTLS command
     *             to the server. 'ssl' means implicit SSL. Connection on port
     *             465 should set this to ''.
     */
    public string $SMTPCrypto = 'tls';

    /**
     * Enable word-wrap
     */
    public bool $wordWrap = true;

    /**
     * Character count to wrap at
     */
    public int $wrapChars = 76;

    /**
     * Type of mail, either 'text' or 'html'
     */
    public string $mailType = 'text';

    /**
     * Character set (utf-8, iso-8859-1, etc.)
     */
    public string $charset = 'UTF-8';

    /**
     * Whether to validate the email address
     */
    public bool $validate = false;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     */
    public int $priority = 3;

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $CRLF = "\r\n";

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $newline = "\r\n";

    /**
     * Enable BCC Batch Mode.
     */
    public bool $BCCBatchMode = false;

    /**
     * Number of emails in each BCC batch
     */
    public int $BCCBatchSize = 200;

    /**
     * Enable notify message from server
     */
    public bool $DSN = false;

    public function __construct()
    {
        parent::__construct();

        $s = static fn (?string $key, string $default = ''): string => self::envString($key, $default);
        $i = static fn (?string $key, int $default): int => self::envInt($key, $default);
        $b = static fn (?string $key, bool $default): bool => self::envBool($key, $default);

        $this->fromEmail  = $s('email.fromEmail', $this->fromEmail);
        $this->fromName   = $s('email.fromName', $this->fromName);
        $this->recipients = $s('email.recipients', $this->recipients);

        $this->userAgent = $s('email.userAgent', $this->userAgent);
        $this->protocol  = $s('email.protocol', $this->protocol);
        $this->mailPath  = $s('email.mailPath', $this->mailPath);

        $this->SMTPHost       = $s('email.SMTPHost', $this->SMTPHost);
        $this->SMTPUser       = $s('email.SMTPUser', $this->SMTPUser);
        $this->SMTPPass       = $s('email.SMTPPass', $this->SMTPPass);
        $this->SMTPPort       = $i('email.SMTPPort', $this->SMTPPort);
        $this->SMTPTimeout    = $i('email.SMTPTimeout', $this->SMTPTimeout);
        $this->SMTPKeepAlive  = $b('email.SMTPKeepAlive', $this->SMTPKeepAlive);
        $this->SMTPCrypto     = $s('email.SMTPCrypto', $this->SMTPCrypto);

        $this->wordWrap  = $b('email.wordWrap', $this->wordWrap);
        $this->wrapChars = $i('email.wrapChars', $this->wrapChars);
        $this->mailType  = $s('email.mailType', $this->mailType);
        $this->charset   = $s('email.charset', $this->charset);
        $this->validate  = $b('email.validate', $this->validate);
        $this->priority  = $i('email.priority', $this->priority);

        $crlf = env('email.CRLF', null);
        if (is_string($crlf) && $crlf !== '') {
            $this->CRLF = str_replace(['\\r', '\\n'], ["\r", "\n"], $crlf);
        }
        $nl = env('email.newline', null);
        if (is_string($nl) && $nl !== '') {
            $this->newline = str_replace(['\\r', '\\n'], ["\r", "\n"], $nl);
        }

        $this->BCCBatchMode = $b('email.BCCBatchMode', $this->BCCBatchMode);
        $this->BCCBatchSize = $i('email.BCCBatchSize', $this->BCCBatchSize);
        $this->DSN          = $b('email.DSN', $this->DSN);

        $this->applyLegacyMailEnvFallbacks();

        if ($this->SMTPHost !== '' && $this->protocol === 'mail') {
            $this->protocol = 'smtp';
        }
    }

    /**
     * รองรับ .env แบบเก่า mail.* (ใช้ในโปรเจกต์นี้) เมื่อยังไม่ได้ตั้ง email.*
     */
    protected function applyLegacyMailEnvFallbacks(): void
    {
        if (trim($this->fromEmail) === '') {
            $this->fromEmail = self::envString('mail.fromEmail', '');
        }
        if (trim($this->fromName) === '') {
            $this->fromName = self::envString('mail.fromName', '');
        }
        if (trim($this->recipients) === '') {
            $this->recipients = self::envString('mail.refereeBcc', self::envString('mail.referee_bcc', ''));
        }
        if (trim($this->SMTPHost) === '') {
            $this->SMTPHost = self::envString('mail.smtpHost', '');
        }
        if (trim($this->SMTPUser) === '') {
            $this->SMTPUser = self::envString('mail.smtpUsername', '');
        }
        if (trim($this->SMTPPass) === '') {
            $this->SMTPPass = self::envString('mail.smtpPassword', '');
        }

        $legacyPort = env('mail.smtpPort', null);
        if ($this->SMTPPort === 25 && $legacyPort !== null && $legacyPort !== false && trim((string) $legacyPort) !== '') {
            $this->SMTPPort = (int) $legacyPort;
        }

        $legacySec = env('mail.smtpSecure', null);
        if ($legacySec !== null && $legacySec !== false && trim((string) $legacySec) !== '') {
            $sec = strtolower(trim((string) $legacySec));
            if (in_array($sec, ['ssl', 'tls'], true)) {
                $this->SMTPCrypto = $sec;
            } elseif (in_array($sec, ['none', 'off', 'false'], true)) {
                $this->SMTPCrypto = '';
            }
        }
    }

    protected static function envString(?string $key, string $default): string
    {
        $v = env($key, null);
        if ($v === null || $v === false) {
            return $default;
        }
        $t = trim((string) $v, " \t\n\r\0\x0B\"'");

        return $t !== '' ? $t : $default;
    }

    protected static function envInt(?string $key, int $default): int
    {
        $v = env($key, null);
        if ($v === null || $v === false || $v === '') {
            return $default;
        }

        return (int) $v;
    }

    protected static function envBool(?string $key, bool $default): bool
    {
        $v = env($key, null);
        if ($v === null || $v === false) {
            return $default;
        }
        if (is_bool($v)) {
            return $v;
        }
        $t = trim((string) $v);
        if ($t === '') {
            return $default;
        }

        return filter_var($t, FILTER_VALIDATE_BOOLEAN);
    }
}
