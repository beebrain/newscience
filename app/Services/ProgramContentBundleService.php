<?php

namespace App\Services;

/**
 * Export/import JSON bundle ต่อหลักสูตร (program_pages + metadata ตาราง programs สำหรับ export)
 * schema_version 1
 */
class ProgramContentBundleService
{
    public const SCHEMA_VERSION = 1;

    /** สำเนา JSON ล่าสุดหลังส่งออกหรือหลังนำเข้าสำเร็จ (writable/uploads/programs/{id}/data/) */
    public const SNAPSHOT_LATEST = 'content-bundle-latest.json';

    /** แม่แบบว่างล่าสุดที่ดาวน์โหลดจากแอดมิน */
    public const SNAPSHOT_TEMPLATE = 'content-bundle-template.json';

    /** path ของ JSON Schema file — ใช้เป็นเอกสารอ้างอิง + โหลดใน test */
    public const SCHEMA_PATH = APPPATH . 'Config/program_content_bundle.v1.schema.json';

    /**
     * Whitelist key ที่อนุญาตใน `page` — ตรงกับ ProgramPageModel::$allowedFields
     * (ไม่รวม program_id/id/timestamps เพราะกำหนดโดยระบบ)
     */
    private const PAGE_ALLOWED_KEYS = [
        'slug', 'philosophy', 'objectives', 'graduate_profile',
        'elos_json', 'learning_standards_json', 'curriculum_json', 'alumni_messages_json',
        'curriculum_structure', 'study_plan', 'career_prospects',
        'careers_json', 'tuition_fees', 'tuition_fees_json',
        'admission_info', 'contact_info', 'intro_video_url', 'meta_description',
        'gallery_images', 'social_links',
        'hero_image', 'theme_color', 'text_color', 'background_color',
        'is_published',
    ];

    /**
     * กฎชนิดข้อมูลต่อ key ใน page — ใช้ใน validatePageShape
     * ค่า: ['string'|'int'|'bool'|'list'|'object'|'jsonish', ...allow null?]
     */
    private const PAGE_TYPE_RULES = [
        'slug'                    => ['string'],
        'philosophy'              => ['string'],
        'objectives'              => ['list', 'string'],
        'graduate_profile'        => ['list', 'string'],
        'elos_json'               => ['jsonish'],
        'learning_standards_json' => ['jsonish'],
        'curriculum_json'         => ['jsonish'],
        'alumni_messages_json'    => ['jsonish'],
        'curriculum_structure'    => ['string'],
        'study_plan'              => ['string'],
        'career_prospects'        => ['string'],
        'careers_json'            => ['jsonish'],
        'tuition_fees'            => ['string'],
        'tuition_fees_json'       => ['jsonish'],
        'admission_info'          => ['string'],
        'contact_info'            => ['string'],
        'intro_video_url'         => ['string'],
        'meta_description'        => ['string'],
        'gallery_images'          => ['jsonish'],
        'social_links'            => ['jsonish'],
        'hero_image'              => ['string'],
        'theme_color'             => ['string'],
        'text_color'              => ['string', 'null'],
        'background_color'        => ['string', 'null'],
        'is_published'            => ['bool', 'int'],
    ];

    /**
     * บันทึก snapshot ลง uploads ต่อหลักสูตร (ไม่แทน DB — สำรอง/อ้างอิง)
     */
    public function writeSnapshotToUploads(int $programId, string $filename, string $jsonBody): bool
    {
        if (strlen($jsonBody) > 2_200_000) {
            return false;
        }
        helper('program_upload');
        $dir = program_upload_path($programId, 'data');
        $path = $dir . $filename;
        $ok   = file_put_contents($path, $jsonBody) !== false;
        if ($ok) {
            $index = $dir . 'index.html';
            if (! is_file($index)) {
                @file_put_contents($index, "<!DOCTYPE html>\n<html><head><title>403</title></head><body>Forbidden</body></html>\n");
            }
        }
        if (! $ok) {
            log_message('warning', "ProgramContentBundleService: cannot write snapshot {$path}");
        }

        return $ok;
    }

    /**
     * สร้าง bundle สำหรับ export (อ่านง่าย — decode json fields)
     *
     * @return array{schema_version: int, program_id: int, exported_at: string, program: array, page: array}
     */
    public function buildBundleFromDatabase(int $programId, ?array $programRow, ?array $pageRow): array
    {
        helper('overview_lists');
        $page = $this->decodePageRowForBundle($pageRow ?? []);

        return [
            'schema_version'  => self::SCHEMA_VERSION,
            'program_id'      => $programId,
            'exported_at'     => gmdate('c'),
            'program'         => $this->programSlice($programId, $programRow),
            'page'            => $page,
        ];
    }

    /**
     * แม่แบบ JSON สำหรับกรอกนอกระบบหรือ initial import (ค่า page เป็นค่าว่าง/ค่าเริ่มต้น)
     *
     * @return array{schema_version: int, program_id: int, exported_at: null, template_note: string, program: array, page: array}
     */
    public function buildEmptyTemplateBundle(int $programId, ?array $programRow): array
    {
        $page = [
            'slug'                 => '',
            'philosophy'          => '',
            'objectives'          => [],
            'graduate_profile'     => [],
            'elos_json'           => [],
            'learning_standards_json' => [
                'intro'     => '',
                'standards' => [],
                'mapping'   => [],
            ],
            'curriculum_json'         => [],
            'alumni_messages_json'   => [],
            'curriculum_structure'    => '',
            'study_plan'              => '',
            'career_prospects'        => '',
            'careers_json'            => [],
            'tuition_fees'            => '',
            'tuition_fees_json'       => [],
            'admission_info'          => '',
            'contact_info'            => '',
            'intro_video_url'         => '',
            'meta_description'        => '',
            'gallery_images'         => [],
            'social_links'            => [],
            'hero_image'              => '',
            'theme_color'             => '#1e40af',
            'text_color'              => '',
            'background_color'        => '',
            'is_published'            => 0,
        ];

        return [
            'schema_version'  => self::SCHEMA_VERSION,
            'program_id'      => $programId,
            'exported_at'     => null,
            'template_note'   => 'แม่แบบว่าง — กรอก key ใน page แล้วนำเข้า (program_id ต้องตรงหลักสูตรนี้; ไม่รวมรูป/ไฟล์แนบ ต้องอัปโหลดแยก)',
            'program'         => $this->programSlice($programId, $programRow),
            'page'            => $page,
        ];
    }

    public function programSlice(int $programId, ?array $programRow): array
    {
        if (! is_array($programRow)) {
            return ['id' => $programId];
        }

        return [
            'id'      => (int) ($programRow['id'] ?? $programId),
            'name_th' => (string) ($programRow['name_th'] ?? ''),
            'name_en' => (string) ($programRow['name_en'] ?? ''),
            'level'   => (string) ($programRow['level'] ?? ''),
            'status'  => (string) ($programRow['status'] ?? ''),
        ];
    }

    /**
     * แปลงแถว DB เป็นค่าใน bundle (objectives/graduate เป็น list, *_json เป็น array/object)
     */
    public function decodePageRowForBundle(array $pageRow): array
    {
        helper('overview_lists');
        $out = [];
        if (isset($pageRow['slug'])) {
            $out['slug'] = (string) $pageRow['slug'];
        }
        $out['philosophy'] = (string) ($pageRow['philosophy'] ?? '');
        $out['objectives'] = overview_text_lines_from_db($pageRow['objectives'] ?? null);
        $out['graduate_profile'] = overview_text_lines_from_db($pageRow['graduate_profile'] ?? '');

        foreach (['elos_json', 'learning_standards_json', 'curriculum_json', 'alumni_messages_json', 'careers_json', 'tuition_fees_json'] as $k) {
            $out[$k] = $this->decodeJsonField($pageRow[$k] ?? null);
        }
        $out['curriculum_structure'] = (string) ($pageRow['curriculum_structure'] ?? '');
        $out['study_plan']           = (string) ($pageRow['study_plan'] ?? '');
        $out['career_prospects']     = (string) ($pageRow['career_prospects'] ?? '');
        $out['tuition_fees']         = (string) ($pageRow['tuition_fees'] ?? '');
        $out['admission_info']       = (string) ($pageRow['admission_info'] ?? '');
        $out['contact_info']         = (string) ($pageRow['contact_info'] ?? '');
        $out['intro_video_url']      = (string) ($pageRow['intro_video_url'] ?? '');
        $out['meta_description']     = (string) ($pageRow['meta_description'] ?? '');

        $out['gallery_images'] = $this->decodeJsonField($pageRow['gallery_images'] ?? null);
        $out['social_links']   = $this->decodeJsonField($pageRow['social_links'] ?? null);

        $out['hero_image']   = (string) ($pageRow['hero_image'] ?? '');
        $out['theme_color']  = (string) ($pageRow['theme_color'] ?? '#1e40af');
        $tc                  = $pageRow['text_color'] ?? null;
        $out['text_color']   = $tc !== null && $tc !== '' ? (string) $tc : '';
        $bc                  = $pageRow['background_color'] ?? null;
        $out['background_color'] = $bc !== null && $bc !== '' ? (string) $bc : '';
        $out['is_published'] = isset($pageRow['is_published']) ? (int) (bool) $pageRow['is_published'] : 0;

        return $out;
    }

    /**
     * @return mixed decoded หรือ null ถ้าว่าง/ไม่ใช่ JSON
     */
    protected function decodeJsonField($raw)
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (is_array($raw)) {
            return $raw;
        }
        $s = (string) $raw;
        if ($s === '') {
            return null;
        }
        $d = json_decode($s, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $s;
        }

        return $d;
    }

    /**
     * อ่าน JSON ไฟล์นำเข้า — ตรวจ shape พื้นฐาน
     *
     * @return array{errors: list<string>, program_id: int|null, page: ?array, raw: ?array}
     */
    public function parseBundleJsonString(string $json): array
    {
        $errors = [];
        if (strlen($json) > 2_200_000) {
            $errors[] = 'ไฟล์ใหญ่เกิน (จำกัด ~2.1MB)';

            return ['errors' => $errors, 'program_id' => null, 'page' => null, 'raw' => null];
        }
        $trim = trim($json);
        if ($trim === '') {
            $errors[] = 'ไฟล์ว่าง';

            return ['errors' => $errors, 'program_id' => null, 'page' => null, 'raw' => null];
        }
        $data = json_decode($json, true);
        if (! is_array($data)) {
            $errors[] = 'รูปแบบ JSON ไม่ถูกต้อง';

            return ['errors' => $errors, 'program_id' => null, 'page' => null, 'raw' => null];
        }
        $v = (int) ($data['schema_version'] ?? 0);
        if ($v < 1 || $v > self::SCHEMA_VERSION) {
            $errors[] = 'schema_version ไม่รองรับ (รองรับ 1–' . self::SCHEMA_VERSION . ')';
        }
        $pid = isset($data['program_id']) ? (int) $data['program_id'] : 0;
        if ($pid <= 0) {
            $errors[] = 'program_id ไม่ถูกต้อง';
        }
        $page = $data['page'] ?? null;
        if (! is_array($page)) {
            $errors[] = 'ต้องมี key "page" เป็น object';
            $page = null;
        } else {
            foreach ($this->validatePageShape($page) as $err) {
                $errors[] = $err;
            }
        }

        return [
            'errors'     => $errors,
            'program_id' => $pid > 0 ? $pid : null,
            'page'       => $page,
            'raw'        => $data,
        ];
    }

    /**
     * ตรวจ shape ของ `page` ตาม JSON Schema v1 (inline — ไม่มี dep composer)
     *
     * - ปฏิเสธ key นอก whitelist
     * - ตรวจ type ต่อ key (string / list / jsonish / bool|int / null)
     * - ตรวจ hex สี (theme_color / text_color / background_color)
     *
     * ไม่ได้ตรวจเชิงลึก (maxLength / nested shape) เพราะ pageBundleToUpdateRow จะ clamp/normalize อีกชั้น
     *
     * @return list<string>
     */
    public function validatePageShape(array $page): array
    {
        $errors = [];
        foreach ($page as $key => $val) {
            if (! is_string($key)) {
                $errors[] = 'page: key ต้องเป็น string';

                continue;
            }
            if (! in_array($key, self::PAGE_ALLOWED_KEYS, true)) {
                $errors[] = 'page: key ต้องห้าม "' . $key . '"';

                continue;
            }
            $rule = self::PAGE_TYPE_RULES[$key] ?? [];
            if ($rule !== [] && ! $this->matchesTypeRule($val, $rule)) {
                $errors[] = 'page.' . $key . ': รูปแบบไม่ตรง (' . implode('|', $rule) . ')';

                continue;
            }
            if (in_array($key, ['theme_color', 'text_color', 'background_color'], true)
                && is_string($val) && $val !== ''
                && ! preg_match('/^#[0-9A-Fa-f]{6}$/', $val)
            ) {
                $errors[] = 'page.' . $key . ': ต้องเป็น #RRGGBB หรือว่าง';
            }
        }

        return $errors;
    }

    /**
     * @param list<string> $rule
     */
    private function matchesTypeRule($val, array $rule): bool
    {
        foreach ($rule as $t) {
            switch ($t) {
                case 'string':
                    if (is_string($val)) {
                        return true;
                    }
                    break;

                case 'int':
                    if (is_int($val)) {
                        return true;
                    }
                    break;

                case 'bool':
                    if (is_bool($val)) {
                        return true;
                    }
                    break;

                case 'null':
                    if ($val === null) {
                        return true;
                    }
                    break;

                case 'list':
                    if (is_array($val) && array_is_list($val)) {
                        return true;
                    }
                    break;

                case 'object':
                    if (is_array($val) && ! array_is_list($val)) {
                        return true;
                    }
                    break;

                case 'jsonish':
                    // array / object / string (raw JSON) / null — ภายหลังแปลงใน pageBundleToUpdateRow
                    if (is_array($val) || is_string($val) || $val === null) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * แปลง page จาก bundle เป็นค่าที่ updateOrCreate รับ
     *
     * @return array{errors: list<string>, update: array<string, mixed>}
     */
    public function pageBundleToUpdateRow(array $pageIn): array
    {
        helper('career_cards');
        helper('tuition_fees');
        helper('overview_lists');
        $errors = [];
        $u      = [];

        if (array_key_exists('slug', $pageIn)) {
            $u['slug'] = mb_substr((string) $pageIn['slug'], 0, 500);
        }
        if (array_key_exists('philosophy', $pageIn)) {
            $u['philosophy'] = mb_substr((string) $pageIn['philosophy'], 0, 5000);
        }
        if (array_key_exists('objectives', $pageIn)) {
            $u['objectives'] = $this->normalizeObjectivesOrGraduate($pageIn['objectives'], 'objectives', $errors);
        }
        if (array_key_exists('graduate_profile', $pageIn)) {
            $u['graduate_profile'] = $this->normalizeObjectivesOrGraduate($pageIn['graduate_profile'], 'graduate_profile', $errors);
        }
        foreach (['elos_json', 'learning_standards_json', 'curriculum_json', 'alumni_messages_json'] as $f) {
            if (array_key_exists($f, $pageIn)) {
                $max = $f === 'alumni_messages_json' ? 100000 : 65000;
                $u[$f] = $this->encodeJsonColumn($pageIn[$f], $f, $max, $errors);
            }
        }
        if (array_key_exists('curriculum_structure', $pageIn)) {
            $u['curriculum_structure'] = mb_substr((string) $pageIn['curriculum_structure'], 0, 10000);
        }
        if (array_key_exists('study_plan', $pageIn)) {
            $u['study_plan'] = mb_substr((string) $pageIn['study_plan'], 0, 10000);
        }
        if (array_key_exists('career_prospects', $pageIn)) {
            $u['career_prospects'] = mb_substr((string) $pageIn['career_prospects'], 0, 5000);
        }
        if (array_key_exists('careers_json', $pageIn)) {
            $enc = $this->encodeJsonColumn($pageIn['careers_json'] ?? '[]', 'careers_json', 65000, $errors);
            $u['careers_json'] = career_json_normalize($enc);
        }
        if (array_key_exists('tuition_fees', $pageIn)) {
            $u['tuition_fees'] = mb_substr((string) $pageIn['tuition_fees'], 0, 5000);
        }
        if (array_key_exists('tuition_fees_json', $pageIn)) {
            $enc = $this->encodeJsonColumn($pageIn['tuition_fees_json'] ?? '[]', 'tuition_fees_json', 65000, $errors);
            $u['tuition_fees_json'] = tuition_fees_json_normalize($enc);
        }
        if (array_key_exists('admission_info', $pageIn)) {
            $u['admission_info'] = mb_substr((string) $pageIn['admission_info'], 0, 5000);
        }
        if (array_key_exists('contact_info', $pageIn)) {
            $u['contact_info'] = mb_substr((string) $pageIn['contact_info'], 0, 5000);
        }
        if (array_key_exists('intro_video_url', $pageIn)) {
            $u['intro_video_url'] = mb_substr((string) $pageIn['intro_video_url'], 0, 500);
        }
        if (array_key_exists('meta_description', $pageIn)) {
            $u['meta_description'] = mb_substr((string) $pageIn['meta_description'], 0, 500);
        }
        if (array_key_exists('gallery_images', $pageIn)) {
            $u['gallery_images'] = $this->encodeMysqlJsonValue($pageIn['gallery_images'], 'gallery_images', 100000, $errors);
        }
        if (array_key_exists('social_links', $pageIn)) {
            $u['social_links'] = $this->encodeMysqlJsonValue($pageIn['social_links'], 'social_links', 100000, $errors);
        }
        if (array_key_exists('hero_image', $pageIn)) {
            $u['hero_image'] = mb_substr((string) $pageIn['hero_image'], 0, 500);
        }
        if (array_key_exists('theme_color', $pageIn)) {
            $tc = trim((string) $pageIn['theme_color']);
            if ($tc !== '' && ! preg_match('/^#[0-9A-Fa-f]{6}$/', $tc)) {
                $tc = '#1e40af';
            }
            $u['theme_color'] = $tc !== '' ? $tc : '#1e40af';
        }
        if (array_key_exists('text_color', $pageIn)) {
            $tc = trim((string) $pageIn['text_color']);
            if ($tc !== '' && ! preg_match('/^#[0-9A-Fa-f]{6}$/', $tc)) {
                $errors[] = 'text_color ต้องเป็น #RRGGBB หรือว่าง';
            } else {
                $u['text_color'] = $tc === '' ? null : $tc;
            }
        }
        if (array_key_exists('background_color', $pageIn)) {
            $bc = trim((string) $pageIn['background_color']);
            if ($bc !== '' && ! preg_match('/^#[0-9A-Fa-f]{6}$/', $bc)) {
                $errors[] = 'background_color ต้องเป็น #RRGGBB หรือว่าง';
            } else {
                $u['background_color'] = $bc === '' ? null : $bc;
            }
        }
        if (array_key_exists('is_published', $pageIn)) {
            $u['is_published'] = (int) (bool) $pageIn['is_published'];
        }

        return ['errors' => $errors, 'update' => $u];
    }

    protected function normalizeObjectivesOrGraduate($v, string $field, array &$errors): string
    {
        if (is_array($v)) {
            $lines = [];
            foreach ($v as $line) {
                if (is_string($line) || is_numeric($line)) {
                    $lines[] = (string) $line;
                }
            }
            if (count($lines) > 40) {
                $errors[] = $field . ': มากกว่า 40 ข้อ จะตัด';
            }
            $lines = array_slice($lines, 0, 40);

            return overview_lines_to_json($lines);
        }
        if (is_string($v)) {
            return overview_lines_normalize($v);
        }
        $errors[] = $field . ': รูปแบบต้องเป็น array ของ string หรือ string';

        return '[]';
    }

    /**
     * @param mixed $val
     */
    protected function encodeJsonColumn($val, string $field, int $maxLen, array &$errors): string
    {
        if (is_string($val)) {
            if (strlen($val) > $maxLen) {
                $errors[] = $field . ' ยาวเกิน ' . $maxLen;

                return '{}';
            }

            return $val;
        }
        if ($val === null) {
            return '[]';
        }
        $s = json_encode($val, JSON_UNESCAPED_UNICODE);
        if ($s === false) {
            $errors[] = $field . ' encode ไม่ได้';

            return '[]';
        }
        if (strlen($s) > $maxLen) {
            $errors[] = $field . ' หลัง encode ยาวเกิน ' . $maxLen;
        }

        return mb_substr($s, 0, $maxLen);
    }

    /**
     * คอลัมน์ MySQL ที่ check json_valid
     *
     * @param mixed $val
     */
    protected function encodeMysqlJsonValue($val, string $field, int $maxLen, array &$errors): ?string
    {
        if ($val === null) {
            return null;
        }
        if (is_string($val)) {
            $try = json_decode($val, true);
            if ($val !== '' && json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = $field . ' ต้องเป็น JSON ที่ถูกต้อง';

                return '[]';
            }
            if (strlen($val) > $maxLen) {
                $errors[] = $field . ' ยาวเกิน ' . $maxLen;
            }

            return mb_substr($val, 0, $maxLen);
        }
        $s = json_encode($val, JSON_UNESCAPED_UNICODE);
        if ($s === false) {
            $errors[] = $field . ' encode ไม่ได้';

            return '[]';
        }
        if (strlen($s) > $maxLen) {
            $errors[] = $field . ' ยาวเกิน ' . $maxLen;
        }

        return mb_substr($s, 0, $maxLen);
    }

    /**
     * สรุปแสดง preview ราย section
     *
     * @return list<array{id: string, title: string, items: list<array{label: string, value: string}>}>
     */
    public function buildSectionPreviews(array $pageDecoded): array
    {
        $sections   = [];
        $items      = [
            ['label' => 'ปรัชญา', 'value' => $this->clip((string) ($pageDecoded['philosophy'] ?? ''), 2000)],
        ];
        $obj = $pageDecoded['objectives'] ?? [];
        if (is_array($obj)) {
            foreach (array_slice($obj, 0, 20) as $i => $line) {
                $items[] = ['label' => 'วัตถุประสงค์ ' . ($i + 1), 'value' => $this->clip((string) $line, 500)];
            }
        }
        $gp = $pageDecoded['graduate_profile'] ?? [];
        if (is_array($gp)) {
            foreach (array_slice($gp, 0, 20) as $i => $line) {
                $items[] = ['label' => 'คุณลักษณะ ' . ($i + 1), 'value' => $this->clip((string) $line, 500)];
            }
        }
        $sections[] = ['id' => 'overview', 'title' => '1. ภาพรวม', 'items' => $items];

        $eCount = 0;
        if (isset($pageDecoded['elos_json']) && is_array($pageDecoded['elos_json'])) {
            $eCount = count($pageDecoded['elos_json']);
        }
        $ls     = $pageDecoded['learning_standards_json'] ?? null;
        $lsNote = is_array($ls) ? (string) json_encode($ls, JSON_UNESCAPED_UNICODE) : (string) $ls;

        $curriculumJson = $pageDecoded['curriculum_json'] ?? null;
        $cjv            = is_array($curriculumJson) || is_object($curriculumJson)
            ? (string) json_encode($curriculumJson, JSON_UNESCAPED_UNICODE)
            : (string) $curriculumJson;

        $sections[] = [
            'id'    => 'quality',
            'title' => '2. มาตรฐาน & PLO',
            'items' => [
                ['label' => 'ELO (จำนวนรายการ)', 'value' => (string) $eCount],
                ['label' => 'มาตรฐานการเรียนรู้ (JSON ย่อ)', 'value' => $this->clip($lsNote, 1500)],
            ],
        ];

        $sections[] = [
            'id'    => 'curriculum',
            'title' => '3. แผนการเรียน',
            'items' => [
                ['label' => 'โครงสร้างหลักสูตร (ย่อ)', 'value' => $this->clip((string) ($pageDecoded['curriculum_structure'] ?? ''), 1500)],
                ['label' => 'แผนการศึกษา (ย่อ)', 'value' => $this->clip((string) ($pageDecoded['study_plan'] ?? ''), 1500)],
                ['label' => 'curriculum_json (ย่อ)', 'value' => $this->clip($cjv, 1500)],
            ],
        ];

        $careerNote = is_array($pageDecoded['careers_json'] ?? null)
            ? json_encode($pageDecoded['careers_json'], JSON_UNESCAPED_UNICODE)
            : (string) ($pageDecoded['careers_json'] ?? '');

        $sections[] = [
            'id'    => 'pages',
            'title' => '4. อาชีพ · รับสมัคร · ติดต่อ',
            'items' => [
                ['label' => 'อาชีพหลังจบ (ข้อความ)', 'value' => $this->clip((string) ($pageDecoded['career_prospects'] ?? ''), 800)],
                ['label' => 'careers_json', 'value' => $this->clip($careerNote, 1200)],
                ['label' => 'tuition_fees_json', 'value' => $this->clip(is_array($pageDecoded['tuition_fees_json'] ?? null) ? json_encode($pageDecoded['tuition_fees_json'], JSON_UNESCAPED_UNICODE) : (string) ($pageDecoded['tuition_fees_json'] ?? ''), 800)],
                ['label' => 'รับสมัคร (ย่อ)', 'value' => $this->clip((string) ($pageDecoded['admission_info'] ?? ''), 500)],
                ['label' => 'ติดต่อ (ย่อ)', 'value' => $this->clip((string) ($pageDecoded['contact_info'] ?? ''), 500)],
                ['label' => 'วิดีโอ', 'value' => $this->clip((string) ($pageDecoded['intro_video_url'] ?? ''), 300)],
            ],
        ];

        $alumni   = $pageDecoded['alumni_messages_json'] ?? null;
        $aCount   = is_array($alumni) ? count($alumni) : 0;
        $sections[] = [
            'id'    => 'alumni',
            'title' => 'ศิษย์เก่า',
            'items' => [
                ['label' => 'รายการ (จำนวน)', 'value' => (string) $aCount],
            ],
        ];

        $sections[] = [
            'id'    => 'publish',
            'title' => '5. เผยแพร่ & หน้าเว็บ',
            'items' => [
                ['label' => 'slug', 'value' => $this->clip((string) ($pageDecoded['slug'] ?? ''), 200)],
                ['label' => 'meta', 'value' => $this->clip((string) ($pageDecoded['meta_description'] ?? ''), 500)],
                ['label' => 'เผยแพร่', 'value' => ! empty($pageDecoded['is_published']) ? 'ใช่' : 'ไม่'],
                ['label' => 'theme / hero', 'value' => $this->clip((string) ($pageDecoded['theme_color'] ?? '') . ' | ' . (string) ($pageDecoded['hero_image'] ?? ''), 500)],
            ],
        ];

        return $sections;
    }

    protected function clip(string $s, int $max): string
    {
        if (mb_strlen($s) <= $max) {
            return $s;
        }

        return mb_substr($s, 0, $max) . '…';
    }

    public function writeStagingFile(int $programId, array $updateData): string
    {
        $dir = WRITEPATH . 'temp' . DIRECTORY_SEPARATOR . 'program_bundle_import';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $token = bin2hex(random_bytes(20));
        $path  = $dir . DIRECTORY_SEPARATOR . 'p' . $programId . '_' . $token . '.json';
        $payload = [
            'program_id' => $programId,
            'created'    => time(),
            'expires'    => time() + 600,
            'update'     => $updateData,
        ];
        file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE));

        return $token;
    }

    public function readStagingFile(int $programId, string $token): ?array
    {
        if (! preg_match('/^[a-f0-9]{40}$/', $token)) {
            return null;
        }
        $path = WRITEPATH . 'temp' . DIRECTORY_SEPARATOR . 'program_bundle_import' . DIRECTORY_SEPARATOR . 'p' . $programId . '_' . $token . '.json';
        if (! is_file($path)) {
            return null;
        }
        $raw = file_get_contents($path);
        if ($raw === false) {
            return null;
        }
        $data = json_decode($raw, true);
        if (! is_array($data) || (int) ($data['program_id'] ?? 0) !== $programId) {
            return null;
        }
        if (time() > (int) ($data['expires'] ?? 0)) {
            @unlink($path);

            return null;
        }

        return $data;
    }

    public function deleteStagingFile(int $programId, string $token): void
    {
        if (! preg_match('/^[a-f0-9]{40}$/', $token)) {
            return;
        }
        $path = WRITEPATH . 'temp' . DIRECTORY_SEPARATOR . 'program_bundle_import' . DIRECTORY_SEPARATOR . 'p' . $programId . '_' . $token . '.json';
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
