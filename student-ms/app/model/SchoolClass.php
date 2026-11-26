<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 班级模型
 */
class SchoolClass extends Model
{
    use SoftDelete;

    protected $name = 'ms_class';

    // 时间字段以 Unix 时间戳形式存储
    protected $autoWriteTimestamp = 'int';

    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 关联院系
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    /**
     * 关联学生
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id', 'id');
    }
}
