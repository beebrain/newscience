<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicationContributorModel extends Model
{
    protected $table         = 'publication_contributors';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'publication_id',
        'contributor_email_norm',
        'contributor_name_key',
        'display_name',
        'personnel_id',
        'contributor_affinity',
        'rr_user_uid',
        'rr_faculty_id',
        'author_order',
        'corresponding',
        'affiliation',
        'source',
    ];
}
