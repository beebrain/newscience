<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'uid';
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
        'role',
        'profile_image',
        'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[user.email,uid,{uid}]',
        'password' => 'permit_empty|min_length[6]',
    ];

    /**
     * Find user by email
     */
    public function findByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Hash password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Get admins only
     */
    public function getAdmins()
    {
        return $this->where('role', 'admin')->where('status', 'active')->findAll();
    }

    /**
     * Get full name
     */
    public function getFullName(array $user): string
    {
        $title = $user['title'] ?? '';
        $firstName = $user['gf_name'] ?? $user['tf_name'] ?? '';
        $lastName = $user['gl_name'] ?? $user['tl_name'] ?? '';
        
        return trim("{$title} {$firstName} {$lastName}");
    }
}
