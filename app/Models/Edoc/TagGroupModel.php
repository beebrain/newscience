<?php

namespace App\Models\Edoc;

use CodeIgniter\Model;

class TagGroupModel extends Model
{
    protected $table = 'edoc_tag_groups';
    protected $primaryKey = 'id';
    protected $allowedFields = ['group_name', 'tags', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get all tag groups
     * 
     * @return array
     */
    public function getAll()
    {
        $groups = $this->findAll();
        // Decode tags if stored as JSON
        foreach ($groups as &$group) {
            $group['name'] = $group['group_name']; // Map group_name to name for frontend compatibility
            $group['tags'] = json_decode($group['tags'], true) ?? [];
        }
        return $groups;
    }

    /**
     * Save a new tag group or update existing
     * 
     * @param string $name
     * @param array $tags
     * @param int|null $id
     * @return array|bool
     */
    public function saveGroup($name, $tags, $id = null)
    {
        $data = [
            'group_name' => $name,
            'tags' => json_encode($tags, JSON_UNESCAPED_UNICODE)
        ];

        if ($id) {
            if ($this->update($id, $data)) {
                return $this->find($id);
            }
            return false;
        } else {
            $newId = $this->insert($data);
            if ($newId) {
                return $this->find($newId);
            }
            return false;
        }
    }

    /**
     * Delete a tag group
     * 
     * @param int $id
     * @return bool
     */
    public function deleteGroup($id)
    {
        return $this->delete($id);
    }
}
