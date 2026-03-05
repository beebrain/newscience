<?php

namespace App\Models\Edoc;

use CodeIgniter\Model;

class EdoctagModel extends Model
{
    protected $table = 'edoctag';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'first_name',
        'last_name',
        'nickname',
        'email'
    ];

    protected $useTimestamps = false;

    /**
     * Get all tag data
     * 
     * @return array
     */
    public function getAllData()
    {
        try {
            $select = 'id, first_name, last_name, nickname';
            if ($this->db->fieldExists('email', 'edoctag')) {
                $select .= ', email';
            }
            $query = $this->db->table($this->table)
                ->select($select)
                ->orderBy('first_name', 'ASC')
                ->get();

            return $query->getResultArray();
        } catch (\Exception $e) {
            log_message('error', '[EdoctagModel::getAllData] Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search for tags by name or email
     *
     * @param string $searchTerm
     * @return array
     */
    public function searchTags($searchTerm)
    {
        try {
            $builder = $this->db->table($this->table)
                ->select('id, first_name, last_name, nickname, email')
                ->groupStart()
                ->like('first_name', $searchTerm)
                ->orLike('last_name', $searchTerm)
                ->orLike('nickname', $searchTerm);
            if ($this->db->fieldExists('email', 'edoctag')) {
                $builder->orLike('email', $searchTerm);
            }
            $query = $builder->groupEnd()
                ->orderBy('first_name', 'ASC')
                ->get();

            return $query->getResultArray();
        } catch (\Exception $e) {
            log_message('error', '[EdoctagModel::searchTags] Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Add new tag (identity by name or email)
     *
     * @param array $data
     * @return bool|int
     */
    public function addTag($data)
    {
        try {
            $hasName = !empty(trim((string) ($data['first_name'] ?? ''))) && !empty(trim((string) ($data['last_name'] ?? '')));
            $hasEmail = !empty(trim((string) ($data['email'] ?? '')));
            if (!$hasName && !$hasEmail) {
                return false;
            }

            return $this->insert($data);
        } catch (\Exception $e) {
            log_message('error', '[EdoctagModel::addTag] Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update existing tag (identity by name or email)
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateTag($id, $data)
    {
        try {
            $hasName = !empty(trim((string) ($data['first_name'] ?? ''))) && !empty(trim((string) ($data['last_name'] ?? '')));
            $hasEmail = !empty(trim((string) ($data['email'] ?? '')));
            if (!$hasName && !$hasEmail) {
                return false;
            }

            return $this->update($id, $data);
        } catch (\Exception $e) {
            log_message('error', '[EdoctagModel::updateTag] Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a tag
     * 
     * @param int $id
     * @return bool
     */
    public function deleteTag($id)
    {
        try {
            return $this->delete($id);
        } catch (\Exception $e) {
            log_message('error', '[EdoctagModel::deleteTag] Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if tag exists by name or by email
     *
     * @param string $firstName
     * @param string $lastName
     * @param string|null $email Optional; if provided, also check by email
     * @return bool
     */
    public function tagExists($firstName, $lastName, $email = null)
    {
        try {
            $builder = $this->db->table($this->table);
            $builder->groupStart()
                ->where('first_name', $firstName)
                ->where('last_name', $lastName)
                ->groupEnd();
            if ($email !== null && $email !== '' && $this->db->fieldExists('email', 'edoctag')) {
                $builder->orWhere('email', $email);
            }

            return $builder->countAllResults() > 0;
        } catch (\Exception $e) {
            log_message('error', '[EdoctagModel::tagExists] Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get tag by email
     * 
     * @param string $email
     * @return array|null
     */
    public function getTagByEmail($email)
    {
        try {
            if (!$this->db->fieldExists('email', 'edoctag')) {
                return null;
            }
            return $this->db->table($this->table)
                ->where('email', $email)
                ->get()
                ->getRowArray();
        } catch (\Exception $e) {
            log_message('error', '[EdoctagModel::getTagByEmail] Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get tag by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getTagById($id)
    {
        try {
            return $this->find($id);
        } catch (\Exception $e) {
            log_message('error', '[EdoctagModel::getTagById] Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate tag data (identity by name or email)
     *
     * @param array $data
     * @return bool|array Returns true if valid, array of errors if invalid
     */
    public function validateTagData($data)
    {
        $validation = \Config\Services::validation();

        $rules = [
            'first_name' => [
                'label' => 'First Name',
                'rules' => 'permit_empty|max_length[100]',
            ],
            'last_name' => [
                'label' => 'Last Name',
                'rules' => 'permit_empty|max_length[100]',
            ],
            'nickname' => [
                'label' => 'Nickname',
                'rules' => 'permit_empty|max_length[100]',
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'permit_empty|valid_email|max_length[255]',
            ],
        ];
        $validation->setRules($rules);

        if (!$validation->run($data)) {
            return $validation->getErrors();
        }

        $hasName = !empty(trim((string) ($data['first_name'] ?? ''))) && !empty(trim((string) ($data['last_name'] ?? '')));
        $hasEmail = !empty(trim((string) ($data['email'] ?? '')));
        if (!$hasName && !$hasEmail) {
            return ['identity' => 'กรุณาระบุชื่อ-นามสกุล หรือ อีเมล เพื่อระบุตัวตน'];
        }

        return true;
    }
}
