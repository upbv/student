<?php

namespace app\model;

use think\Model;

/**
 * 权限（菜单）模型
 */
class Auth extends Model
{
    protected $name = 'ms_auth';

    // 时间字段以 Unix 时间戳形式存储
    protected $autoWriteTimestamp = 'int';

    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 获取器：类型文字
     */
    public function getTypeTextAttr($value, $data)
    {
        return $data['type'] == 1 ? '菜单' : '按钮';
    }

    /**
     * 获取所有启用的一级菜单
     */
    public function topMenus()
    {
        return $this->where('pid', 0)->where('status', 1)->order('sort', 'asc')->select();
    }
}
