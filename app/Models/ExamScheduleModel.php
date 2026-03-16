<?php

namespace App\Models;

use CodeIgniter\Model;

class ExamScheduleModel extends Model
{
    protected $table            = 'exam_schedules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'batch_id',
        'section_text',
        'course_code',
        'course_name',
        'student_group',
        'student_program',
        'instructor_text',
        'exam_date',
        'exam_time_text',
        'room',
        'examiner1_text',
        'examiner2_text',
        'semester_label',
        'academic_year',
        'semester_no',
        'exam_type',
        'is_published',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get schedules by batch
     */
    public function getByBatch(int $batchId): array
    {
        return $this->where('batch_id', $batchId)
            ->orderBy('exam_date', 'ASC')
            ->orderBy('exam_time_text', 'ASC')
            ->findAll();
    }

    /**
     * Get published schedules for semester/type
     */
    public function getPublished(string $semesterLabel, string $examType): array
    {
        return $this->where('semester_label', $semesterLabel)
            ->where('exam_type', $examType)
            ->where('is_published', 1)
            ->orderBy('exam_date', 'ASC')
            ->orderBy('exam_time_text', 'ASC')
            ->findAll();
    }

    /**
     * Get schedules for a specific user (via links)
     */
    public function getByUser(int $userUid, string $semesterLabel, string $examType): array
    {
        return $this->select('exam_schedules.*, exam_schedule_user_links.link_role')
            ->join('exam_schedule_user_links', 'exam_schedule_user_links.schedule_id = exam_schedules.id')
            ->where('exam_schedule_user_links.user_uid', $userUid)
            ->where('exam_schedules.semester_label', $semesterLabel)
            ->where('exam_schedules.exam_type', $examType)
            ->where('exam_schedules.is_published', 1)
            ->orderBy('exam_schedules.exam_date', 'ASC')
            ->orderBy('exam_schedules.exam_time_text', 'ASC')
            ->findAll();
    }

    /**
     * Get schedules with unmatched examiners for admin review
     */
    public function getUnmatched(int $batchId): array
    {
        $subquery = $this->db->table('exam_schedule_user_links')
            ->select('schedule_id')
            ->where('link_role IN ("examiner1", "examiner2")')
            ->getCompiledSelect();

        return $this->where('batch_id', $batchId)
            ->where("(examiner1_text != '' AND examiner1_text IS NOT NULL AND id NOT IN ({$subquery})) OR (examiner2_text != '' AND examiner2_text IS NOT NULL AND id NOT IN ({$subquery}))", null, false)
            ->findAll();
    }

    /**
     * Batch update schedules to published
     */
    public function publishBatch(int $batchId): bool
    {
        return $this->where('batch_id', $batchId)
            ->set(['is_published' => 1])
            ->update();
    }
}
