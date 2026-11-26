<?php

namespace app\service;

use app\model\OperationLog;
use think\Request;

/**
 * 日志服务：记录管理员的关键操作行为
 */
class LogService
{
    /**
     * 写入一条操作日志
     */
    public static function write(
        string $module,
        string $action,
        string $method = '',
        string $url = '',
        string $ip = '',
        array $params = [],
        int $status = 1,
        string $errorMsg = '',
        string $result = ''
    ): void {
        // 参数中可能包含密码等敏感字段，做脱敏处理
        $params = self::maskParams($params);

        OperationLog::create([
            'admin_id'     => AuthService::id(),
            'username'     => AuthService::username(),
            'module'       => $module,
            'action'       => $action,
            'method'       => $method,
            'url'          => mb_substr($url, 0, 190),
            'ip'           => $ip,
            'params'       => mb_substr(json_encode($params, JSON_UNESCAPED_UNICODE), 0, 4000),
            'result'       => mb_substr($result, 0, 1000),
            'status'       => $status,
            'error_msg'    => mb_substr($errorMsg, 0, 490),
            'operate_time' => time(),
        ]);
    }

    /**
     * 从请求对象采集日志所需信息
     */
    public static function writeFromRequest(
        Request $request,
        string $module,
        string $action,
        int $status = 1,
        string $errorMsg = '',
        string $result = ''
    ): void {
        self::write(
            $module,
            $action,
            $request->method(),
            $request->baseUrl() . $request->url(),
            $request->ip(),
            $request->param(),
            $status,
            $errorMsg,
            $result
        );
    }

    /**
     * 敏感参数脱敏
     */
    protected static function maskParams(array $params): array
    {
        $sensitive = ['password', 'password_confirm', 'old_password', 'new_password', 'token'];
        foreach ($params as $key => $value) {
            if (in_array(strtolower((string) $key), $sensitive, true)) {
                $params[$key] = '******';
            }
        }
        return $params;
    }
}
