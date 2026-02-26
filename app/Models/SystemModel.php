<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemModel extends Model
{
    protected $table            = 'systems';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'slug',
        'name_th',
        'name_en',
        'description',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'slug'     => 'required|max_length[50]|is_unique[systems.slug]',
        'name_th'  => 'required|max_length[100]',
        'name_en'  => 'permit_empty|max_length[100]',
    ];

    /**
     * ดึงระบบตาม slug
     */
    public function getBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)
                    ->where('is_active', 1)
                    ->first();
    }

    /**
     * ดึงระบบทั้งหมดที่ active (เรียงตาม sort_order)
     */
    public function getAllActive(): array
    {
        return $this->where('is_active', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * ดึง id จาก slug
     */
    public function getIdBySlug(string $slug): ?int
    {
        $result = $this->select('id')
                        ->where('slug', $slug)
                        ->first();
        return $result ? (int) $result['id'] : null;
    }
}
