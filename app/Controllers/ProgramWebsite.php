<?php

namespace App\Controllers;

use App\Models\ProgramModel;
use App\Models\ProgramContentBlockModel;
use App\Models\ProgramPageModel;

class ProgramWebsite extends BaseController
{
    protected $programModel;
    protected $contentBlockModel;
    protected $programPageModel;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
        $this->contentBlockModel = new ProgramContentBlockModel();
        $this->programPageModel = new ProgramPageModel();
    }

    /**
     * Display program website with custom content blocks
     */
    public function index($programId)
    {
        return $this->renderProgramSite($programId);
    }

    /**
     * Render the program website
     */
    protected function renderProgramSite(int $programId, bool $preview = false)
    {
        $program = $this->programModel->find($programId);
        
        if (!$program || $program['status'] !== 'active') {
            return $this->response->setStatusCode(404)->setBody('Program not found');
        }

        // Get published content blocks
        $blocks = $preview 
            ? $this->contentBlockModel->getActiveByProgramId($programId)
            : $this->contentBlockModel->getPublishedByProgramId($programId);

        // Get CSS and JS
        $customCss = $this->contentBlockModel->getCombinedCss($programId);
        $customJs = $this->contentBlockModel->getCombinedJs($programId);

        // Get program page data if exists
        $programPage = $this->programPageModel->findByProgramId($programId);

        // Separate blocks by type
        $contentBlocks = array_filter($blocks, fn($b) => in_array($b['block_type'], ['html', 'wysiwyg', 'markdown']));
        
        // Process content
        $processedBlocks = [];
        foreach ($contentBlocks as $block) {
            $content = $block['content'];
            
            // Process markdown
            if ($block['block_type'] === 'markdown') {
                $content = $this->parseMarkdown($content);
            }
            
            // Wrap with custom CSS if exists
            if (!empty($block['custom_css'])) {
                $content = '<style>' . $block['custom_css'] . '</style>' . $content;
            }
            
            // Add custom JS
            if (!empty($block['custom_js'])) {
                $content .= '<script>' . $block['custom_js'] . '</script>';
            }
            
            $processedBlocks[] = [
                'id' => $block['id'],
                'title' => $block['title'],
                'key' => $block['block_key'],
                'content' => $content,
            ];
        }

        $data = [
            'page_title' => $program['name_th'] ?? $program['name_en'],
            'program' => $program,
            'program_page' => $programPage,
            'blocks' => $processedBlocks,
            'custom_css' => $customCss,
            'custom_js' => $customJs,
            'preview' => $preview,
        ];

        return view('program_website', $data);
    }

    /**
     * Simple markdown parser (or use Parsedown if available)
     */
    protected function parseMarkdown(string $text): string
    {
        // Basic markdown parsing
        // Headers
        $text = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $text);
        $text = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $text);
        $text = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $text);
        
        // Bold and italic
        $text = preg_replace('/\*\*\*(.*?)\*\*\*/', '<strong><em>$1</em></strong>', $text);
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
        
        // Links
        $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text);
        
        // Lists
        $text = preg_replace('/^\* (.*$)/m', '<li>$1</li>', $text);
        $text = preg_replace('/(<li>.*<\/li>\n?)+/', '<ul>$0</ul>', $text);
        $text = str_replace('</ul><ul>', '', $text);
        
        // Line breaks
        $text = nl2br($text);
        
        return $text;
    }
}
