<?php

namespace App\Models;

use CodeIgniter\Model;

class SiteSettingModel extends Model
{
    protected $table = 'site_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'setting_key', 'setting_value', 'setting_type', 'category', 'description'
    ];
    protected $useTimestamps = true;
    protected $createdField = '';
    protected $updatedField = 'updated_at';
    
    /**
     * Get a setting value by key
     */
    public function getValue($key, $default = null)
    {
        $setting = $this->where('setting_key', $key)->first();
        return $setting ? $setting['setting_value'] : $default;
    }
    
    /**
     * Set a setting value
     */
    public function setValue($key, $value, $type = 'text', $category = 'general')
    {
        $existing = $this->where('setting_key', $key)->first();
        
        if ($existing) {
            return $this->update($existing['id'], ['setting_value' => $value]);
        }
        
        return $this->insert([
            'setting_key' => $key,
            'setting_value' => $value,
            'setting_type' => $type,
            'category' => $category
        ]);
    }
    
    /**
     * Get all settings as key-value array
     */
    public function getAll()
    {
        $settings = $this->findAll();
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        return $result;
    }
    
    /**
     * Get settings by category
     */
    public function getByCategory($category)
    {
        return $this->where('category', $category)->findAll();
    }
}
