<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 教师模型
 */
class Teacher extends Model
{
    use SoftDelete;

    protected $name = 'ms_teacher';

    // 时间字段以 Unix 时间戳形式存储
    protected $autoWriteTimestamp = 'int';

    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 获取器：性别文字
     */
    public function getGenderTextAttr($value, $data)
    {
        $arr = [0 => '未知', 1 => '男', 2 => '女'];
        return $arr[$data['gender'] ?? 0] ?? '未知';
    }

    /**
     * 关联院系
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}
