<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramContentBlockModel extends Model
{
    protected $table = 'program_content_blocks';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'program_id',
        'block_key',
        'block_type',
        'title',
        'content',
        'custom_css',
        'custom_js',
        'sort_order',
        'is_active',
        'is_published',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all blocks for a program
     */
    public function getByProgramId(int $programId): array
    {
        return $this->where('program_id', $programId)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Get published blocks for a program
     */
    public function getPublishedByProgramId(int $programId): array
    {
        return $this->where('program_id', $programId)
            ->where('is_published', 1)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Get active blocks for a program (for admin preview)
     */
    public function getActiveByProgramId(int $programId): array
    {
        return $this->where('program_id', $programId)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Get a specific block by key
     */
    public function getByBlockKey(int $programId, string $blockKey): ?array
    {
        return $this->where('program_id', $programId)
            ->where('block_key', $blockKey)
            ->first();
    }

    /**
     * Check if block key exists for program
     */
    public function blockKeyExists(int $programId, string $blockKey): bool
    {
        return $this->where('program_id', $programId)
            ->where('block_key', $blockKey)
            ->countAllResults() > 0;
    }

    /**
     * Generate a unique block key
     */
    public function generateBlockKey(int $programId, string $title): string
    {
        $key = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $key = trim($key, '-');
        $baseKey = $key;
        $counter = 1;

        while ($this->blockKeyExists($programId, $key)) {
            $key = $baseKey . '-' . $counter;
            $counter++;
        }

        return $key;
    }

    /**
     * Update sort order for blocks
     */
    public function updateSortOrder(int $programId, array $blockIds): bool
    {
        foreach ($blockIds as $index => $blockId) {
            $this->update($blockId, ['sort_order' => $index + 1]);
        }
        return true;
    }

    /**
     * Toggle block active status
     */
    public function toggleActive(int $blockId): bool
    {
        $block = $this->find($blockId);
        if (!$block) {
            return false;
        }
        return $this->update($blockId, ['is_active' => !$block['is_active']]);
    }

    /**
     * Toggle block published status
     */
    public function togglePublished(int $blockId): bool
    {
        $block = $this->find($blockId);
        if (!$block) {
            return false;
        }
        return $this->update($blockId, ['is_published' => !$block['is_published']]);
    }

    /**
     * Get CSS blocks combined
     */
    public function getCombinedCss(int $programId): string
    {
        $blocks = $this->where('program_id', $programId)
            ->where('block_type', 'css')
            ->where('is_published', 1)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        $css = '';
        foreach ($blocks as $block) {
            $css .= "/* Block: {$block['title']} */\n";
            $css .= $block['content'] . "\n\n";
        }
        return $css;
    }

    /**
     * Get JS blocks combined
     */
    public function getCombinedJs(int $programId): string
    {
        $blocks = $this->where('program_id', $programId)
            ->where('block_type', 'js')
            ->where('is_published', 1)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        $js = '';
        foreach ($blocks as $block) {
            $js .= "// Block: {$block['title']}\n";
            $js .= $block['content'] . "\n\n";
        }
        return $js;
    }

    /**
     * Get content blocks (html/wysiwyg/markdown only)
     */
    public function getContentBlocks(int $programId): array
    {
        return $this->where('program_id', $programId)
            ->whereIn('block_type', ['html', 'wysiwyg', 'markdown'])
            ->where('is_published', 1)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Count blocks for a program
     */
    public function countByProgramId(int $programId): int
    {
        return $this->where('program_id', $programId)->countAllResults();
    }

    /**
     * Delete all blocks for a program
     */
    public function deleteByProgramId(int $programId): bool
    {
        return $this->where('program_id', $programId)->delete();
    }

    /**
     * Duplicate a block
     */
    public function duplicateBlock(int $blockId): ?int
    {
        $block = $this->find($blockId);
        if (!$block) {
            return null;
        }

        $newBlockKey = $this->generateBlockKey($block['program_id'], $block['title'] . ' Copy');
        $newData = [
            'program_id' => $block['program_id'],
            'block_key' => $newBlockKey,
            'block_type' => $block['block_type'],
            'title' => $block['title'] . ' (Copy)',
            'content' => $block['content'],
            'custom_css' => $block['custom_css'],
            'custom_js' => $block['custom_js'],
            'sort_order' => (int) $block['sort_order'] + 1,
            'is_active' => 0,
            'is_published' => 0,
        ];

        return $this->insert($newData);
    }
}
