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
            $query = $this->db->table($this->table)
                ->select('id, first_name, last_name, nickname, email')
                ->orderBy('first_name', 'ASC')
                ->get();

            return $query->getResultArray();
        } catch (\Exception $e) {
            log_message('error', '[EdoctagModel::getAllData] Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search for tags by name
     * 
     * @param string $searchTerm
     * @return array
     */
    public function searchTags($searchTerm)
    {
        try {
            $query = $this->db->table($this->table)
                ->select('first_name, last_name, nickname')
                ->groupStart()
                ->like('first_name', $searchTerm)
                ->orLike('last_name', $searchTerm)
                ->orLike('nickname', $searchTerm)
                ->groupEnd()
                ->orderBy('first_name', 'ASC')
                ->get();

            return $query->getResultArray();
        } catch (\Exception $e) {
            log_message('error', '[EdoctagModel::searchTags] Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Add new tag
     * 
     * @param array $data
     * @return bool|int
     */
    public function addTag($data)
    {
        try {
            if (empty($data['first_name']) || empty($data['last_name'])) {
                return false;
            }

            return $this->insert($data);
        } catch (\Exception $e) {
            log_message('error', '[EdoctagModel::addTag] Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update existing tag
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateTag($id, $data)
    {
        try {
            if (empty($data['first_name']) || empty($data['last_name'])) {
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
     * Check if tag exists
     * 
     * @param string $firstName
     * @param string $lastName
     * @return bool
     */
    public function tagExists($firstName, $lastName)
    {
        try {
            $count = $this->db->table($this->table)
                ->where('first_name', $firstName)
                ->where('last_name', $lastName)
                ->countAllResults();

            return $count > 0;
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
     * Validate tag data
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
                'rules' => 'required|max_length[50]'
            ],
            'last_name' => [
                'label' => 'Last Name',
                'rules' => 'required|max_length[50]'
            ],
            'nickname' => [
                'label' => 'Nickname',
                'rules' => 'permit_empty|max_length[50]'
            ]
        ];

        $validation->setRules($rules);

        if ($validation->run($data)) {
            return true;
        }

        return $validation->getErrors();
    }
}
