<?php

namespace app\model;

use think\Model;

/**
 * 选课模型
 */
class SelectCourse extends Model
{
    protected $name = 'ms_select_course';

    // 时间字段以 Unix 时间戳形式存储
    protected $autoWriteTimestamp = 'int';

    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 关联学生
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    /**
     * 关联课程
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    /**
     * 依据总评成绩给出等级
     */
    public function level()
    {
        if ($this->score === null) {
            return '未录入';
        }
        $score = (float) $this->score;
        if ($score >= 90) return '优秀';
        if ($score >= 80) return '良好';
        if ($score >= 70) return '中等';
        if ($score >= 60) return '及格';
        return '不及格';
    }
}
