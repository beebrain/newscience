<?php

namespace App\Controllers\ScienceWeek;

use App\Controllers\BaseController;
use App\Models\SwRegistrationModel;
use App\Models\SwParticipantModel;
use Config\SciWeek;

class Verify extends BaseController
{
    /** หน้า list สาธารณะ — ผู้สมัครตรวจสอบรายชื่อ */
    public function index(): string
    {
        $cfg      = config('SciWeek');
        $compKey  = $this->request->getGet('competition') ?? '';
        $levelKey = $this->request->getGet('level') ?? '';

        $competitions = $cfg->competitions;
        $registrations = [];
        $participants  = [];
        $selectedComp  = null;
        $selectedLevel = null;

        if ($compKey !== '' && isset($competitions[$compKey])) {
            $selectedComp  = $competitions[$compKey];
            $selectedLevel = $levelKey !== '' && isset($selectedComp['levels'][$levelKey]) ? $levelKey : null;

            $regModel  = new SwRegistrationModel();
            $partModel = new SwParticipantModel();

            if ($selectedLevel !== null) {
                $registrations = $regModel->getWithParticipants($compKey, $levelKey);
            } else {
                // แสดงทุกระดับ
                foreach (array_keys($selectedComp['levels']) as $lk) {
                    $registrations = array_merge($registrations, $regModel->getWithParticipants($compKey, $lk));
                }
            }

            if (!empty($registrations)) {
                $ids = array_column($registrations, 'id');
                $participants = $partModel->getGroupedByRegistrations($ids);
            }
        }

        return view('scienceweek/verify', [
            'competitions'  => $competitions,
            'compKey'       => $compKey,
            'levelKey'      => $levelKey,
            'selectedComp'  => $selectedComp,
            'selectedLevel' => $selectedLevel,
            'registrations' => $registrations,
            'participants'  => $participants,
        ]);
    }
}
