<?php

namespace App\Models;

use CodeIgniter\Model;

class ExamScheduleUserLinkModel extends Model
{
    protected $table            = 'exam_schedule_user_links';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'schedule_id',
        'user_uid',
        'link_role',
        'matched_value',
        'match_source',
        'confidence',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get links for a schedule
     */
    public function getBySchedule(int $scheduleId): array
    {
        return $this->where('schedule_id', $scheduleId)
            ->join('user', 'user.uid = exam_schedule_user_links.user_uid')
            ->select('exam_schedule_user_links.*, user.nickname, user.tf_name, user.tl_name, user.email')
            ->findAll();
    }

    /**
     * Get all schedules for a user
     */
    public function getByUser(int $userUid): array
    {
        return $this->where('user_uid', $userUid)
            ->join('exam_schedules', 'exam_schedules.id = exam_schedule_user_links.schedule_id')
            ->select('exam_schedules.*, exam_schedule_user_links.link_role')
            ->findAll();
    }

    /**
     * Create link between schedule and user
     */
    public function createLink(int $scheduleId, int $userUid, string $role, string $matchedValue, string $source = 'manual', ?float $confidence = null): bool
    {
        return $this->insert([
            'schedule_id'   => $scheduleId,
            'user_uid'      => $userUid,
            'link_role'     => $role,
            'matched_value' => $matchedValue,
            'match_source'  => $source,
            'confidence'    => $confidence,
        ]) !== false;
    }

    /**
     * Delete existing links for a schedule
     */
    public function clearScheduleLinks(int $scheduleId): bool
    {
        return $this->where('schedule_id', $scheduleId)->delete();
    }

    /**
     * Auto-match by nickname
     */
    public function autoMatchByNickname(int $scheduleId, string $examinerText, string $role, UserModel $userModel): ?int
    {
        $user = $userModel->findByNickname($examinerText);
        
        if (!$user) {
            // Try partial match
            $users = $userModel->searchByNickname($examinerText);
            if (count($users) === 1) {
                $user = $users[0];
            }
        }

        if ($user) {
            $this->createLink(
                $scheduleId,
                $user['uid'],
                $role,
                $examinerText,
                'auto_nickname',
                1.0
            );
            return $user['uid'];
        }

        return null;
    }

    /**
     * Get match statistics for a batch
     */
    public function getMatchStats(int $batchId): array
    {
        $builder = $this->db->table('exam_schedules');
        
        $total = $builder->where('batch_id', $batchId)->countAllResults();
        
        $builder = $this->db->table('exam_schedules');
        $matched = $builder->where('batch_id', $batchId)
            ->whereIn('id', function($subquery) {
                return $subquery->select('schedule_id')
                    ->from('exam_schedule_user_links')
                    ->whereIn('link_role', ['examiner1', 'examiner2']);
            })
            ->countAllResults();

        return [
            'total'   => $total,
            'matched' => $matched,
            'pending' => $total - $matched,
        ];
    }
}
