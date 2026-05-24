<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicationSyncStateModel extends Model
{
    protected $table         = 'publication_sync_state';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'sync_external_key',
        'rr_publication_id',
        'ns_publication_id',
        'sync_origin',
        'last_synced_from',
        'last_sync_direction',
        'content_hash_rr',
        'content_hash_ns',
        'last_synced_at',
    ];
}
