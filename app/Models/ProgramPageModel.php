<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramPageModel extends Model
{
    protected $table = 'program_pages';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'program_id',
        'slug',
        'philosophy',
        'objectives',
        'graduate_profile',
        'curriculum_structure',
        'study_plan',
        'career_prospects',
        'tuition_fees',
        'admission_info',
        'contact_info',
        'intro_video_url',
        'gallery_images',
        'social_links',
        'hero_image',
        'theme_color',
        'meta_description',
        'is_published'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get program page by slug
     */
    public function findBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get program page by program ID
     */
    public function findByProgramId(int $programId)
    {
        return $this->where('program_id', $programId)->first();
    }

    /**
     * Get published program pages
     */
    public function getPublished()
    {
        return $this->where('is_published', 1)
            ->orderBy('updated_at', 'DESC')
            ->findAll();
    }

    /**
     * Get program page with program info
     */
    public function getWithProgram(int $programId)
    {
        return $this->select('program_pages.*, programs.name_th, programs.name_en, programs.level, programs.degree_th, programs.degree_en')
            ->join('programs', 'programs.id = program_pages.program_id')
            ->where('program_pages.program_id', $programId)
            ->first();
    }

    /**
     * Get all program pages with program info
     */
    public function getAllWithProgram()
    {
        return $this->select('program_pages.*, programs.name_th, programs.name_en, programs.level, programs.degree_th, programs.degree_en, programs.status')
            ->join('programs', 'programs.id = program_pages.program_id')
            ->orderBy('programs.sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Get published program pages with program info
     */
    public function getPublishedWithProgram()
    {
        return $this->select('program_pages.*, programs.name_th, programs.name_en, programs.level, programs.degree_th, programs.degree_en, programs.status')
            ->join('programs', 'programs.id = program_pages.program_id')
            ->where('program_pages.is_published', 1)
            ->where('programs.status', 'active')
            ->orderBy('programs.sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Check if program page exists for a program
     */
    public function existsForProgram(int $programId): bool
    {
        return $this->where('program_id', $programId)->countAllResults() > 0;
    }

    /**
     * Publish/unpublish program page
     */
    public function publish(int $programId): bool
    {
        return $this->where('program_id', $programId)
            ->set(['is_published' => 1, 'updated_at' => date('Y-m-d H:i:s')])
            ->update();
    }

    /**
     * Unpublish program page
     */
    public function unpublish(int $programId): bool
    {
        return $this->where('program_id', $programId)
            ->set(['is_published' => 0, 'updated_at' => date('Y-m-d H:i:s')])
            ->update();
    }

    /**
     * Create or update program page with proper slug
     */
    public function updateOrCreate(array $condition, array $data): bool
    {
        // Ensure slug is URL-safe
        if (isset($data['slug'])) {
            $data['slug'] = 'program-' . $condition['program_id'];
        }

        $existing = $this->where($condition)->first();
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert(array_merge($condition, $data)) !== false;
        }
    }

    /**
     * Get theme color for program page
     */
    public function getThemeColor(int $programId): string
    {
        $page = $this->findByProgramId($programId);
        return $page['theme_color'] ?? '#1e40af';
    }

    /**
     * Update theme color
     */
    public function updateThemeColor(int $programId, string $color): bool
    {
        return $this->where('program_id', $programId)
            ->set(['theme_color' => $color, 'updated_at' => date('Y-m-d H:i:s')])
            ->update();
    }
}
