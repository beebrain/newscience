<?php

namespace App\Models;

use CodeIgniter\Model;

class CertTemplateModel extends Model
{
    protected $table = 'cert_templates';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name_th',
        'name_en',
        'level',
        'template_file',
        'field_mapping',
        'signature_x',
        'signature_y',
        'qr_x',
        'qr_y',
        'qr_size',
        'status',
        'created_by',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getActiveTemplates(?string $level = null): array
    {
        $builder = $this->where('status', 'active');
        if ($level !== null) {
            $builder->where('level', $level);
        }
        return $builder->orderBy('name_th', 'ASC')->findAll();
    }
}
