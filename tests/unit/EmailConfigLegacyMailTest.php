<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Email as EmailConfig;

/**
 * Config\Email อ่าน mail.smtpHost / mail.* ผ่าน applyLegacyMailEnvFallbacks
 *
 * @internal
 */
final class EmailConfigLegacyMailTest extends CIUnitTestCase
{
    /** @var array<string, string|false|null> */
    private array $savedEnv = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->savedEnv = [];
        foreach ([
            'email.fromEmail',
            'email.SMTPHost',
            'email.SMTPUser',
            'email.SMTPPass',
            'email.SMTPPort',
            'email.SMTPCrypto',
            'email.protocol',
            'mail.smtpHost',
            'mail.smtpUsername',
            'mail.smtpPassword',
            'mail.smtpPort',
            'mail.smtpSecure',
            'mail.fromEmail',
            'mail.fromName',
        ] as $key) {
            $this->savedEnv[$key] = $_ENV[$key] ?? getenv($key);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->savedEnv as $key => $value) {
            if ($value === false || $value === null) {
                putenv($key);
                unset($_ENV[$key], $_SERVER[$key]);
            } else {
                putenv($key . '=' . $value);
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }
        parent::tearDown();
    }

    private function clearEmailEnvKeys(): void
    {
        foreach (array_keys($this->savedEnv) as $key) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }
    }

    private function setEnv(string $key, string $value): void
    {
        putenv($key . '=' . $value);
        $_ENV[$key]    = $value;
        $_SERVER[$key] = $value;
    }

    public function testLegacyMailKeysPopulateSmtpConfig(): void
    {
        $this->clearEmailEnvKeys();
        $this->setEnv('mail.smtpHost', 'smtp.gmail.com');
        $this->setEnv('mail.smtpUsername', 'user@example.com');
        $this->setEnv('mail.smtpPassword', 'secret');
        $this->setEnv('mail.smtpPort', '465');
        $this->setEnv('mail.smtpSecure', 'ssl');
        $this->setEnv('mail.fromEmail', 'sender@example.com');
        $this->setEnv('mail.fromName', 'Test Sender');

        $email = new EmailConfig();

        $this->assertSame('smtp.gmail.com', $email->SMTPHost);
        $this->assertSame('user@example.com', $email->SMTPUser);
        $this->assertSame('secret', $email->SMTPPass);
        $this->assertSame(465, $email->SMTPPort);
        $this->assertSame('ssl', $email->SMTPCrypto);
        $this->assertSame('sender@example.com', $email->fromEmail);
        $this->assertSame('Test Sender', $email->fromName);
        $this->assertSame('smtp', $email->protocol);
    }

    public function testEmailKeysTakePrecedenceOverMailKeys(): void
    {
        $this->clearEmailEnvKeys();
        $this->setEnv('mail.smtpHost', 'smtp.old.example');
        $this->setEnv('mail.fromEmail', 'old@example.com');
        $this->setEnv('email.SMTPHost', 'smtp.new.example');
        $this->setEnv('email.fromEmail', 'new@example.com');

        $email = new EmailConfig();

        $this->assertSame('smtp.new.example', $email->SMTPHost);
        $this->assertSame('new@example.com', $email->fromEmail);
    }
}
