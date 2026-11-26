<?php

namespace app\model;

use think\Model;

/**
 * 院系模型
 */
class Department extends Model
{
    protected $name = 'ms_department';

    // 时间字段以 Unix 时间戳形式存储
    protected $autoWriteTimestamp = 'int';

    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 关联班级
     */
    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'department_id', 'id');
    }
}
