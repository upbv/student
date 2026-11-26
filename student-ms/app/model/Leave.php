<?php

namespace app\model;

use think\Model;

/**
 * 学生请假模型
 */
class Leave extends Model
{
    protected $name = 'ms_leave';

    // 时间字段以 Unix 时间戳形式存储
    protected $autoWriteTimestamp = 'int';

    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 获取器：请假类型文字
     */
    public function getTypeTextAttr($value, $data)
    {
        $arr = [1 => '事假', 2 => '病假', 3 => '其他'];
        return $arr[$data['type'] ?? 0] ?? '未知';
    }

    /**
     * 获取器：审批状态文字
     */
    public function getStatusTextAttr($value, $data)
    {
        $arr = [0 => '待审批', 1 => '已批准', 2 => '已驳回'];
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
