<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * Exam Controller - JSON File Based (User View)
 * 
 * Reads exam schedules from JSON files
 */
class ExamJsonController extends BaseController
{
    private const UPLOAD_PATH = WRITEPATH . 'exampuploads/';

    /**
     * Display user's exam schedules
     */
    public function index()
    {
        // Get current user info for debugging
        $session = session();
        $userId = $session->get('user_id') ?? $session->get('admin_id');

        if ($userId) {
            $userModel = new UserModel();
            $user = $userModel->find($userId);

            $data = [
                'page_title' => 'ตารางคุมสอบ',
                'current_user' => [
                    'user_id' => $userId,
                    'nickname' => $user['nickname'] ?? '',
                    'thai_name' => trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? '')),
                    'eng_name' => trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? '')),
                ],
                'debug_user' => [
                    'user_id' => $userId,
                    'nickname' => $user['nickname'] ?? '(ไม่มี)',
                    'thai_name' => trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? '')) ?: '(ไม่มี)',
                    'eng_name' => trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? '')) ?: '(ไม่มี)',
                ]
            ];
        } else {
            $data = [
                'page_title' => 'ตารางคุมสอบ',
                'current_user' => [
                    'user_id' => null,
                    'nickname' => '',
                    'thai_name' => '',
                    'eng_name' => '',
                ],
                'debug_user' => ['error' => 'ไม่พบ User ID']
            ];
        }

        return view('exam_json/index', $data);
    }

    /**
     * AJAX: Get available semesters
     */
    public function getSemesters()
    {
        $files = [];

        if (is_dir(self::UPLOAD_PATH)) {
            $iterator = new \DirectoryIterator(self::UPLOAD_PATH);

            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isFile() && $fileinfo->getExtension() === 'json') {
                    $filename = $fileinfo->getFilename();

                    // Parse filename to get metadata (handle both _prefix and normal formats)
                    if (preg_match('/^_?schedules_(\d+)_(\d+)_(midterm|final)\.json$/', $filename, $matches)) {
                        $files[] = [
                            'name' => $filename,
                            'semester_no' => $matches[1],
                            'year' => $matches[2],
                            'exam_type' => $matches[3],
                            'semester_label' => $matches[1] . '/' . $matches[2],
                            'is_published' => strpos($filename, '_') !== 0, // Files without _ prefix are published
                            'size' => $fileinfo->getSize(),
                            'modified' => $fileinfo->getMTime(),
                        ];
                    }
                }
            }

            // Sort by modified time descending
            usort($files, function ($a, $b) {
                return $b['modified'] - $a['modified'];
            });

            // Group by semester
            $result = [];
            $semesterMap = [];

            foreach ($files as $file) {
                $label = $file['semester_label'];
                $type = $file['exam_type'];

                if (!isset($semesterMap[$label])) {
                    $semesterMap[$label] = [];
                }

                $semesterMap[$label][] = $type;
            }

            foreach ($semesterMap as $label => $types) {
                $result[] = [
                    'label' => $label,
                    'exam_types' => array_unique($types),
                ];
            }

            // Sort by year descending, then semester descending
            usort($result, function ($a, $b) {
                $aParts = explode('/', $a['label']);
                $bParts = explode('/', $b['label']);

                $aYear = (int)$aParts[1];
                $bYear = (int)$bParts[1];
                $aSem = (int)$aParts[0];
                $bSem = (int)$bParts[0];

                if ($aYear !== $bYear) return $bYear - $aYear;
                return $bSem - $aSem;
            });
        }

        return $this->response->setJSON(['success' => true, 'semesters' => $result]);
    }

    /**
     * AJAX: Get schedules for a semester/exam type
     */
    public function getSchedules()
    {
        $semester = $this->request->getGet('semester');
        $examType = $this->request->getGet('exam_type');

        if (!$semester || !$examType) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing parameters']);
        }

        $parts = explode('/', $semester);
        if (count($parts) !== 2) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid semester format']);
        }

        $semesterNo = $parts[0];
        $year = $parts[1];

        // Try published version first (without _ prefix)
        $publishedFile = "schedules_{$semesterNo}_{$year}_{$examType}.json";
        $filepath = self::UPLOAD_PATH . $publishedFile;

        // Fall back to draft version (with _ prefix)
        if (!file_exists($filepath)) {
            $draftFile = "_schedules_{$semesterNo}_{$year}_{$examType}.json";
            $filepath = self::UPLOAD_PATH . $draftFile;
        }

        if (!file_exists($filepath)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลตารางสอบ']);
        }

        $jsonData = json_decode(file_get_contents($filepath), true);

        if (!$jsonData) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่สามารถอ่านข้อมูลได้']);
        }

        $schedules = $jsonData['schedules'] ?? [];

        // Get current user info from database (not just session)
        $session = session();
        $userId = $session->get('user_id') ?? $session->get('admin_id');

        // Query user table directly for fresh data
        $userModel = new UserModel();
        $user = $userId ? ($userModel->find($userId) ?? []) : [];

        $userNickname = trim((string)($user['nickname'] ?? ''));
        $userThaiName = trim(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? ''));
        $userEngName = trim(($user['gf_name'] ?? '') . ' ' . ($user['gl_name'] ?? ''));
        $userIdentities = array_values(array_filter([$userNickname, $userThaiName]));

        $allSchedules = [];
        $mySchedules = [];
        $matchDetails = [];
        $instructorMap = [];

        foreach ($schedules as $schedule) {
            $ownerNames = $this->extractNames($schedule['instructor'] ?? '');
            $examiner1 = trim((string)($schedule['examiner1'] ?? ''));
            $examiner2 = trim((string)($schedule['examiner2'] ?? ''));

            $isOwner = $this->matchesAny($ownerNames, $userIdentities);
            $isExaminer1 = $this->matchesIdentity($examiner1, $userIdentities);
            $isExaminer2 = $this->matchesIdentity($examiner2, $userIdentities);

            $roles = [];
            if ($isOwner) {
                $roles[] = 'เจ้าของรายวิชา';
            }
            if ($isExaminer1) {
                $roles[] = 'ผู้คุมสอบ 1';
            }
            if ($isExaminer2) {
                $roles[] = 'ผู้คุมสอบ 2';
            }

            $row = [
                'section' => $schedule['section'] ?? '',
                'course_code' => $schedule['course_code'] ?? '',
                'course_name' => $schedule['course_name'] ?? '',
                'student_group' => $schedule['student_group'] ?? '',
                'instructor' => $schedule['instructor'] ?? '',
                'owner_names' => $ownerNames,
                'exam_date' => $schedule['exam_date'] ?? '',
                'exam_time' => $schedule['exam_time'] ?? '',
                'room' => $schedule['room'] ?? '',
                'examiner1' => $examiner1,
                'examiner2' => $examiner2,
                'is_owner' => $isOwner,
                'is_examiner1' => $isExaminer1,
                'is_examiner2' => $isExaminer2,
                'is_mine' => !empty($roles),
                'roles' => $roles,
            ];

            $allSchedules[] = $row;

            if (!empty($roles)) {
                $mySchedules[] = $row;
                $matchDetails[] = [
                    'course' => ($row['course_code'] ?: '-') . ' ' . ($row['course_name'] ?: '-'),
                    'roles' => $roles,
                    'matched_by' => implode(', ', $roles),
                ];
            }

            foreach ([$examiner1 => 'ผู้คุมสอบ 1', $examiner2 => 'ผู้คุมสอบ 2'] as $name => $role) {
                if ($name === '') {
                    continue;
                }

                if (!isset($instructorMap[$name])) {
                    $instructorMap[$name] = [
                        'name' => $name,
                        'exam_schedules' => [],
                        'owner_courses' => [],
                        'owner_course_keys' => [],
                    ];
                }

                $instructorMap[$name]['exam_schedules'][] = [
                    'section' => $row['section'],
                    'course_code' => $row['course_code'],
                    'course_name' => $row['course_name'],
                    'student_group' => $row['student_group'],
                    'exam_date' => $row['exam_date'],
                    'exam_time' => $row['exam_time'],
                    'room' => $row['room'],
                    'role' => $role,
                ];
            }

            foreach ($ownerNames as $ownerName) {
                if (!isset($instructorMap[$ownerName])) {
                    $instructorMap[$ownerName] = [
                        'name' => $ownerName,
                        'exam_schedules' => [],
                        'owner_courses' => [],
                        'owner_course_keys' => [],
                    ];
                }

                $courseKey = implode('|', [
                    $row['course_code'],
                    $row['section'],
                    $row['student_group'],
                ]);

                if (!isset($instructorMap[$ownerName]['owner_course_keys'][$courseKey])) {
                    $instructorMap[$ownerName]['owner_course_keys'][$courseKey] = true;
                    $instructorMap[$ownerName]['owner_courses'][] = [
                        'section' => $row['section'],
                        'course_code' => $row['course_code'],
                        'course_name' => $row['course_name'],
                        'student_group' => $row['student_group'],
                    ];
                }
            }
        }

        $instructors = array_values(array_map(function ($instructor) {
            usort($instructor['exam_schedules'], function ($a, $b) {
                return $this->buildSortKey($a['exam_date'] ?? '', $a['exam_time'] ?? '') <=> $this->buildSortKey($b['exam_date'] ?? '', $b['exam_time'] ?? '');
            });

            usort($instructor['owner_courses'], function ($a, $b) {
                return strcmp(($a['course_code'] ?? '') . ($a['section'] ?? ''), ($b['course_code'] ?? '') . ($b['section'] ?? ''));
            });

            unset($instructor['owner_course_keys']);
            $instructor['exam_count'] = count($instructor['exam_schedules']);
            $instructor['owner_count'] = count($instructor['owner_courses']);

            return $instructor;
        }, $instructorMap));

        usort($allSchedules, function ($a, $b) {
            return $this->buildSortKey($a['exam_date'] ?? '', $a['exam_time'] ?? '') <=> $this->buildSortKey($b['exam_date'] ?? '', $b['exam_time'] ?? '');
        });

        usort($mySchedules, function ($a, $b) {
            return $this->buildSortKey($a['exam_date'] ?? '', $a['exam_time'] ?? '') <=> $this->buildSortKey($b['exam_date'] ?? '', $b['exam_time'] ?? '');
        });

        usort($instructors, function ($a, $b) {
            return strcmp($this->normalizeName($a['name'] ?? ''), $this->normalizeName($b['name'] ?? ''));
        });

        $summary = [
            'total_schedules' => count($allSchedules),
            'my_schedules' => count($mySchedules),
            'instructor_count' => count($instructors),
        ];

        return $this->response->setJSON([
            'success' => true,
            'filters' => [
                'semester' => $semester,
                'exam_type' => $examType,
            ],
            'summary' => $summary,
            'current_user' => [
                'user_id' => $userId,
                'nickname' => $userNickname,
                'thai_name' => $userThaiName,
                'eng_name' => $userEngName,
            ],
            'schedules' => $allSchedules,
            'my_schedules' => $mySchedules,
            'schedules_all' => $allSchedules,
            'schedules_mine' => $mySchedules,
            'instructors' => $instructors,
            'stats' => [
                'total' => $summary['total_schedules'],
                'my_schedules' => $summary['my_schedules'],
            ],
            'debug' => [
                'user_id' => $userId,
                'nickname_from_table' => $userNickname,
                'thai_name_from_table' => $userThaiName,
                'eng_name_from_table' => $userEngName,
                'matched_schedules' => count($mySchedules),
                'match_details' => $matchDetails,
            ]
        ]);
    }

    private function extractNames(string $value): array
    {
        // Split by comma first (most common separator)
        $parts = explode(',', $value);
        $names = [];

        foreach ($parts as $part) {
            $name = trim($part);

            // Skip empty or placeholder values
            if ($name === '' || $name === '-' || $name === 'N/A') {
                continue;
            }

            // Additional cleanup for common formatting issues
            $name = preg_replace('/\s+/u', ' ', $name); // Normalize spaces
            $name = trim($name);

            if ($name !== '') {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
    }

    private function normalizeName(string $value): string
    {
        $value = trim($value);
        // Remove multiple spaces and normalize to single space
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        // Remove common special characters that might be separators
        $value = preg_replace('/[,\s]+/u', ' ', $value) ?? $value;
        return mb_strtolower(trim($value), 'UTF-8');
    }

    private function matchesIdentity(string $candidate, array $identities): bool
    {
        if ($candidate === '' || empty($identities)) {
            return false;
        }

        $normalizedCandidate = $this->normalizeName($candidate);

        foreach ($identities as $identity) {
            $normalizedIdentity = $this->normalizeName((string)$identity);

            // Check exact match
            if ($normalizedCandidate === $normalizedIdentity) {
                return true;
            }

            // Check if normalized candidate contains normalized identity or vice versa
            if (
                strpos($normalizedCandidate, $normalizedIdentity) !== false ||
                strpos($normalizedIdentity, $normalizedCandidate) !== false
            ) {
                return true;
            }
        }

        return false;
    }

    private function matchesAny(array $candidates, array $identities): bool
    {
        foreach ($candidates as $candidate) {
            if ($this->matchesIdentity((string)$candidate, $identities)) {
                return true;
            }
        }

        return false;
    }

    private function buildSortKey(string $date, string $time): int
    {
        $dateParts = explode('/', trim($date));
        $day = (int)($dateParts[0] ?? 0);
        $month = (int)($dateParts[1] ?? 0);
        $year = (int)($dateParts[2] ?? 0);

        if ($year > 2400) {
            $year -= 543;
        }

        $startTime = trim(explode('-', $time)[0] ?? '00.00');
        $timeParts = explode('.', str_replace(':', '.', $startTime));
        $hour = (int)($timeParts[0] ?? 0);
        $minute = (int)($timeParts[1] ?? 0);

        return (((($year * 100) + $month) * 100 + $day) * 10000) + ($hour * 100) + $minute;
    }
}
