<?php

namespace app\model;

use think\Model;

/**
 * 登录日志模型
 */
class LoginLog extends Model
{
    protected $name = 'ms_login_log';

    protected $autoWriteTimestamp = false;

    /**
     * 获取器：结果文字
     */
    public function getStatusTextAttr($value, $data)
    {
        return $data['status'] == 1 ? '成功' : '失败';
    }
}
