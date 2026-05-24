<?php

namespace App\Controllers\ScienceWeek;

use App\Controllers\BaseController;
use App\Models\SwRegistrationModel;
use App\Models\SwParticipantModel;
use Config\SciWeek;

/**
 * อาจารย์/บุคลากรที่ login (loggedin filter) ดู/ลบรายชื่อผู้สมัคร
 */
class Manage extends BaseController
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

    /** รายการผู้สมัครทั้งหมด (กรองตาม competition/level) */
    public function index(): string
    {
        $compKey  = $this->request->getGet('competition') ?? '';
        $levelKey = $this->request->getGet('level') ?? '';
        $showDeleted = (bool) $this->request->getGet('show_deleted');

        $competitions = $this->cfg->competitions;
        $registrations = [];
        $participants  = [];

        if ($compKey !== '' && isset($competitions[$compKey])) {
            $lk = $levelKey !== '' ? $levelKey : null;

            if ($showDeleted) {
                $registrations = $this->regModel->getAllIncludingDeleted($compKey, $lk);
            } else {
                $query = $this->regModel->where('competition_key', $compKey);
                if ($lk !== null) {
                    $query->where('level_key', $lk);
                }
                $registrations = $query->orderBy('created_at', 'ASC')->findAll();
            }

            if (!empty($registrations)) {
                $ids = array_column($registrations, 'id');
                $participants = $this->partModel->getGroupedByRegistrations($ids);
            }
        }

        // นับสรุปต่อรายการ
        $summary = [];
        foreach ($competitions as $key => $comp) {
            foreach (array_keys($comp['levels']) as $lk) {
                $summary[$key][$lk] = [
                    'count' => $this->regModel->countByLevel($key, $lk),
                    'cap'   => $comp['cap_per_level'],
                ];
            }
        }

        return view('scienceweek/manage/list', [
            'competitions'  => $competitions,
            'compKey'       => $compKey,
            'levelKey'      => $levelKey,
            'registrations' => $registrations,
            'participants'  => $participants,
            'summary'       => $summary,
            'showDeleted'   => $showDeleted,
        ]);
    }

    /** รายละเอียดการสมัคร (รวมข้อมูลที่ถูกลบ) */
    public function detail(int $id): string
    {
        $reg = $this->regModel->withDeleted()->find($id);
        if ($reg === null) {
            return redirect()->to(base_url('scienceweek/manage'))->with('error', 'ไม่พบข้อมูล');
        }
        $participants = $this->partModel->getByRegistration($id);
        $comp = $this->cfg->competitions[$reg['competition_key']] ?? [];

        return view('scienceweek/manage/detail', [
            'reg'          => $reg,
            'participants' => $participants,
            'comp'         => $comp,
        ]);
    }

    /** Soft-delete การสมัคร */
    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $reg = $this->regModel->find($id);
        if ($reg === null) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูล หรือถูกลบไปแล้ว');
        }

        $this->regModel->delete($id);

        $adminName = session('admin_name') ?? session('admin_email') ?? 'อาจารย์';
        log_message('notice', "ScienceWeek: registration #{$id} deleted by {$adminName}");

        return redirect()->to(
            base_url("scienceweek/manage?competition={$reg['competition_key']}&level={$reg['level_key']}")
        )->with('success', "ลบข้อมูลการสมัคร #{$id} ({$reg['school_name']}) เรียบร้อยแล้ว");
    }
}
