<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\CalendarEventModel;
use App\Models\CalendarParticipantModel;
use App\Models\UserModel;

/**
 * User Calendar - ปฏิทินนัดหมาย (ดู/แก้ไขเฉพาะกิจกรรมของตนเอง, tag ได้แค่ตัวเอง)
 */
class CalendarController extends BaseController
{
    protected CalendarEventModel $eventModel;
    protected CalendarParticipantModel $participantModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->eventModel       = new CalendarEventModel();
        $this->participantModel = new CalendarParticipantModel();
        $this->userModel       = new UserModel();
    }

    /**
     * หน้าปฏิทิน (User) - แสดง FullCalendar เฉพาะของตัวเอง + ดูรวมได้
     */
    public function index()
    {
        $userId = (int) session()->get('admin_id');
        $user   = $this->userModel->find($userId);
        $users  = [];
        if ($user) {
            $users[] = [
                'uid'     => (int) $user['uid'],
                'email'   => $user['email'] ?? '',
                'name_th' => $this->userModel->getFullNameThaiForDisplay($user),
                'name_en' => trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? '')),
            ];
        }

        return view('user/calendar/index', [
            'page_title' => 'ปฏิทินนัดหมายของฉัน',
            'users'      => $users,
            'is_admin'   => false,
        ]);
    }
}
