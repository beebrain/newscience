<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * การแปลงข้อความเป็นผลงาน CV — โหมด n8n webhook (JSON แบบ ResearchRecord)
 * หรือโหมด OpenAI-compatible Chat Completions
 */
class AiCv extends BaseConfig
{
    /** เปิดใช้เมื่อมี webhook n8n หรือ URL+key ของผู้ให้บริการโดยตรง */
    public bool $enabled = false;

    /** POST ไปที่นี่ — n8n รับ JSON { "url": "..." } (แบบ RR extract-article-v2) หรือ { "text": "..." } */
    public string $n8nUrl = '';

    /** Optional: Bearer สำหรับป้องกัน webhook (ส่งเป็น Authorization: Bearer …) */
    public string $n8nBearerToken = '';

    /**
     * Base URL ที่ n8n ดึงไฟล์ได้ (โดเมนรากเดียวกับ Edoc — ว่างแล้วใช้ app.baseURL)
     * เช่น https://sci.uru.ac.th → …/index.php/cv-ai/file/{storedName}
     */
    public string $filePublicBaseUrl = '';

    public string $apiUrl = '';

    public string $apiKey = '';

    public string $model = 'gpt-4o-mini';

    public int $timeoutSeconds = 45;

    public int $maxInputChars = 8000;

    /** จำนวนคำขอสูงสุดต่อชั่วโมงต่อ user (session) */
    public int $rateLimitPerHour = 20;

    public function __construct()
    {
        parent::__construct();
        $this->n8nUrl             = trim((string) env('AI_CV_N8N_URL', ''));
        $this->n8nBearerToken     = trim((string) env('AI_CV_N8N_TOKEN', ''));
        $this->filePublicBaseUrl  = rtrim(trim((string) env('AI_CV_FILE_PUBLIC_BASE_URL', '')), '/');
        $this->apiKey          = trim((string) env('AI_CV_API_KEY', ''));
        $this->apiUrl          = rtrim(trim((string) env('AI_CV_API_URL', '')), '/');
        $this->model           = trim((string) env('AI_CV_MODEL', 'gpt-4o-mini'));
        $this->enabled         = filter_var(env('AI_CV_ENABLED', false), FILTER_VALIDATE_BOOL)
            || $this->n8nUrl !== ''
            || ($this->apiKey !== '' && $this->apiUrl !== '');
        $t = env('AI_CV_TIMEOUT');
        $this->timeoutSeconds = $t !== null && $t !== '' ? max(5, (int) $t) : 45;
        $m = env('AI_CV_MAX_INPUT_CHARS');
        $this->maxInputChars = $m !== null && $m !== '' ? max(500, (int) $m) : 8000;
        $rl = env('AI_CV_RATE_LIMIT_PER_HOUR');
        $this->rateLimitPerHour = $rl !== null && $rl !== '' ? max(1, (int) $rl) : 20;
    }

    public function isReady(): bool
    {
        if (! $this->enabled) {
            return false;
        }
        if ($this->n8nUrl !== '') {
            return true;
        }

        return $this->apiKey !== '' && $this->apiUrl !== '';
    }

    public function usesN8n(): bool
    {
        return $this->n8nUrl !== '';
    }
}
