<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicationModel extends Model
{
    protected $table         = 'publications';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'sync_external_key',
        'rr_publication_id',
        'title',
        'publication_year',
        'publication_type',
        'source',
        'doi',
        'doi_norm',
        'sync_origin',
        'last_synced_from',
        'content_hash',
        'metadata',
        'is_active',
    ];
}
