<?php

namespace app\model;

use think\Model;

/**
 * 操作日志模型
 */
class OperationLog extends Model
{
    protected $name = 'ms_operation_log';

    protected $autoWriteTimestamp = false;

    /**
     * 获取器：状态文字
     */
    public function getStatusTextAttr($value, $data)
    {
        return $data['status'] == 1 ? '成功' : '失败';
    }
}
