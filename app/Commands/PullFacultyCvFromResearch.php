<?php

namespace App\Commands;

use App\Libraries\CvProfile;
use App\Libraries\FacultyPersonnelApi;
use App\Libraries\ResearchRecordCvPull;
use App\Models\PersonnelModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\ResearchApi;

/**
 * ดึง CV/ผลงานจาก กบศ ลงฐานข้อมูลคณะ
 *
 * การเชื่อมบุคลากร newScience กับรายชื่อจาก API ใช้ **อีเมล (normalize: lowercase + trim) เท่านั้น**
 * — ไม่ใช้ uid, login, หรือรหัสอื่นเป็นตัวจับคู่
 *
 * @see PersonnelModel::findByEmail()
 */
class PullFacultyCvFromResearch extends BaseCommand
{
    /** ฟิลด์ JSON ที่ยอมรับเป็น “อีเมลสำหรับเชื่อม” (อ่านแบบไม่สนตัวพิมพ์ของชื่อคีย์) */
    private const EMAIL_LINK_FIELD_KEYS = [
        'email',
        'user_email',
        'work_email',
        'institutional_email',
        'contact_email',
        'workEmail',
        'primary_email',
        'secondary_email',
        'academic_email',
        'office_email',
    ];

    protected $group       = 'Research';
    protected $name        = 'research:pull-faculty-cv';
    protected $description = 'ดึง CV จาก กบศ (เชื่อมบุคลากรด้วยอีเมลเท่านั้น — รายชื่อคณะจาก faculty-personnel API)';

    protected $usage = 'research:pull-faculty-cv [--list] [--all] [--email=you@faculty]';

    protected $arguments = [
        '--list'   => 'แสดงรายชื่อจาก API จับคู่กับ personnel ด้วยอีเมล (และคนที่ยังไม่มี personnel)',
        '--all'    => 'ดึงให้ทุกคนที่มี personnel และอีเมลอยู่ในรายชื่อคณะจาก API',
        '--email=' => 'ดึงหนึ่งคน — อีเมลต้องอยู่ในรายชื่อคณะจาก API (ใช้จับคู่กับ personnel เท่านั้น)',
    ];

    public function run(array $params)
    {
        $researchApi = config(ResearchApi::class);
        if (! $researchApi->syncConfigured()) {
            CLI::error('ตั้งค่าไม่ครบ: ต้องมี RESEARCH_API_BASE_URL และ RESEARCH_API_KEY ใน .env');

            return;
        }
        if (! $researchApi->isConfigured()) {
            CLI::error('ตั้งค่าไม่ครบ — ตั้ง RESEARCH_API_FACULTY_ID หรือ RESEARCH_API_FACULTY_CODE ใน .env ให้ตรงกับคณะในระบบกบศ');

            return;
        }

        $verbose = CLI::getOption('verbose') !== null;

        $payload = FacultyPersonnelApi::fetch();
        if ($payload === null) {
            CLI::error('เรียก faculty-personnel ไม่สำเร็จ (ค่าตอบกลับ null)');
            $diag = FacultyPersonnelApi::fetchWithDiagnostics();
            CLI::write('URL: ' . ($diag['url'] ?? ''), 'light_gray');
            CLI::write('HTTP: ' . ($diag['http_code'] ?? 0) . ' — ' . ($diag['message'] ?? ''), 'yellow');
            if (($diag['body_preview'] ?? '') !== '') {
                CLI::write('ตัวอย่าง body: ' . $diag['body_preview'], 'dark_gray');
            }

            return;
        }

        $personnelList = isset($payload['personnel']) && is_array($payload['personnel']) ? $payload['personnel'] : [];
        if ($personnelList === []) {
            CLI::error('API ส่งสำเร็จแต่รายการบุคลากรว่าง (personnel ว่างหลัง normalize)');
            if ($verbose && is_array($payload)) {
                CLI::write('คีย์ระดับบนสุดของ JSON: ' . implode(', ', array_keys($payload)), 'light_gray');
            }

            return;
        }

        $facultyLabel = '';
        if (! empty($payload['faculty']) && is_array($payload['faculty'])) {
            $facultyLabel = trim((string) ($payload['faculty']['name_th'] ?? $payload['faculty']['name'] ?? ''));
        }

        $byEmail = $this->uniqueFacultyRowsByEmail($personnelList);
        $totalApi = count($byEmail);

        if ($totalApi === 0) {
            CLI::error('มีแถวจาก API ' . count($personnelList) . ' แต่ไม่มีฟิลด์อีเมลสำหรับเชื่อม (ต้องมีคีย์เช่น email, user_email ใน JSON)');
            if ($verbose) {
                $first = $personnelList[0] ?? [];
                CLI::write('คีย์ของแถวแรก: ' . (is_array($first) ? implode(', ', array_keys($first)) : '(ไม่ใช่ array)'), 'light_gray');
            }

            return;
        }

        $listMode = CLI::getOption('list') !== null;
        $allMode  = CLI::getOption('all') !== null;
        $emailOpt = CLI::getOption('email');
        $oneEmail = $emailOpt !== null && $emailOpt !== ''
            ? CvProfile::normalizeEmail(trim((string) $emailOpt))
            : '';

        if (! $listMode && ! $allMode && $oneEmail === '') {
            CLI::write('คำสั่ง: ' . $this->name, 'yellow');
            CLI::newLine();
            CLI::write('ดึงข้อมูล CV จาก กบศ ลงฐานข้อมูลคณะ — รายชื่อจาก faculty-personnel API', 'white');
            CLI::write('การเชื่อมกับ personnel ใน newScience: ใช้เฉพาะอีเมล (lowercase + trim) — ไม่ใช้ uid/login', 'light_gray');
            if ($facultyLabel !== '') {
                CLI::write('คณะ (จาก API): ' . $facultyLabel, 'light_gray');
            }
            CLI::write('จำนวนอีเมลไม่ซ้ำจาก API: ' . $totalApi, 'light_gray');
            CLI::newLine();
            CLI::write('ตัวเลือก:', 'white');
            CLI::write('  --list              แสดงรายชื่อและสถานะจับคู่ personnel', 'light_gray');
            CLI::write('  --all               ดึงให้ทุกคนที่มี personnel ในระบบ', 'light_gray');
            CLI::write('  --email=a@b.c       ดึงหนึ่งคน (อีเมลต้องอยู่ในรายชื่อคณะจาก API)', 'light_gray');
            CLI::write('  --verbose           แสดงคีย์ JSON เมื่อจับอีเมลไม่ได้', 'light_gray');
            CLI::newLine();

            return;
        }

        $personnelModel = new PersonnelModel();
        $resolved       = [];
        foreach ($byEmail as $email => $apiRow) {
            // จับคู่กับตาราง personnel เฉพาะด้วยอีเมล (user_email / personnel.email / user.email ตาม Model)
            $person = $personnelModel->findByEmail($email);
            $resolved[] = [
                'email'         => $email,
                'api_name'      => $this->displayNameFromApiPerson($apiRow),
                'personnel_id'  => $person !== null ? (int) ($person['id'] ?? 0) : 0,
                'personnel_row' => $person,
            ];
        }
        usort($resolved, static fn ($a, $b) => strcmp($a['email'], $b['email']));

        if ($listMode) {
            $this->printList($resolved, $facultyLabel, $totalApi);

            return;
        }

        if ($oneEmail !== '') {
            if (! isset($byEmail[$oneEmail])) {
                CLI::error('อีเมลนี้ไม่อยู่ในรายชื่อคณะจาก API (จับคู่ด้วยอีเมลเท่านั้น) — ไม่ดึง');

                return;
            }
            $match = null;
            foreach ($resolved as $r) {
                if ($r['email'] === $oneEmail) {
                    $match = $r;
                    break;
                }
            }
            if ($match === null || $match['personnel_id'] <= 0) {
                CLI::error('ไม่พบ personnel ใน newScience สำหรับอีเมล: ' . $oneEmail);

                return;
            }
            $this->runPullForPersonnel($match['personnel_id'], $oneEmail);

            return;
        }

        if ($allMode) {
            $ok = 0;
            $fail = 0;
            $skip = 0;
            foreach ($resolved as $r) {
                if ($r['personnel_id'] <= 0) {
                    $skip++;
                    CLI::write('[ข้าม ไม่มี personnel] ' . $r['email'], 'yellow');
                    continue;
                }
                CLI::write('--- ' . $r['email'] . ' (id ' . $r['personnel_id'] . ') ---', 'cyan');
                if ($this->runPullForPersonnel($r['personnel_id'], $r['email'])) {
                    $ok++;
                } else {
                    $fail++;
                }
            }
            CLI::newLine();
            CLI::write(sprintf('สรุป: สำเร็จ %d, ล้มเหลว %d, ข้าม (ไม่มี personnel) %d', $ok, $fail, $skip), $fail > 0 ? 'yellow' : 'green');
        }
    }

    /**
     * รวมแถวจาก API ต่อหนึ่งอีเมล (คีย์หลักสำหรับเชื่อมกับ newScience)
     *
     * @param list<array<string,mixed>> $personnelList
     *
     * @return array<string, array<string, mixed>> normalized_email => first API row
     */
    private function uniqueFacultyRowsByEmail(array $personnelList): array
    {
        $out = [];
        foreach ($personnelList as $person) {
            if (! is_array($person)) {
                continue;
            }
            $email = $this->emailFromApiPerson($person, 0);
            if ($email === '') {
                continue;
            }
            if (! isset($out[$email])) {
                $out[$email] = $person;
            }
        }

        return $out;
    }

    /**
     * อีเมลสำหรับเชื่อมกับ personnel — อ่านเฉพาะจากฟิลด์ที่ชื่อชัดว่าเป็นอีเมล (+ วัตถุย่อยที่มักมี email)
     * ไม่สแกนค่า string ทั่วทั้งแถว เพื่อไม่ให้ไปจับคู่ผิดกับข้อความอื่นที่มี @
     *
     * @param array<string, mixed> $person
     */
    private function emailFromApiPerson(array $person, int $depth = 0): string
    {
        if ($depth > 6) {
            return '';
        }

        foreach (self::EMAIL_LINK_FIELD_KEYS as $key) {
            $raw = $this->stringFromPersonCaseInsensitive($person, $key);
            if ($raw === '') {
                continue;
            }
            if (filter_var($raw, FILTER_VALIDATE_EMAIL)) {
                return CvProfile::normalizeEmail($raw);
            }
        }

        foreach (['user', 'profile', 'person', 'teacher', 'account'] as $nest) {
            if (! empty($person[$nest]) && is_array($person[$nest])) {
                $inner = $this->emailFromApiPerson($person[$nest], $depth + 1);
                if ($inner !== '') {
                    return $inner;
                }
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $person
     */
    private function stringFromPersonCaseInsensitive(array $person, string $wantKey): string
    {
        foreach ($person as $k => $v) {
            if (! is_string($k) || ! is_string($v)) {
                continue;
            }
            if (strcasecmp($k, $wantKey) === 0) {
                return trim($v);
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $person
     */
    private function displayNameFromApiPerson(array $person): string
    {
        foreach (['name', 'name_thai', 'name_th', 'display_name', 'full_name'] as $k) {
            $name = trim((string) ($person[$k] ?? ''));
            if ($name !== '') {
                return $name;
            }
        }
        $title = trim((string) ($person['academic_title'] ?? ''));
        $rest  = trim((string) ($person['name_english'] ?? $person['name_en'] ?? ''));

        return trim($title . ' ' . $rest);
    }

    /**
     * @param list<array{email:string, api_name:string, personnel_id:int, personnel_row: ?array}> $resolved
     */
    private function printList(array $resolved, string $facultyLabel, int $totalApi): void
    {
        CLI::write('=== รายชื่อบุคลากรคณะ (จาก faculty-personnel API) ===', 'cyan');
        CLI::write('เชื่อมกับ newScience: อีเมลเท่านั้น (normalize) — PersonnelModel::findByEmail', 'light_gray');
        if ($facultyLabel !== '') {
            CLI::write('คณะ: ' . $facultyLabel, 'white');
        }
        CLI::write('อีเมลไม่ซ้ำจาก API: ' . $totalApi, 'light_gray');
        CLI::newLine();

        $wEmail = 38;
        $wName  = 28;
        $wStat  = 20;

        CLI::write(
            str_pad('email', $wEmail) . str_pad('ชื่อ (API)', $wName) . str_pad('personnel', $wStat),
            'yellow'
        );
        CLI::write(str_repeat('-', $wEmail + $wName + $wStat), 'dark_gray');

        $matched = 0;
        $missing = 0;
        foreach ($resolved as $r) {
            $stat = $r['personnel_id'] > 0 ? 'id ' . $r['personnel_id'] : 'ไม่มีในระบบ';
            if ($r['personnel_id'] > 0) {
                $matched++;
            } else {
                $missing++;
            }
            $name = $r['api_name'] !== '' ? $r['api_name'] : '—';
            CLI::write(
                str_pad($r['email'], $wEmail) . str_pad(mb_substr($name, 0, 26), $wName) . str_pad($stat, $wStat)
            );
        }
        CLI::newLine();
        CLI::write('จับคู่ personnel ได้: ' . $matched . ' | ยังไม่มี personnel: ' . $missing, 'white');
    }

    private function runPullForPersonnel(int $personnelId, string $canonicalEmail): bool
    {
        $result = ResearchRecordCvPull::run($personnelId, $canonicalEmail, ResearchRecordCvPull::TRIGGER_MANUAL);
        if (! empty($result['success'])) {
            CLI::write($result['message'] ?? 'สำเร็จ', 'green');

            return true;
        }
        CLI::error($result['message'] ?? 'ดึงไม่สำเร็จ');
        if (! empty($result['error'])) {
            CLI::write('รหัส: ' . $result['error'], 'light_gray');
        }

        return false;
    }
}
