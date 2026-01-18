<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    protected $table = 'news';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'featured_image',
        'author_id',
        'view_count',
        'published_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'title' => 'required|min_length[3]|max_length[500]',
        'slug' => 'required|is_unique[news.slug,id,{id}]',
    ];

    /**
     * Get published news
     */
    public function getPublished(int $limit = 10, int $offset = 0)
    {
        return $this->where('status', 'published')
                    ->orderBy('published_at', 'DESC')
                    ->findAll($limit, $offset);
    }

    /**
     * Get news by slug
     */
    public function findBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get news with author info
     */
    public function getNewsWithAuthor(int $id)
    {
        return $this->select('news.*, user.gf_name, user.gl_name, user.title as author_title')
                    ->join('user', 'user.uid = news.author_id', 'left')
                    ->find($id);
    }

    /**
     * Get all news with author
     */
    public function getAllWithAuthor()
    {
        return $this->select('news.*, user.gf_name, user.gl_name')
                    ->join('user', 'user.uid = news.author_id', 'left')
                    ->orderBy('news.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Generate slug from title
     */
    public function generateSlug(string $title, ?int $excludeId = null): string
    {
        $slug = url_title($title, '-', true);
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $builder = $this->builder()->where('slug', $slug);
            if ($excludeId) {
                $builder->where('id !=', $excludeId);
            }
            
            if ($builder->countAllResults() === 0) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Increment view count
     */
    public function incrementViews(int $id)
    {
        return $this->builder()
                    ->where('id', $id)
                    ->set('view_count', 'view_count + 1', false)
                    ->update();
    }
}
