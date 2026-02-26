<?php

namespace App\Controllers\Edoc;

/**
 * Diagnostic Controller for checking EdocDocument files
 * URL: /edoc/diagnostic/checkfile/{filename}
 * URL: /edoc/diagnostic/listfiles
 */
class DiagnosticController extends EdocBaseController
{
    /**
     * Check if a specific file exists
     * GET /edoc/diagnostic/checkfile/{filename}
     */
    public function checkFile($filename = null)
    {
        $results = [];
        
        $results['ROOTPATH'] = ROOTPATH;
        $results['APPPATH'] = APPPATH;
        $results['FCPATH'] = FCPATH;
        $results['WRITEPATH'] = WRITEPATH;
        
        $pathsToCheck = [
            'WRITEPATH + edoc_documents' => WRITEPATH . 'edoc_documents/',
            'ROOTPATH + EdocDocument' => ROOTPATH . 'EdocDocument/',
            'FCPATH + EdocDocument' => FCPATH . 'EdocDocument/',
        ];
        
        $results['folder_checks'] = [];
        foreach ($pathsToCheck as $label => $path) {
            $realPath = realpath($path);
            $results['folder_checks'][$label] = [
                'path' => $path,
                'realpath' => $realPath ?: 'NOT FOUND',
                'exists' => is_dir($path) ? 'YES' : 'NO',
                'is_readable' => is_readable($path) ? 'YES' : 'NO',
            ];
            
            if (is_dir($path)) {
                $files = array_slice(scandir($path), 0, 12);
                $results['folder_checks'][$label]['sample_files'] = array_values(array_diff($files, ['.', '..']));
            }
        }
        
        if ($filename) {
            $results['target_filename'] = $filename;
            $results['file_checks'] = [];
            
            foreach ($pathsToCheck as $label => $basePath) {
                $fullPath = $basePath . $filename;
                $results['file_checks'][$label] = [
                    'full_path' => $fullPath,
                    'exists' => file_exists($fullPath) ? 'YES' : 'NO',
                    'is_readable' => is_readable($fullPath) ? 'YES' : 'NO',
                    'size' => file_exists($fullPath) ? filesize($fullPath) . ' bytes' : 'N/A',
                ];
            }
        }
        
        log_message('info', '========== DIAGNOSTIC CHECK ==========');
        log_message('info', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        log_message('info', '========== END DIAGNOSTIC ==========');
        
        return $this->response
            ->setContentType('application/json')
            ->setBody(json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    
    /**
     * List all files in edoc_documents folder
     * GET /edoc/diagnostic/listfiles
     */
    public function listFiles()
    {
        $results = [];
        
        $edocPath = $this->getEdocDocumentPath();
        $results['path_checked'] = $edocPath;
        $results['realpath'] = realpath($edocPath) ?: 'NOT FOUND';
        $results['exists'] = is_dir($edocPath) ? 'YES' : 'NO';
        
        if (is_dir($edocPath)) {
            $files = scandir($edocPath);
            $files = array_values(array_diff($files, ['.', '..']));
            $results['total_files'] = count($files);
            $results['files'] = $files;
        } else {
            $results['error'] = 'edoc_documents folder not found at: ' . $edocPath;
        }
        
        log_message('info', '========== LIST FILES ==========');
        log_message('info', 'Path: ' . $edocPath);
        log_message('info', 'Exists: ' . ($results['exists'] ?? 'Unknown'));
        log_message('info', 'Total files: ' . ($results['total_files'] ?? 0));
        log_message('info', '========== END LIST ==========');
        
        return $this->response
            ->setContentType('application/json')
            ->setBody(json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
