<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Pivot: User–Programs (user_programs)
 *
 * กฎธุรกิจ: User อาจอยู่ได้หลายหลักสูตร
 * ใช้ email ของ user เป็น key (user_email) — แหล่งความจริงสำหรับ "user อยู่หลักสูตรไหนบ้าง"
 *
 * DB: UNIQUE(user_email, program_id). is_primary ใช้ระบุหลักสูตรหลัก (ได้แค่ 1 ต่อ user ในแอป)
 */
class UserProgramModel extends Model
{
    protected $table = 'user_programs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_email', 'program_id', 'is_primary', 'sort_order'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * รายการหลักสูตรที่ user สังกัด (เรียง sort_order, หลักสูตรหลักก่อน)
     * Key: email ของ user
     */
    public function getByUserEmail(string $userEmail): array
    {
        $userEmail = trim($userEmail);
        if ($userEmail === '') {
            return [];
        }
        return $this->where('user_email', $userEmail)
            ->orderBy('is_primary', 'DESC')
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * รายการ program_id ที่ user สังกัด (ใช้เช็คสิทธิ์/กรองรายการหลักสูตร)
     * Key: email ของ user. ถ้า pivot ยังไม่มีข้อมูล return [] — caller ใช้ fallback user.program_id ได้
     */
    public function getProgramIdsForUser(string $userEmail): array
    {
        $rows = $this->getByUserEmail($userEmail);
        return array_map(fn($r) => (int) $r['program_id'], $rows);
    }

    /**
     * เช็คว่า user (โดย email) อยู่หลักสูตรนี้หรือไม่
     */
    public function userInProgram(string $userEmail, int $programId): bool
    {
        $userEmail = trim($userEmail);
        if ($userEmail === '') {
            return false;
        }
        return $this->where('user_email', $userEmail)
            ->where('program_id', $programId)
            ->countAllResults() > 0;
    }

    /**
     * ตั้งหลักสูตรของ user (แทนที่ทั้งหมด). is_primary = 1 ให้แถวแรก
     * Key: email ของ user
     */
    public function setProgramsForUser(string $userEmail, array $programIds): void
    {
        $userEmail = trim($userEmail);
        if ($userEmail === '') {
            return;
        }
        $this->db->table($this->table)->where('user_email', $userEmail)->delete();
        $programIds = array_values(array_unique(array_map('intval', $programIds)));
        foreach ($programIds as $i => $pid) {
            if ($pid <= 0) {
                continue;
            }
            $this->insert([
                'user_email' => $userEmail,
                'program_id' => $pid,
                'is_primary' => $i === 0 ? 1 : 0,
                'sort_order' => $i,
            ]);
        }
    }

    /**
     * เพิ่ม user (โดย email) เข้าหลักสูตร (ถ้ามีอยู่แล้วไม่ซ้ำ)
     */
    public function addUserToProgram(string $userEmail, int $programId, bool $isPrimary = false): bool
    {
        $userEmail = trim($userEmail);
        if ($userEmail === '') {
            return false;
        }
        if ($this->userInProgram($userEmail, $programId)) {
            return true;
        }
        if ($isPrimary) {
            $this->db->table($this->table)->where('user_email', $userEmail)->update(['is_primary' => 0]);
        }
        $sortOrder = $this->where('user_email', $userEmail)->countAllResults();
        return $this->insert([
            'user_email' => $userEmail,
            'program_id' => $programId,
            'is_primary' => $isPrimary ? 1 : 0,
            'sort_order' => $sortOrder,
        ]) !== false;
    }

    /**
     * ลบ user (โดย email) ออกจากหลักสูตร
     */
    public function removeUserFromProgram(string $userEmail, int $programId): bool
    {
        $userEmail = trim($userEmail);
        if ($userEmail === '') {
            return false;
        }
        $this->db->table($this->table)
            ->where('user_email', $userEmail)
            ->where('program_id', $programId)
            ->delete();
        return true;
    }
}
