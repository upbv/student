<?php

namespace app\model;

use think\Model;

/**
 * 角色模型
 */
class Role extends Model
{
    protected $name = 'ms_role';

    // 时间字段以 Unix 时间戳形式存储
    protected $autoWriteTimestamp = 'int';

    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 关联账号
     */
    public function admins()
    {
        return $this->hasMany(AdminRole::class, 'role_id', 'id');
    }

    /**
     * 关联权限
     */
    public function auths()
    {
        return $this->hasMany(RoleAuth::class, 'role_id', 'id');
    }

    /**
     * 获取权限ID数组
     */
    public function authIds()
    {
        return RoleAuth::where('role_id', $this->id)->column('auth_id');
    }
}
