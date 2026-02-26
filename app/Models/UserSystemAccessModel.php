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
        'system_id',
        'access_level',
        'granted_by',
        'granted_at',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'user_uid'     => 'required|integer',
        'system_id'    => 'required|integer',
        'access_level' => 'required|in_list[view,manage,admin]',
    ];

    /**
     * เช็คว่า user มีสิทธิ์เข้าระบบนี้หรือไม่
     */
    public function hasAccess(int $userUid, string $systemSlug, string $minLevel = 'view'): bool
    {
        $systemModel = new SystemModel();
        $system = $systemModel->getBySlug($systemSlug);
        
        if (!$system) {
            return false;
        }

        $access = $this->where('user_uid', $userUid)
                        ->where('system_id', $system['id'])
                        ->first();

        if (!$access) {
            return false;
        }

        // Check level hierarchy: admin > manage > view
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
        $systemModel = new SystemModel();
        $system = $systemModel->getBySlug($systemSlug);
        
        if (!$system) {
            return null;
        }

        $access = $this->where('user_uid', $userUid)
                        ->where('system_id', $system['id'])
                        ->first();

        return $access ? $access['access_level'] : null;
    }

    /**
     * ดึงรายการระบบทั้งหมดที่ user เข้าถึงได้
     */
    public function getUserSystems(int $userUid): array
    {
        return $this->select('user_system_access.*, systems.slug, systems.name_th, systems.name_en, systems.description, systems.icon')
                    ->join('systems', 'systems.id = user_system_access.system_id')
                    ->where('user_system_access.user_uid', $userUid)
                    ->where('systems.is_active', 1)
                    ->orderBy('systems.sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * กำหนด/อัปเดตสิทธิ์
     */
    public function grantAccess(int $userUid, string $systemSlug, string $level = 'view', ?int $grantedBy = null): bool
    {
        $systemModel = new SystemModel();
        $system = $systemModel->getBySlug($systemSlug);
        
        if (!$system) {
            return false;
        }

        $data = [
            'user_uid'     => $userUid,
            'system_id'    => $system['id'],
            'access_level' => $level,
            'granted_by'   => $grantedBy,
            'granted_at'   => date('Y-m-d H:i:s'),
        ];

        // Check if exists
        $existing = $this->where('user_uid', $userUid)
                          ->where('system_id', $system['id'])
                          ->first();

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
        $systemModel = new SystemModel();
        $system = $systemModel->getBySlug($systemSlug);
        
        if (!$system) {
            return false;
        }

        return $this->where('user_uid', $userUid)
                      ->where('system_id', $system['id'])
                      ->delete();
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

        return $this->select('user_system_access.*, user.email, user.thai_name, user.thai_lastname, user.role')
                    ->join('user', 'user.uid = user_system_access.user_uid')
                    ->where('user_system_access.system_id', $system['id'])
                    ->findAll();
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
     * ดึงข้อมูลสิทธิ์ทั้งหมดของ user ในรูปแบบ key-value (slug => level)
     */
    public function getUserAccessMap(int $userUid): array
    {
        $accesses = $this->select('systems.slug, user_system_access.access_level')
                          ->join('systems', 'systems.id = user_system_access.system_id')
                          ->where('user_system_access.user_uid', $userUid)
                          ->where('systems.is_active', 1)
                          ->findAll();

        $map = [];
        foreach ($accesses as $access) {
            $map[$access['slug']] = $access['access_level'];
        }

        return $map;
    }
}
