<?php

namespace App\Controllers\Concerns;

/**
 * เกราะกันบอตสแปมฟอร์มสาธารณะ (เช่น lxbfYeaa) แบบใช้ซ้ำได้
 *
 * ใช้กับ controller ที่มีฟอร์มสาธารณะรับข้อมูลจากภายนอก 3 ชั้น:
 *   1) honeypot — ช่องซ่อนที่คนมองไม่เห็น ถ้ามีค่ากรอกมา = บอต
 *   2) timing  — กรอกฟอร์มเสร็จเร็วเกินกว่ามนุษย์จะทำได้
 *   3) blocklist — เจอลายเซ็นบอตที่รู้จัก (เช่น lxbfYeaa) ในข้อมูลที่ส่งมา
 *
 * วิธีใช้:
 *   - ตอน render ฟอร์ม (GET): เรียก $this->markAntiBotFormRendered('<context>')
 *   - ในวิว: <?= view('partials/anti_bot_field') ?> (วางหลัง csrf_field())
 *   - ตอนรับ POST: if ($this->isLikelyBot('<context>', [...ค่าข้อความ...])) { ... }
 *
 * controller ที่ use trait นี้ต้องมี $this->request (ทุก controller ของ CI4 มีอยู่แล้ว)
 */
trait AntiBot
{
    /** ชื่อช่อง honeypot (ต้องตรงกับใน partials/anti_bot_field.php) */
    private const HONEYPOT_FIELD = 'website';

    /** เวลาขั้นต่ำ (วินาที) ที่คนจริงใช้กรอกฟอร์ม — ต่ำกว่านี้ถือว่าเป็นบอต */
    private const MIN_FILL_SECONDS = 3;

    /** ลายเซ็นบอตสแปมที่รู้จัก (ตัวพิมพ์เล็กทั้งหมด) */
    private const SPAM_TOKENS = ['lxbfyeaa'];

    /** จดเวลาเปิดฟอร์มไว้ใน session สำหรับตรวจ timing */
    protected function markAntiBotFormRendered(string $context): void
    {
        session()->set($this->antiBotSessionKey($context), time());
    }

    /**
     * ตรวจว่าการส่งครั้งนี้น่าจะมาจากบอตสแปมหรือไม่
     *
     * @param string   $context    ชื่อฟอร์ม (ใช้แยก timestamp ใน session)
     * @param string[] $textFields ค่าข้อความที่ผู้ใช้กรอก (ไว้ค้นลายเซ็นบอต)
     */
    protected function isLikelyBot(string $context, array $textFields = []): bool
    {
        // 1) honeypot ต้องว่างเสมอสำหรับคนจริง
        if (trim((string) $this->request->getPost(self::HONEYPOT_FIELD)) !== '') {
            return true;
        }

        // 2) ส่งเร็วผิดปกติ (ตรวจเฉพาะเมื่อมี timestamp ใน session — กัน false positive)
        $loadedAt = (int) session()->get($this->antiBotSessionKey($context));
        if ($loadedAt > 0 && (time() - $loadedAt) < self::MIN_FILL_SECONDS) {
            return true;
        }

        // 3) ลายเซ็นบอตที่รู้จักในข้อความที่ส่งมา
        $haystack = strtolower(implode(' ', array_map(static fn ($v) => (string) $v, $textFields)));
        foreach (self::SPAM_TOKENS as $token) {
            if ($token !== '' && str_contains($haystack, $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * ตรวจ Google reCAPTCHA v3 กับฝั่ง server
     *
     * - ถ้ายังไม่ได้ตั้งค่า secretKey → คืน true (ข้าม reCAPTCHA, honeypot ยังกันอยู่)
     * - ตั้งค่าแล้วแต่ไม่มี token → คืน false (ไม่ผ่าน JS / บอตยิงตรง)
     * - เชื่อม Google ไม่ได้ (network ล้ม) → คืน true (fail-open กันฟอร์มพัง, honeypot ยังกัน)
     * - success=false / action ไม่ตรง / score < ขั้นต่ำ → คืน false
     */
    protected function passesRecaptcha(string $action): bool
    {
        $cfg = config('Recaptcha');
        if ($cfg->secretKey === '') {
            return true; // ยังไม่เปิดใช้ reCAPTCHA
        }

        $token = trim((string) $this->request->getPost('g-recaptcha-response'));
        if ($token === '') {
            log_message('warning', 'reCAPTCHA: missing token on {action}. IP: {ip}', [
                'action' => $action,
                'ip'     => $this->request->getIPAddress(),
            ]);

            return false;
        }

        try {
            $client = \Config\Services::curlrequest(['timeout' => 5, 'http_errors' => false]);
            $resp   = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret'   => $cfg->secretKey,
                    'response' => $token,
                    'remoteip' => $this->request->getIPAddress(),
                ],
            ]);
            $data = json_decode((string) $resp->getBody(), true) ?: [];
        } catch (\Throwable $e) {
            log_message('error', 'reCAPTCHA verify error: {msg}', ['msg' => $e->getMessage()]);

            return true; // fail-open เมื่อต่อ Google ไม่ได้
        }

        if (empty($data['success'])) {
            log_message('warning', 'reCAPTCHA failed on {action}: {err}', [
                'action' => $action,
                'err'    => implode(',', (array) ($data['error-codes'] ?? ['unknown'])),
            ]);

            return false;
        }

        // กัน token ถูกนำไปใช้ข้ามฟอร์ม
        if (isset($data['action']) && $data['action'] !== $action) {
            return false;
        }

        $score = (float) ($data['score'] ?? 0);
        if ($score < $cfg->minScore) {
            log_message('warning', 'reCAPTCHA low score {score} (< {min}) on {action}. IP: {ip}', [
                'score'  => $score,
                'min'    => $cfg->minScore,
                'action' => $action,
                'ip'     => $this->request->getIPAddress(),
            ]);

            return false;
        }

        return true;
    }

    /** เขียน log เตือนเมื่อบล็อกบอต (ไว้ตรวจสอบย้อนหลัง) */
    protected function logAntiBotBlocked(string $context): void
    {
        log_message('warning', 'Spam bot blocked on {ctx}. IP: {ip} | UA: {ua}', [
            'ctx' => $context,
            'ip'  => $this->request->getIPAddress(),
            'ua'  => substr((string) $this->request->getUserAgent(), 0, 255),
        ]);
    }

    private function antiBotSessionKey(string $context): string
    {
        return 'antibot_ts_' . $context;
    }
}
