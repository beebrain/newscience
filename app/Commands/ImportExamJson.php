<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ExamImportBatchModel;
use App\Models\ExamScheduleModel;
use App\Models\ExamScheduleUserLinkModel;
use App\Models\ExamPublishVersionModel;
use App\Models\UserModel;

/**
 * Import existing JSON exam schedules from EdocSci into newScience database
 * 
 * Usage: php spark exam:import-json <json_file_path> [options]
 * 
 * Options:
 *   --auto-match    Auto-match examiners by nickname
 *   --publish       Publish immediately after import
 * 
 * Examples:
 *   php spark exam:import-json writable/exampuploads/schedules_1_2568_midterm.json
 *   php spark exam:import-json schedules_2_2568_final.json --auto-match --publish
 */
class ImportExamJson extends BaseCommand
{
    protected $group       = 'Exam';
    protected $name        = 'exam:import-json';
    protected $description = 'Import existing JSON exam schedules into database';
    protected $usage       = 'exam:import-json <json_file_path> [options]';
    protected $arguments   = [
        'json_file_path' => 'Path to JSON file (absolute or relative to CI root)',
    ];
    protected $options     = [
        '--auto-match' => 'Auto-match examiners by nickname',
        '--publish'    => 'Publish immediately after import',
    ];

    protected $batchModel;
    protected $scheduleModel;
    protected $linkModel;
    protected $versionModel;
    protected $userModel;

    public function run(array $params)
    {
        // Initialize models
        $this->batchModel    = new ExamImportBatchModel();
        $this->scheduleModel = new ExamScheduleModel();
        $this->linkModel     = new ExamScheduleUserLinkModel();
        $this->versionModel  = new ExamPublishVersionModel();
        $this->userModel     = new UserModel();

        $jsonPath = $params[0] ?? null;

        if (!$jsonPath) {
            CLI::error('Error: Please provide JSON file path');
            $this->showHelp();
            return;
        }

        // Resolve path
        $fullPath = $this->resolvePath($jsonPath);

        if (!file_exists($fullPath)) {
            CLI::error("Error: File not found: {$jsonPath}");
            return;
        }

        CLI::write("Importing: {$jsonPath}", 'yellow');

        // Parse JSON
        $jsonContent = file_get_contents($fullPath);
        $data = json_decode($jsonContent, true);

        if (!$data || json_last_error() !== JSON_ERROR_NONE) {
            CLI::error('Error: Invalid JSON file - ' . json_last_error_msg());
            return;
        }

        // Extract metadata
        $metadata = $data['metadata'] ?? [];
        $schedules = $data['schedules'] ?? [];

        if (empty($schedules)) {
            CLI::error('Error: No schedules found in JSON');
            return;
        }

        $semester = $metadata['semester'] ?? $this->extractSemesterFromFilename(basename($jsonPath));
        $examType = $metadata['exam_type'] ?? $this->extractExamTypeFromFilename(basename($jsonPath));

        if (!$semester || !$examType) {
            CLI::error('Error: Cannot determine semester/exam type from file');
            return;
        }

        CLI::write("Semester: {$semester}, Exam Type: {$examType}", 'cyan');
        CLI::write("Found " . count($schedules) . " schedules to import", 'cyan');

        // Confirm
        if (!CLI::prompt('Continue with import?', ['y', 'n']) === 'y') {
            CLI::write('Import cancelled', 'yellow');
            return;
        }

        // Create batch
        $parts = explode('/', $semester);
        $batchData = [
            'semester_label' => $semester,
            'academic_year'  => (int)($parts[1] ?? 0),
            'semester_no'    => (int)($parts[0] ?? 0),
            'exam_type'      => $examType,
            'source_filename' => $metadata['filename'] ?? basename($jsonPath),
            'source_hash'    => md5_file($fullPath),
            'source_snapshot_path' => $jsonPath,
            'status'         => 'draft',
            'imported_by'    => 1, // System/admin user
        ];

        $batchId = $this->batchModel->insert($batchData);

        if (!$batchId) {
            CLI::error('Error: Failed to create import batch');
            return;
        }

        CLI::write("Created batch ID: {$batchId}", 'green');

        // Import schedules
        $imported = 0;
        $matched = 0;
        $autoMatch = CLI::getOption('auto-match');
        $shouldPublish = CLI::getOption('publish');
        $total = count($schedules);

        CLI::write("Importing {$total} schedules...", 'cyan');

        foreach ($schedules as $i => $schedule) {
            $scheduleData = [
                'batch_id'        => $batchId,
                'section_text'    => $schedule['section'] ?? '',
                'course_code'     => $schedule['course_code'] ?? '',
                'course_name'     => $schedule['course_name'] ?? '',
                'student_group'   => $schedule['student_group'] ?? '',
                'student_program' => $schedule['student_program'] ?? '',
                'instructor_text' => $schedule['instructor'] ?? '',
                'exam_date'       => $this->parseDate($schedule['exam_date'] ?? ''),
                'exam_time_text'  => $schedule['exam_time'] ?? '',
                'room'            => $schedule['room'] ?? '',
                'examiner1_text'  => $schedule['examiner1'] ?? '',
                'examiner2_text'  => $schedule['examiner2'] ?? '',
                'semester_label'  => $semester,
                'academic_year'   => (int)($parts[1] ?? 0),
                'semester_no'     => (int)($parts[0] ?? 0),
                'exam_type'       => $examType,
            ];

            $scheduleId = $this->scheduleModel->insert($scheduleData);

            if ($scheduleId) {
                $imported++;

                // Auto-match if enabled
                if ($autoMatch) {
                    if (!empty($schedule['examiner1'])) {
                        $userId = $this->linkModel->autoMatchByNickname(
                            $scheduleId,
                            $schedule['examiner1'],
                            'examiner1',
                            $this->userModel
                        );
                        if ($userId) $matched++;
                    }

                    if (!empty($schedule['examiner2'])) {
                        $userId = $this->linkModel->autoMatchByNickname(
                            $scheduleId,
                            $schedule['examiner2'],
                            'examiner2',
                            $this->userModel
                        );
                        if ($userId) $matched++;
                    }
                }
            }

            // Show progress every 10 items
            if (($i + 1) % 10 === 0 || $i === $total - 1) {
                CLI::write("  Processed: " . ($i + 1) . "/{$total}", 'cyan');
            }
        }

        CLI::write("");
        CLI::write("Import complete:", 'green');
        CLI::write("  - Total schedules: " . count($schedules), 'cyan');
        CLI::write("  - Imported: {$imported}", 'green');
        CLI::write("  - Auto-matched: {$matched}", 'green');

        // Publish if requested
        if ($shouldPublish) {
            CLI::write("Publishing batch...", 'yellow');

            $this->batchModel->update($batchId, [
                'status' => 'published',
                'published_at' => date('Y-m-d H:i:s'),
            ]);

            $this->scheduleModel->publishBatch($batchId);

            $this->versionModel->setActive(
                $batchId,
                $semester,
                $examType,
                1 // admin_id
            );

            CLI::write("Published successfully!", 'green');
            CLI::write("Users can now view schedules at: exam/", 'cyan');
        } else {
            CLI::write("");
            CLI::write("Batch is in DRAFT mode. To publish:", 'yellow');
            CLI::write("  php spark exam:publish {$batchId}", 'cyan');
            CLI::write("Or go to Admin > Exam > Preview and click Publish", 'cyan');
        }

        CLI::write("");
        CLI::write("Next steps:", 'green');
        CLI::write("  1. Go to /admin/exam/preview/{$batchId} to review", 'cyan');
        CLI::write("  2. Set user nicknames to match examiner names", 'cyan');
        CLI::write("  3. Manual match any unmatched schedules", 'cyan');
        CLI::write("  4. Click Publish when ready", 'cyan');
    }

    /**
     * Resolve relative path to absolute
     */
    private function resolvePath(string $path): string
    {
        if (strpos($path, '/') === 0 || preg_match('/^[A-Za-z]:\\\\/', $path)) {
            return $path; // Already absolute
        }

        // Try relative to CI root
        $ciPath = ROOTPATH . $path;
        if (file_exists($ciPath)) {
            return $ciPath;
        }

        // Try relative to writable
        $writablePath = WRITEPATH . $path;
        if (file_exists($writablePath)) {
            return $writablePath;
        }

        return $path; // Return as-is, will fail gracefully
    }

    /**
     * Extract semester from filename (e.g., schedules_1_2568_midterm.json)
     */
    private function extractSemesterFromFilename(string $filename): ?string
    {
        if (preg_match('/schedules_(\d+)_(\d+)_(?:midterm|final)\.json/', $filename, $matches)) {
            return $matches[1] . '/' . $matches[2];
        }
        return null;
    }

    /**
     * Extract exam type from filename
     */
    private function extractExamTypeFromFilename(string $filename): ?string
    {
        if (preg_match('/schedules_\d+_\d+_(midterm|final)\.json/', $filename, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate(?string $dateStr): ?string
    {
        if (empty($dateStr)) return null;

        $formats = ['d/m/Y', 'm/d/Y', 'Y-m-d', 'Y/m/d'];
        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $dateStr);
            if ($dt !== false) {
                return $dt->format('Y-m-d');
            }
        }

        return null;
    }
}
