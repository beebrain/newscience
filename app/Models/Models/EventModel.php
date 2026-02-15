<?php

namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'title',
        'slug',
        'excerpt',
        'content',
        'event_date',
        'event_time',
        'event_end_date',
        'event_end_time',
        'location',
        'featured_image',
        'status',
        'sort_order',
        'author_id'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'title'     => 'required|min_length[3]|max_length[500]',
        'slug'      => 'required|is_unique[events.slug,id,{id}]',
        'event_date' => 'required|valid_date',
    ];

    /**
     * Get upcoming published events (event_date >= today), ordered by event_date
     */
    public function getUpcoming(int $limit = 10, int $offset = 0): array
    {
        return $this->where('status', 'published')
            ->where('event_date >=', date('Y-m-d'))
            ->orderBy('event_date', 'ASC')
            ->orderBy('event_time', 'ASC')
            ->findAll($limit, $offset);
    }

    /**
     * Get all events for admin list (all statuses), ordered by event_date desc
     */
    public function getAllOrdered(): array
    {
        return $this->orderBy('event_date', 'DESC')
            ->orderBy('event_time', 'ASC')
            ->findAll();
    }

    /**
     * Get events for a given month (for calendar view)
     * Returns events where event_date falls in the given month
     */
    public function getByMonth(int $year, int $month): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $lastDay = (int) date('t', strtotime($start));
        $end = sprintf('%04d-%02d-%02d', $year, $month, $lastDay);

        return $this->where('status', 'published')
            ->where('event_date >=', $start)
            ->where('event_date <=', $end)
            ->orderBy('event_date', 'ASC')
            ->orderBy('event_time', 'ASC')
            ->findAll();
    }

    /**
     * Get events for admin calendar (all statuses) in a month
     */
    public function getByMonthAdmin(int $year, int $month): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $lastDay = (int) date('t', strtotime($start));
        $end = sprintf('%04d-%02d-%02d', $year, $month, $lastDay);

        return $this->where('event_date >=', $start)
            ->where('event_date <=', $end)
            ->orderBy('event_date', 'ASC')
            ->orderBy('event_time', 'ASC')
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
     * Find by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $row = $this->where('slug', $slug)->first();
        return $row ?: null;
    }
}
