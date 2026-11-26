<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 学生模型
 */
class Student extends Model
{
    use SoftDelete;

    protected $name = 'ms_student';

    // 时间字段以 Unix 时间戳形式存储
    protected $autoWriteTimestamp = 'int';

    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    // 性别
    const SEX_UNKNOWN = 0;
    const SEX_MALE    = 1;
    const SEX_FEMALE  = 2;

    // 学籍状态
    const STATUS_STUDYING  = 1;
    const STATUS_SUSPENDED = 0;
    const STATUS_GRADUATED = 2;
    const STATUS_WITHDRAWN = 3;

    /**
     * 获取器：性别文字
     */
    public function getGenderTextAttr($value, $data)
    {
        $arr = [
            self::SEX_UNKNOWN => '未知',
            self::SEX_MALE    => '男',
            self::SEX_FEMALE  => '女',
        ];
        return $arr[$data['gender'] ?? 0] ?? '未知';
    }

    /**
     * 获取器：学籍状态文字
     */
    public function getStatusTextAttr($value, $data)
    {
        $arr = [
            self::STATUS_STUDYING  => '在读',
            self::STATUS_SUSPENDED => '休学',
            self::STATUS_GRADUATED => '毕业',
            self::STATUS_WITHDRAWN => '退学',
        ];
        return $arr[$data['status'] ?? 0] ?? '未知';
    }

    /**
     * 关联班级
     */
    public function classInfo()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'id');
    }

    /**
     * 关联选课记录
     */
    public function selectCourses()
    {
        return $this->hasMany(SelectCourse::class, 'student_id', 'id');
    }
}
