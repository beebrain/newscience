<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentUserModel extends Model
{
    protected $table = 'student_user';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'login_uid',
        'email',
        'password',
        'title',
        'gf_name',
        'gl_name',
        'tf_name',
        'tl_name',
        'th_name',
        'thai_lastname',
        'profile_image',
        'role',
        'status',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[student_user.email,id,{id}]',
        'password' => 'permit_empty|min_length[6]',
    ];

    /**
     * Find by email
     */
    public function findByEmail(string $email): ?array
    {
        $row = $this->where('email', $email)->first();
        return is_array($row) ? $row : null;
    }

    /**
     * Find by email or login_uid
     */
    public function findByIdentifier(string $login): ?array
    {
        $row = $this->db->table($this->table)
            ->where('email', $login)
            ->orWhere('login_uid', $login)
            ->limit(1)
            ->get()
            ->getRowArray();
        return $row !== null ? $row : null;
    }

    public function verifyPassword(string $password, $hash): bool
    {
        if ($hash === null || $hash === '') {
            return false;
        }
        return password_verify($password, $hash);
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Get full name (Thai preferred)
     */
    public function getFullName(array $row): string
    {
        $first = trim($row['th_name'] ?? $row['tf_name'] ?? '');
        $last  = trim($row['thai_lastname'] ?? $row['tl_name'] ?? '');
        $full  = trim($first . ' ' . $last);
        if ($full !== '') {
            return $full;
        }
        $firstEn = trim($row['gf_name'] ?? '');
        $lastEn  = trim($row['gl_name'] ?? '');
        return trim($firstEn . ' ' . $lastEn) ?: $row['email'] ?? '';
    }

    /**
     * Get club users (student_user with role=club)
     */
    public function getClubs()
    {
        return $this->where('role', 'club')->where('status', 'active')->findAll();
    }

    /**
     * List for dropdown (id, email, display name)
     */
    public function getListForDropdown(): array
    {
        $rows = $this->orderBy('email', 'ASC')->findAll();
        $list = [];
        foreach ($rows as $r) {
            $list[] = [
                'id' => (int) $r['id'],
                'email' => $r['email'] ?? '',
                'display_name' => $this->getFullName($r) ?: $r['email'],
            ];
        }
        return $list;
    }
}
