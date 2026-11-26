<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 课程模型
 */
class Course extends Model
{
    use SoftDelete;

    protected $name = 'ms_course';

    // 时间字段以 Unix 时间戳形式存储
    protected $autoWriteTimestamp = 'int';

    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 课程类型
     */
    const TYPE_REQUIRED = 1;
    const TYPE_OPTIONAL = 2;
    const TYPE_PUBLIC   = 3;

    /**
     * 获取器：课程类型文字
     */
    public function getCourseTypeTextAttr($value, $data)
    {
        $arr = [
            self::TYPE_REQUIRED => '必修',
            self::TYPE_OPTIONAL => '选修',
            self::TYPE_PUBLIC   => '公共课',
        ];
        return $arr[$data['course_type'] ?? 0] ?? '未知';
    }

    /**
     * 关联授课教师
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    /**
     * 关联院系
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}
