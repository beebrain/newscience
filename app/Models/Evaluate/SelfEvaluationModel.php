<?php

namespace App\Models\Evaluate;

use CodeIgniter\Model;

/**
 * แบบประเมินของตนเอง — ทุกคนเข้าได้ ไม่ต้อง login
 */
class SelfEvaluationModel extends Model
{
    protected $table            = 'evaluate_self';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'uid',
        'name',
        'email',
        'academic_year',
        'semester',
        'score_1',
        'score_2',
        'score_3',
        'score_4',
        'score_5',
        'comment',
        'created_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $dateFormat    = 'datetime';

    public function getByUser(int $uid): array
    {
        return $this->where('uid', $uid)->orderBy('created_at', 'DESC')->findAll();
    }

    public function getByPeriod(?string $year, ?string $semester): array
    {
        $builder = $this->orderBy('created_at', 'DESC');
        if ($year !== null && $year !== '') {
            $builder->where('academic_year', $year);
        }
        if ($semester !== null && $semester !== '') {
            $builder->where('semester', $semester);
        }
        return $builder->findAll();
    }
}
