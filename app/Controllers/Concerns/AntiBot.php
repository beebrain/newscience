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
    protected function antiBotHoneypotName(): string
    {
        return 'website';
    }

    /** เวลาขั้นต่ำ (วินาที) ที่คนจริงใช้กรอกฟอร์ม — ต่ำกว่านี้ถือว่าเป็นบอต */
    protected function antiBotMinSeconds(): int
    {
        return 3;
    }

    /** ลายเซ็นบอตสแปมที่รู้จัก (ตัวพิมพ์เล็กทั้งหมด) */
    protected function antiBotSpamTokens(): array
    {
        return ['lxbfyeaa'];
    }

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
        if (trim((string) $this->request->getPost($this->antiBotHoneypotName())) !== '') {
            return true;
        }

        // 2) ส่งเร็วผิดปกติ (ตรวจเฉพาะเมื่อมี timestamp ใน session — กัน false positive)
        $loadedAt = (int) session()->get($this->antiBotSessionKey($context));
        if ($loadedAt > 0 && (time() - $loadedAt) < $this->antiBotMinSeconds()) {
            return true;
        }

        // 3) ลายเซ็นบอตที่รู้จักในข้อความที่ส่งมา
        $haystack = strtolower(implode(' ', array_map(static fn ($v) => (string) $v, $textFields)));
        foreach ($this->antiBotSpamTokens() as $token) {
            if ($token !== '' && str_contains($haystack, $token)) {
                return true;
            }
        }

        return false;
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
