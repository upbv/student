<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 系统账号模型
 */
class Admin extends Model
{
    use SoftDelete;

    // 密码与盐值不出现在接口返回中
    protected $hidden = ['password', 'salt'];

    protected $name = 'ms_admin';

    // 时间字段以 Unix 时间戳形式存储
    protected $autoWriteTimestamp = 'int';

    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];

    // 时间字段转换
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 获取器：状态文字
     */
    public function getStatusTextAttr($value, $data)
    {
        return $data['status'] == 1 ? '正常' : '停用';
    }

    /**
     * 获取器：性别文字
     */
    public function getSexTextAttr($value, $data)
    {
        $arr = [0 => '未知', 1 => '男', 2 => '女'];
        return $arr[$data['sex'] ?? 0] ?? '未知';
    }

    /**
     * 获取关联的角色名称
     */
    public function roleNames(): array
    {
        $roleIds = AdminRole::where('admin_id', $this->id)->column('role_id');
        if (empty($roleIds)) {
            return [];
        }
        return Role::whereIn('id', $roleIds)->column('name');
    }
}
