<?php

namespace App\Commands;

use App\Libraries\CvProfile;
use App\Libraries\ResearchRecordCvPull;
use App\Libraries\ResearchRecordCvSyncClient;
use App\Models\PersonnelModel;
use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\ResearchApi;

/**
 * ดึง CV จาก กบศ (RR) ตามอีเมลใน user ที่สังกัดคณะวิทยาศาสตร์และเทคโนโลยี
 *
 * 1) อ่านจากตาราง user (ฟิลด์ faculty) เป็นหลัก
 * 2) จับคู่ personnel ด้วยอีเมลเท่านั้น (normalize) — PersonnelModel::findByEmail
 * 3) ข้าม: เคยดึงจาก RR สำเร็จแล้ว (cv_sync_log) | กบศไม่มี bundle | ไม่มี personnel
 * 4) วนครบทุกอีเมลที่เข้าเงื่อนไข
 *
 * @see PullFacultyCvFromResearch (รายชื่อจาก faculty-personnel API แทน user)
 */
class PullScienceFacultyCvFromRrByUser extends BaseCommand
{
    /** ตรงกับค่า option ในหน้า admin/user-faculty และ Edoc\GeneralController::SCIENCE_TECH_FACULTY_LABEL */
    private const DEFAULT_FACULTY_LABELS = [
        'คณะวิทยาศาสตร์และเทคโนโลยี',
        'คณะวิทยาศาสตร์และเทคโนโลยี',
    ];

    protected $group       = 'Research';

    protected $name        = 'research:pull-sci-faculty-cv-by-user';

    protected $description = 'ดึง CV จาก กบศ ให้ user คณะวิทยาศาสตร์ฯ (จากตาราง user — อีเมลเป็นตัวเชื่อม)';

    protected $usage = 'research:pull-sci-faculty-cv-by-user [--list] [--email=a@b.c] [--faculty=ชื่อคณะ]';

    protected $arguments = [
        '--list'     => 'แสดงรายชื่อจาก user + สถานะโดยไม่เรียก API กบศ',
        '--email='   => 'จำกัดหนึ่งอีเมล (ต้องเป็นคณะที่เลือก)',
        '--faculty=' => 'กรอง faculty เป็นข้อความเดียว (ค่าเริ่มต้น = คณะวิทยาศาสตร์และเทคโนโลยี + รูปแบบสะกดเดิมใน Edoc)',
    ];

    public function run(array $params)
    {
        $researchApi = config(ResearchApi::class);
        if (! $researchApi->syncConfigured()) {
            CLI::error('ตั้งค่าไม่ครบ: ต้องมี RESEARCH_API_BASE_URL และ RESEARCH_API_KEY ใน .env');

            return;
        }

        $facultyOpt = CLI::getOption('faculty');
        $facultyFilter = $facultyOpt !== null && trim((string) $facultyOpt) !== ''
            ? [trim((string) $facultyOpt)]
            : self::DEFAULT_FACULTY_LABELS;
        $facultyFilter = array_values(array_unique($facultyFilter, SORT_STRING));

        $userModel = new UserModel();
        $db        = $userModel->db;
        if (! $db->fieldExists('faculty', 'user')) {
            CLI::error('ตาราง user ไม่มีคอลัมน์ faculty — รัน migrate ก่อน');

            return;
        }

        $builder = $userModel->builder()
            ->where('email IS NOT NULL')
            ->where('TRIM(email) !=', '')
            ->groupStart();

        foreach ($facultyFilter as $i => $label) {
            if ($i === 0) {
                $builder->where('faculty', $label);
            } else {
                $builder->orWhere('faculty', $label);
            }
        }
        $builder->groupEnd();

        $emailOpt = CLI::getOption('email');
        if ($emailOpt !== null && trim((string) $emailOpt) !== '') {
            $ne = CvProfile::normalizeEmail(trim((string) $emailOpt));
            $builder->where('email', $ne);
        }

        $users = $builder->orderBy('email', 'ASC')->get()->getResultArray();

        if ($users === []) {
            CLI::write('ไม่พบ user ที่ตรงเงื่อนไขคณะ/อีเมล', 'yellow');

            return;
        }

        $personnelModel = new PersonnelModel();
        $rows            = [];
        foreach ($users as $u) {
            $rawEmail = trim((string) ($u['email'] ?? ''));
            $email    = CvProfile::normalizeEmail($rawEmail);
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $person = $personnelModel->findByEmail($email);
            $pid     = $person !== null ? (int) ($person['id'] ?? 0) : 0;
            $pulled  = $pid > 0 && ResearchRecordCvPull::lastSuccessfulRrPullAt($pid) !== null;
            $rows[]  = [
                'uid'            => (int) ($u['uid'] ?? 0),
                'email'          => $email,
                'faculty'        => trim((string) ($u['faculty'] ?? '')),
                'personnel_id'   => $pid,
                'already_pulled' => $pulled,
            ];
        }

        if (CLI::getOption('list') !== null) {
            $this->printList($rows, $facultyFilter);

            return;
        }

        $ok        = 0;
        $fail      = 0;
        $skipNp    = 0;
        $skipPul   = 0;
        $skipNoRr  = 0;

        foreach ($rows as $r) {
            $email = $r['email'];
            CLI::write('--- ' . $email . ' ---', 'cyan');

            if ($r['personnel_id'] <= 0) {
                CLI::write('[ข้าม ไม่มี personnel สำหรับอีเมลนี้]', 'yellow');
                $skipNp++;

                continue;
            }

            if ($r['already_pulled']) {
                CLI::write('[ข้าม เคยดึงจาก กบศ สำเร็จแล้ว]', 'light_gray');
                $skipPul++;

                continue;
            }

            $peek = ResearchRecordCvSyncClient::fetchCvBundle($email);
            if (! $peek['success'] || empty($peek['bundle'])) {
                CLI::write('[ข้าม ไม่มีข้อมูล CV ใน กบศ] ' . ($peek['message'] ?? ''), 'yellow');
                $skipNoRr++;

                continue;
            }

            $result = ResearchRecordCvPull::run($r['personnel_id'], $email, ResearchRecordCvPull::TRIGGER_MANUAL);
            if (! empty($result['success'])) {
                CLI::write($result['message'] ?? 'สำเร็จ', 'green');
                $ok++;
            } else {
                CLI::error($result['message'] ?? 'ดึงไม่สำเร็จ');
                if (! empty($result['error'])) {
                    CLI::write('รหัส: ' . $result['error'], 'light_gray');
                }
                $fail++;
            }
        }

        CLI::newLine();
        CLI::write(
            sprintf(
                'สรุป: สำเร็จ %d | ล้มเหลว %d | ข้ามไม่มี personnel %d | ข้ามเคยดึงแล้ว %d | ข้ามไม่มีข้อมูล กบศ %d',
                $ok,
                $fail,
                $skipNp,
                $skipPul,
                $skipNoRr
            ),
            $fail > 0 ? 'yellow' : 'green'
        );
    }

    /**
     * @param list<array{uid:int,email:string,faculty:string,personnel_id:int,already_pulled:bool}> $rows
     * @param list<string>                                                                        $facultyFilter
     */
    private function printList(array $rows, array $facultyFilter): void
    {
        CLI::write('=== user ตามคณะ (จากตาราง user) ===', 'cyan');
        CLI::write('คณะที่กรอง: ' . implode(' | ', $facultyFilter), 'light_gray');
        CLI::write('จำนวนแถว: ' . count($rows), 'light_gray');
        CLI::newLine();

        $wEmail = 40;
        $wPid   = 14;
        $wStat  = 28;

        CLI::write(
            str_pad('email', $wEmail) . str_pad('personnel', $wPid) . str_pad('สถานะ', $wStat),
            'yellow'
        );
        CLI::write(str_repeat('-', $wEmail + $wPid + $wStat), 'dark_gray');

        foreach ($rows as $r) {
            if ($r['personnel_id'] <= 0) {
                $stat = 'ไม่มี personnel';
            } elseif ($r['already_pulled']) {
                $stat = 'เคยดึง กบศ แล้ว';
            } else {
                $stat = 'รอดึง (มี personnel)';
            }
            $pidStr = $r['personnel_id'] > 0 ? (string) $r['personnel_id'] : '—';
            CLI::write(str_pad($r['email'], $wEmail) . str_pad($pidStr, $wPid) . str_pad($stat, $wStat));
        }
    }
}
