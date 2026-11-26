<?php

namespace app\model;

use think\Model;

/**
 * 学生资助模型
 */
class Aid extends Model
{
    protected $name = 'ms_aid';

    // 时间字段以 Unix 时间戳形式存储
    protected $autoWriteTimestamp = 'int';

    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 获取器：资助类型文字
     */
    public function getTypeTextAttr($value, $data)
    {
        $arr = [1 => '助学金', 2 => '奖学金', 3 => '困难补助', 4 => '勤工助学'];
        return $arr[$data['type'] ?? 0] ?? '未知';
    }

    /**
     * 获取器：状态文字
     */
    public function getStatusTextAttr($value, $data)
    {
        $arr = [0 => '申请中', 1 => '已通过', 2 => '未通过'];
        return $arr[$data['status'] ?? 0] ?? '未知';
    }

    /**
     * 关联学生
     */
    public function studentInfo()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}
