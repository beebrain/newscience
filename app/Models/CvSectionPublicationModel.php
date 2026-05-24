<?php

namespace App\Models;

use CodeIgniter\Model;

class CvSectionPublicationModel extends Model
{
    protected $table         = 'cv_section_publications';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'section_id',
        'publication_id',
        'sort_order',
        'visible_on_public',
    ];
}
