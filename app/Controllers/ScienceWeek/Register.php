<?php

namespace App\Controllers\ScienceWeek;

use App\Controllers\BaseController;
use App\Models\SwRegistrationModel;
use App\Models\SwParticipantModel;
use Config\SciWeek;

class Register extends BaseController
{
    private SciWeek $cfg;
    private SwRegistrationModel $regModel;
    private SwParticipantModel $partModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->cfg       = config('SciWeek');
        $this->regModel  = new SwRegistrationModel();
        $this->partModel = new SwParticipantModel();
    }

    /** หน้าแรก — เลือกรายการแข่งขัน */
    public function index(): string
    {
        $competitions = $this->cfg->competitions;
        $caps = [];
        foreach ($competitions as $key => $comp) {
            $counts = [];
            foreach (array_keys($comp['levels']) as $levelKey) {
                $counts[$levelKey] = $this->regModel->countByLevel($key, $levelKey);
            }
            $caps[$key] = $counts;
        }

        return view('scienceweek/index', [
            'competitions' => $competitions,
            'caps'         => $caps,
        ]);
    }

    /** แสดงฟอร์มสมัคร */
    public function form(string $competitionKey): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $comp = $this->cfg->competitions[$competitionKey] ?? null;
        if ($comp === null) {
            return $this->redirectNotFound();
        }

        // ตรวจ deadline
        $deadlineMsg = $this->getDeadlineMessage($comp);

        // ตรวจเพดานทีม (cap_total)
        if ($comp['cap_total'] !== null) {
            $total = $this->regModel->countTotal($competitionKey);
            if ($total >= $comp['cap_total']) {
                return view('scienceweek/closed', [
                    'comp'    => $comp,
                    'message' => 'ปิดรับสมัครแล้ว จำนวนทีมเต็มแล้ว ('.$total.'/'.$comp['cap_total'].' ทีม)',
                ]);
            }
        }

        return view('scienceweek/form', [
            'comp'          => $comp,
            'competitionKey'=> $competitionKey,
            'deadlineMsg'   => $deadlineMsg,
            'validation'    => \Config\Services::validation(),
            'old'           => session()->getFlashdata('old_input') ?? [],
        ]);
    }

    /** บันทึกการสมัคร */
    public function save(string $competitionKey): \CodeIgniter\HTTP\RedirectResponse|string
    {
        $comp = $this->cfg->competitions[$competitionKey] ?? null;
        if ($comp === null) {
            return redirect()->to(base_url('scienceweek'));
        }

        // ตรวจ deadline
        if ($comp['deadline'] !== null && date('Y-m-d') > $comp['deadline']) {
            return redirect()->back()->with('error', 'ปิดรับสมัครแล้ว เกินกำหนด '.$comp['deadline']);
        }

        $post = $this->request->getPost();

        // Validation พื้นฐาน
        $rules = $this->buildValidationRules($comp);
        if (!$this->validate($rules)) {
            session()->setFlashdata('old_input', $post);
            return redirect()->back()->withInput();
        }

        $levelKey   = $post['level_key'];
        $schoolName = trim($post['school_name']);

        // ตรวจเพดานทีม (atomic check ภายใน transaction)
        $db = \Config\Database::connect();
        $db->transStart();

        $capError = $this->checkCapacity($competitionKey, $comp, $levelKey, $schoolName);
        if ($capError !== null) {
            $db->transRollback();
            return redirect()->back()->with('error', $capError)->withInput();
        }

        // สร้าง extra JSON (ครูที่ปรึกษาคนที่ 2, ฟิลด์พิเศษอื่น ๆ)
        $extra = [];
        if (!empty($post['coach2_name'])) {
            $extra['coach2'] = [
                'name'  => $post['coach2_name'],
                'phone' => $post['coach2_phone'] ?? '',
                'email' => $post['coach2_email'] ?? '',
                'line'  => $post['coach2_line'] ?? '',
            ];
        }

        // บันทึก registration
        $regId = $this->regModel->insert([
            'competition_key' => $competitionKey,
            'level_key'       => $levelKey,
            'school_name'     => $schoolName,
            'school_address'  => $post['school_address'] ?? null,
            'contact_phone'   => $post['contact_phone'],
            'contact_email'   => $post['contact_email'] ?? null,
            'team_name'       => $post['team_name'] ?? null,
            'coach_name'      => $post['coach_name'],
            'coach_position'  => $post['coach_position'] ?? null,
            'coach_phone'     => $post['coach_phone'] ?? null,
            'coach_email'     => $post['coach_email'] ?? null,
            'extra'           => empty($extra) ? [] : $extra,
            'status'          => 'pending',
            'ip_address'      => $this->request->getIPAddress(),
        ]);

        // บันทึก participants
        $participants = $this->extractParticipants($post, $comp);
        foreach ($participants as $idx => $p) {
            $this->partModel->insert(array_merge(['registration_id' => $regId, 'sort_order' => $idx], $p));
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่')->withInput();
        }

        return redirect()->to(base_url('scienceweek/success/'.$regId));
    }

    /** หน้ายืนยันหลังบันทึกสำเร็จ */
    public function success(int $regId): string
    {
        $reg = $this->regModel->find($regId);
        if ($reg === null) {
            return $this->redirectNotFound();
        }
        $participants = $this->partModel->getByRegistration($regId);
        $comp         = $this->cfg->competitions[$reg['competition_key']] ?? [];

        return view('scienceweek/success', [
            'reg'          => $reg,
            'participants' => $participants,
            'comp'         => $comp,
        ]);
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    private function buildValidationRules(array $comp): array
    {
        $levels = implode(',', array_keys($comp['levels']));
        $rules  = [
            'level_key'    => "required|in_list[{$levels}]",
            'school_name'  => 'required|max_length[255]',
            'contact_phone'=> 'required|max_length[40]',
            'coach_name'   => 'required|max_length[190]',
        ];

        // CI4 validation ใช้ dot notation สำหรับ nested arrays
        $min = $comp['team_min'];
        $max = $comp['team_max'];
        for ($i = 0; $i < $max; $i++) {
            $required = $i < $min ? 'required' : 'permit_empty';
            $rules["participants.{$i}.full_name"] = "{$required}|max_length[190]";

            // ฟิลด์พิเศษต่อคน
            foreach ($comp['per_person'] as $field => $meta) {
                $req = ($meta['required'] && $i < $min) ? 'required' : 'permit_empty';
                $rules["participants.{$i}.{$field}"] = "{$req}|max_length[190]";
            }
        }

        // ตัวสำรอง
        if ($comp['has_reserve']) {
            for ($i = 0; $i < $comp['reserve_max']; $i++) {
                $rules["reserves.{$i}.full_name"] = 'permit_empty|max_length[190]';
                foreach ($comp['per_person'] as $field => $meta) {
                    $rules["reserves.{$i}.{$field}"] = 'permit_empty|max_length[190]';
                }
            }
        }

        return $rules;
    }

    private function checkCapacity(string $key, array $comp, string $levelKey, string $schoolName): ?string
    {
        // cap_per_level
        if ($comp['cap_per_level'] !== null) {
            $cnt = $this->regModel->countByLevel($key, $levelKey);
            if ($cnt >= $comp['cap_per_level']) {
                return "ปิดรับสมัครแล้ว ระดับนี้เต็มแล้ว ({$cnt}/{$comp['cap_per_level']} ทีม)";
            }
        }
        // cap_total
        if ($comp['cap_total'] !== null) {
            $total = $this->regModel->countTotal($key);
            if ($total >= $comp['cap_total']) {
                return "ปิดรับสมัครแล้ว จำนวนทีมรวมเต็มแล้ว ({$total}/{$comp['cap_total']} ทีม)";
            }
        }
        // cap_per_school per level (ROV)
        if ($comp['cap_per_school'] !== null && $key === 'rov') {
            $cnt = $this->regModel->countBySchoolAndLevel($key, $levelKey, $schoolName);
            if ($cnt >= $comp['cap_per_school']) {
                return "สถาบันของท่านได้ส่งทีมในระดับนี้ครบ {$comp['cap_per_school']} ทีมแล้ว";
            }
        }
        // cap_per_school total (python)
        if ($comp['cap_per_school'] !== null && $key === 'python') {
            $cnt = $this->regModel->countBySchool($key, $schoolName);
            if ($cnt >= $comp['cap_per_school']) {
                return "สถาบันของท่านได้ส่งทีมครบ {$comp['cap_per_school']} ทีมแล้ว";
            }
        }
        return null;
    }

    private function extractParticipants(array $post, array $comp): array
    {
        $list = [];
        $rawMain = $post['participants'] ?? [];
        $i = 0;
        foreach ($rawMain as $p) {
            if (empty(trim($p['full_name'] ?? ''))) {
                continue;
            }
            $row = [
                'full_name'   => trim($p['full_name']),
                'level_class' => trim($p['level_class'] ?? ''),
                'role'        => 'main',
                'game_id'     => isset($p['game_id']) ? trim($p['game_id']) : null,
                'age'         => isset($p['age']) && $p['age'] !== '' ? (int)$p['age'] : null,
                'occupation'  => isset($p['occupation']) ? trim($p['occupation']) : null,
                'line_id'     => isset($p['line_id']) ? trim($p['line_id']) : null,
            ];
            $list[] = $row;
            $i++;
        }
        if ($comp['has_reserve']) {
            foreach (($post['reserves'] ?? []) as $p) {
                if (empty(trim($p['full_name'] ?? ''))) {
                    continue;
                }
                $list[] = [
                    'full_name'   => trim($p['full_name']),
                    'level_class' => trim($p['level_class'] ?? ''),
                    'role'        => 'reserve',
                    'game_id'     => isset($p['game_id']) ? trim($p['game_id']) : null,
                    'age'         => null,
                    'occupation'  => null,
                    'line_id'     => null,
                ];
            }
        }
        return $list;
    }

    private function redirectNotFound(): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to(base_url('scienceweek'))->with('error', 'ไม่พบรายการแข่งขันที่ระบุ');
    }

    private function getDeadlineMessage(array $comp): ?string
    {
        if ($comp['deadline'] === null) {
            return null;
        }
        $deadline = \DateTime::createFromFormat('Y-m-d', $comp['deadline']);
        $thYear   = (int)$deadline->format('Y') + 543;
        $months   = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                     'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $label = $deadline->format('j').' '.$months[(int)$deadline->format('n')].' พ.ศ. '.$thYear;

        if (date('Y-m-d') > $comp['deadline']) {
            return "ปิดรับสมัครแล้ว (ปิดรับ {$label})";
        }
        return "เปิดรับสมัครถึงวันที่ {$label}";
    }
}
