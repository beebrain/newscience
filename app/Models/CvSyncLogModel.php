<?php

namespace App\Models;

use CodeIgniter\Model;

class CvSyncLogModel extends Model
{
    protected $table         = 'cv_sync_log';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'personnel_id',
        'direction',
        'ns_content_hash',
        'rr_content_hash',
        'decisions_json',
        'created_at',
    ];
}
