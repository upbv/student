<?php

namespace app\middleware;

use think\Request;

/**
 * 输入清洗中间件
 *
 * 对 GET / POST 中的字符串参数去除 HTML 标签，防止存储型 XSS；
 * 密码类字段保持原样，避免改变用户输入的密码。
 */
class SanitizeInput
{
    /**
     * 不做处理的字段
     */
    protected $except = [
        'password',
        'old_password',
        'new_password',
        'new_password_confirm',
    ];

    /**
     * 处理请求
     */
    public function handle(Request $request, \Closure $next)
    {
        $request->withGet($this->clean($request->get()));
        $request->withPost($this->clean($request->post()));

        return $next($request);
    }

    /**
     * 递归清洗数组中的字符串
     */
    protected function clean(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array((string) $key, $this->except, true)) {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->clean($value);
            } elseif (is_string($value)) {
                $data[$key] = strip_tags($value);
            }
        }

        return $data;
    }
}
