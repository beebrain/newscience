<?php

namespace App\Models\Evaluate;

use CodeIgniter\Model;

/**
 * สิทธิ์รายบุคคลสำหรับระบบประเมินผลการสอน
 * can_submit_teaching = ส่งคำร้องขอประเมิน, can_be_referee = เป็นผู้ประเมินได้, can_manage_evaluate = จัดการระบบประเมิน
 */
class EvaluateUserRightsModel extends Model
{
    protected $table            = 'evaluate_user_rights';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'uid',
        'can_submit_teaching',
        'can_be_referee',
        'can_manage_evaluate',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    public function getRightsByUid(int $uid): ?array
    {
        $row = $this->where('uid', $uid)->first();
        return is_array($row) ? $row : null;
    }

    public function canSubmitTeaching(int $uid): bool
    {
        $r = $this->getRightsByUid($uid);
        return $r && (int) ($r['can_submit_teaching'] ?? 0) === 1;
    }

    public function canBeReferee(int $uid): bool
    {
        $r = $this->getRightsByUid($uid);
        return $r && (int) ($r['can_be_referee'] ?? 0) === 1;
    }

    public function canManageEvaluate(int $uid): bool
    {
        $r = $this->getRightsByUid($uid);
        return $r && (int) ($r['can_manage_evaluate'] ?? 0) === 1;
    }

    public function setRights(int $uid, bool $canSubmit, bool $canReferee, bool $canManage): bool
    {
        $existing = $this->where('uid', $uid)->first();
        $data = [
            'can_submit_teaching'  => $canSubmit ? 1 : 0,
            'can_be_referee'       => $canReferee ? 1 : 0,
            'can_manage_evaluate'  => $canManage ? 1 : 0,
        ];
        if (is_array($existing)) {
            return (bool) $this->update($existing['id'], $data);
        }
        $data['uid'] = $uid;
        return (bool) $this->insert($data);
    }

    public function getAllWithUsers(): array
    {
        $rights = $this->findAll();
        $userModel = new \App\Models\UserModel();
        $list = [];
        foreach ($rights as $r) {
            $u = $userModel->find($r['uid']);
            $r['user_name'] = $u ? ($u['gf_name'] ?? $u['tf_name'] ?? '') . ' ' . ($u['gl_name'] ?? $u['tl_name'] ?? '') : '';
            $r['user_email'] = $u['email'] ?? '';
            $list[] = $r;
        }
        return $list;
    }
}
