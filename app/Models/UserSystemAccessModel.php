<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSystemAccessModel extends Model
{
    protected $table            = 'user_system_access';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'user_uid',
        'user_email',
        'system_id',
        'access_level',
        'granted_by',
        'granted_at',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'system_id'    => 'required|integer',
        'access_level' => 'required|in_list[view,manage,admin]',
    ];

    /** Resolve uid to email (uses user_email as primary key in this table) */
    private function uidToEmail(int $userUid): ?string
    {
        $user = (new \App\Models\UserModel())->find($userUid);
        return $user['email'] ?? null;
    }

    /**
     * เช็คว่า user มีสิทธิ์เข้าระบบนี้หรือไม่ (ใช้ email เป็นหลักในตาราง)
     */
    public function hasAccess(int $userUid, string $systemSlug, string $minLevel = 'view'): bool
    {
        $email = $this->uidToEmail($userUid);
        return $email !== null && $this->hasAccessByEmail($email, $systemSlug, $minLevel);
    }

    /**
     * เช็คสิทธิ์จากอีเมลโดยตรง
     */
    public function hasAccessByEmail(string $email, string $systemSlug, string $minLevel = 'view'): bool
    {
        $systemModel = new SystemModel();
        $system = $systemModel->getBySlug($systemSlug);
        if (!$system) {
            return false;
        }
        $access = $this->where('user_email', $email)->where('system_id', $system['id'])->first();
        if (!$access) {
            return false;
        }
        $levels = ['view' => 1, 'manage' => 2, 'admin' => 3];
        $userLevel = $levels[$access['access_level']] ?? 0;
        $requiredLevel = $levels[$minLevel] ?? 1;
        return $userLevel >= $requiredLevel;
    }

    /**
     * ดึงระดับสิทธิ์ของ user ในระบบนั้น (null = ไม่มีสิทธิ์)
     */
    public function getAccessLevel(int $userUid, string $systemSlug): ?string
    {
        $email = $this->uidToEmail($userUid);
        if ($email === null) {
            return null;
        }
        $systemModel = new SystemModel();
        $system = $systemModel->getBySlug($systemSlug);
        if (!$system) {
            return null;
        }
        $access = $this->where('user_email', $email)->where('system_id', $system['id'])->first();
        return $access ? $access['access_level'] : null;
    }

    /**
     * ดึงรายการระบบทั้งหมดที่ user เข้าถึงได้
     */
    public function getUserSystems(int $userUid): array
    {
        $email = $this->uidToEmail($userUid);
        if ($email === null) {
            return [];
        }
        $builder = $this->select('user_system_access.*, systems.slug, systems.name_th, systems.name_en, systems.description, systems.icon')
            ->join('systems', 'systems.id = user_system_access.system_id')
            ->where('systems.is_active', 1)
            ->orderBy('systems.sort_order', 'ASC');
        if ($this->db->fieldExists('user_email', 'user_system_access')) {
            $builder->where('user_system_access.user_email', $email);
        } else {
            $builder->where('user_system_access.user_uid', $userUid);
        }
        return $builder->findAll();
    }

    /**
     * กำหนด/อัปเดตสิทธิ์ (ใช้ email เป็นหลัก)
     */
    public function grantAccess(int $userUid, string $systemSlug, string $level = 'view', ?int $grantedBy = null): bool
    {
        $email = $this->uidToEmail($userUid);
        if ($email === null) {
            return false;
        }
        return $this->grantAccessByEmail($email, $systemSlug, $level, $userUid, $grantedBy);
    }

    /**
     * กำหนดสิทธิ์ด้วยอีเมล (ใช้เป็นหลักในตาราง)
     */
    public function grantAccessByEmail(string $email, string $systemSlug, string $level = 'view', ?int $userUid = null, ?int $grantedBy = null): bool
    {
        $systemModel = new SystemModel();
        $system = $systemModel->getBySlug($systemSlug);
        if (!$system) {
            return false;
        }
        $data = [
            'system_id'    => $system['id'],
            'access_level' => $level,
            'granted_by'   => $grantedBy,
            'granted_at'   => date('Y-m-d H:i:s'),
        ];
        if ($this->db->fieldExists('user_email', 'user_system_access')) {
            $data['user_email'] = $email;
        }
        if ($userUid !== null && $this->db->fieldExists('user_uid', 'user_system_access')) {
            $data['user_uid'] = $userUid;
        }
        $existing = $this->where('user_email', $email)->where('system_id', $system['id'])->first();
        if ($existing) {
            return $this->update($existing['id'], $data);
        }
        return $this->insert($data) !== false;
    }

    /**
     * ยกเลิกสิทธิ์
     */
    public function revokeAccess(int $userUid, string $systemSlug): bool
    {
        $email = $this->uidToEmail($userUid);
        if ($email === null) {
            return false;
        }
        $systemModel = new SystemModel();
        $system = $systemModel->getBySlug($systemSlug);
        if (!$system) {
            return false;
        }
        if ($this->db->fieldExists('user_email', 'user_system_access')) {
            return $this->where('user_email', $email)->where('system_id', $system['id'])->delete();
        }
        return $this->where('user_uid', $userUid)->where('system_id', $system['id'])->delete();
    }

    /**
     * ดึงรายการ user ทั้งหมดที่มีสิทธิ์ในระบบนั้น
     */
    public function getSystemUsers(string $systemSlug): array
    {
        $systemModel = new SystemModel();
        $system = $systemModel->getBySlug($systemSlug);
        if (!$system) {
            return [];
        }
        $builder = $this->select('user_system_access.*, user.email, user.gf_name AS thai_name, user.gl_name AS thai_lastname, user.role')
            ->where('user_system_access.system_id', $system['id']);
        if ($this->db->fieldExists('user_email', 'user_system_access')) {
            $builder->join('user', 'user.email = user_system_access.user_email');
        } else {
            $builder->join('user', 'user.uid = user_system_access.user_uid');
        }
        return $builder->findAll();
    }

    /**
     * Migrate ข้อมูลเดิมจาก user.edoc และ user.admin_edoc
     */
    public function migrateFromUserTable(): array
    {
        $result = ['edoc' => 0, 'edoc_admin' => 0];
        
        // Migrate edoc = 1 → edoc (view)
        $edocUsers = $this->db->query("SELECT uid FROM user WHERE edoc = 1");
        foreach ($edocUsers->getResultArray() as $user) {
            if ($this->grantAccess($user['uid'], 'edoc', 'view')) {
                $result['edoc']++;
            }
        }

        // Migrate admin_edoc = 1 → edoc_admin (admin)
        $adminEdocUsers = $this->db->query("SELECT uid FROM user WHERE admin_edoc = 1");
        foreach ($adminEdocUsers->getResultArray() as $user) {
            if ($this->grantAccess($user['uid'], 'edoc_admin', 'admin')) {
                $result['edoc_admin']++;
            }
        }

        return $result;
    }

    /**
     * ดึงข้อมูลสิทธิ์ทั้งหมดของ user ในรูปแบบ key-value (slug => level) — ใช้ email เป็นหลัก
     */
    public function getUserAccessMap(int $userUid): array
    {
        $email = $this->uidToEmail($userUid);
        if ($email === null) {
            return [];
        }
        $col = $this->db->fieldExists('user_email', 'user_system_access') ? 'user_email' : 'user_uid';
        $value = $col === 'user_email' ? $email : $userUid;
        $accesses = $this->select('systems.slug, user_system_access.access_level')
            ->join('systems', 'systems.id = user_system_access.system_id')
            ->where("user_system_access.{$col}", $value)
            ->where('systems.is_active', 1)
            ->findAll();
        $map = [];
        foreach ($accesses as $access) {
            $map[$access['slug']] = $access['access_level'];
        }
        return $map;
    }

    /**
     * ดึงข้อมูลสิทธิ์จากอีเมล (ใช้ในหน้าแก้ไขผู้ใช้)
     */
    public function getUserAccessMapByEmail(string $email): array
    {
        if (!$this->db->fieldExists('user_email', 'user_system_access')) {
            $user = (new \App\Models\UserModel())->findByIdentifier($email);
            return $user ? $this->getUserAccessMap((int) $user['uid']) : [];
        }
        $accesses = $this->select('systems.slug, user_system_access.access_level')
            ->join('systems', 'systems.id = user_system_access.system_id')
            ->where('user_system_access.user_email', $email)
            ->where('systems.is_active', 1)
            ->findAll();
        $map = [];
        foreach ($accesses as $access) {
            $map[$access['slug']] = $access['access_level'];
        }
        return $map;
    }
}
