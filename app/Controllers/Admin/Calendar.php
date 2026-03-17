<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CalendarSchema;
use App\Models\CalendarEventModel;
use App\Models\CalendarParticipantModel;
use App\Models\UserModel;

/**
 * Admin Calendar - ปฏิทินนัดหมายกิจกรรมผู้บริหาร (CRUD ทุก event, tag ใครก็ได้)
 */
class Calendar extends BaseController
{
    protected CalendarEventModel $eventModel;
    protected CalendarParticipantModel $participantModel;
    protected UserModel $userModel;

    public function __construct()
    {
        CalendarSchema::ensure();
        $this->eventModel      = new CalendarEventModel();
        $this->participantModel = new CalendarParticipantModel();
        $this->userModel       = new UserModel();
    }

    /**
     * หน้าปฏิทิน (Admin) - แสดง FullCalendar + filter ตาม user
     */
    public function index()
    {
        $users = $this->userModel->getCalendarEligibleUsers();

        return view('admin/calendar/index', [
            'page_title' => 'ปฏิทินนัดหมายกิจกรรมผู้บริหาร',
            'users'      => $users,
            'is_admin'   => true,
        ]);
    }
}
