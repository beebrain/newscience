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
 * ดึง CV/ผลงานจาก กบศ ลงฐานข้อมูลคณะ — จำกัดเฉพาะอีเมลที่ปรากฏในรายชื่อบุคลากรคณะจาก API faculty-personnel
 * (อิง RESEARCH_API_FACULTY_ID / RESEARCH_API_FACULTY_CODE เดียวกับหน้าเว็บคณะ)
 */
class PullFacultyCvFromResearch extends BaseCommand
{
    protected $group       = 'Research';
    protected $name        = 'research:pull-faculty-cv';
    protected $description = 'ดึง CV จาก กบศ ลง newScience เฉพาะบุคลากรในรายชื่อคณะ (faculty-personnel API)';

    protected $usage = 'research:pull-faculty-cv [--list] [--all] [--email=you@faculty]';

    protected $arguments = [
        '--list'   => 'แสดงรายชื่อจาก API ที่จับคู่กับ personnel ในระบบ (และรายการที่ยังไม่มี personnel)',
        '--all'    => 'ดึง CV ให้ทุกคนที่มี personnel และอยู่ในรายชื่อคณะ',
        '--email=' => 'ดึงหนึ่งคน — อีเมลต้องอยู่ในรายชื่อคณะจาก API เท่านั้น',
    ];

    public function run(array $params)
    {
        $researchApi = config(ResearchApi::class);
        if (! $researchApi->syncConfigured()) {
            CLI::error('ตั้งค่าไม่ครบ: ต้องมี RESEARCH_API_BASE_URL และ RESEARCH_API_KEY ใน .env');

            return;
        }
        if (! $researchApi->isConfigured()) {
            CLI::error('ตั้งค่าไม่ครบ: ต้องมี RESEARCH_API_FACULTY_ID หรือ RESEARCH_API_FACULTY_CODE สำหรับขอบเขตคณะ');

            return;
        }

        $payload = FacultyPersonnelApi::fetch();
        if ($payload === null || empty($payload['personnel']) || ! is_array($payload['personnel'])) {
            CLI::error('เรียก faculty-personnel API ไม่สำเร็จหรือไม่มีรายชื่อบุคลากร');

            return;
        }

        $facultyLabel = '';
        if (! empty($payload['faculty']) && is_array($payload['faculty'])) {
            $facultyLabel = trim((string) ($payload['faculty']['name_th'] ?? $payload['faculty']['name'] ?? ''));
        }

        $byEmail = $this->uniqueFacultyRowsByEmail($payload['personnel']);
        $totalApi = count($byEmail);

        $listMode = CLI::getOption('list') !== null;
        $allMode  = CLI::getOption('all') !== null;
        $emailOpt = CLI::getOption('email');
        $oneEmail = $emailOpt !== null && $emailOpt !== ''
            ? CvProfile::normalizeEmail(trim((string) $emailOpt))
            : '';

        if (! $listMode && ! $allMode && $oneEmail === '') {
            CLI::write('คำสั่ง: ' . $this->name, 'yellow');
            CLI::newLine();
            CLI::write('ดึงข้อมูล CV จาก กบศ ลงฐานข้อมูลคณะ โดยยึดรายชื่อจาก API เดียวกับหน้าเว็บ (faculty-personnel)', 'white');
            if ($facultyLabel !== '') {
                CLI::write('คณะ (จาก API): ' . $facultyLabel, 'light_gray');
            }
            CLI::write('จำนวนอีเมลไม่ซ้ำจาก API: ' . $totalApi, 'light_gray');
            CLI::newLine();
            CLI::write('ตัวเลือก:', 'white');
            CLI::write('  --list              แสดงรายชื่อและสถานะจับคู่ personnel', 'light_gray');
            CLI::write('  --all               ดึงให้ทุกคนที่มี personnel ในระบบ', 'light_gray');
            CLI::write('  --email=a@b.c       ดึงหนึ่งคน (อีเมลต้องอยู่ในรายชื่อคณะจาก API)', 'light_gray');
            CLI::newLine();

            return;
        }

        $personnelModel = new PersonnelModel();
        $resolved       = [];
        foreach ($byEmail as $email => $apiRow) {
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
                CLI::error('อีเมลนี้ไม่อยู่ในรายชื่อบุคลากรคณะจาก API — ไม่ดึง (กันดึงนอกขอบเขตคณะ)');

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
     * @param list<array<string,mixed>> $personnelList
     *
     * @return array<string, array<string, mixed>> email => first API row
     */
    private function uniqueFacultyRowsByEmail(array $personnelList): array
    {
        $out = [];
        foreach ($personnelList as $person) {
            if (! is_array($person)) {
                continue;
            }
            $email = $this->emailFromApiPerson($person);
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
     * @param array<string, mixed> $person
     */
    private function emailFromApiPerson(array $person): string
    {
        foreach (['email', 'work_email', 'institutional_email', 'user_email'] as $key) {
            $raw = trim((string) ($person[$key] ?? ''));
            if ($raw !== '') {
                return CvProfile::normalizeEmail($raw);
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $person
     */
    private function displayNameFromApiPerson(array $person): string
    {
        $name = trim((string) ($person['name'] ?? ''));
        if ($name !== '') {
            return $name;
        }
        $title = trim((string) ($person['academic_title'] ?? ''));
        $rest  = trim((string) ($person['name_th'] ?? $person['display_name'] ?? ''));

        return trim($title . ' ' . $rest);
    }

    /**
     * @param list<array{email:string, api_name:string, personnel_id:int, personnel_row: ?array}> $resolved
     */
    private function printList(array $resolved, string $facultyLabel, int $totalApi): void
    {
        CLI::write('=== รายชื่อบุคลากรคณะ (จาก faculty-personnel API) ===', 'cyan');
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
