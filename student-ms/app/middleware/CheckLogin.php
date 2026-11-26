<?php

namespace app\middleware;

use app\service\AuthService;
use think\Request;

/**
 * 登录校验中间件
 */
class CheckLogin
{
    /**
     * 无需登录校验的路径
     */
    protected $allowPaths = [
        'login',
        'login/do',
        'logout',
    ];

    /**
     * 处理请求
     */
    public function handle(Request $request, \Closure $next)
    {
        $path = trim($request->pathinfo(), '/');
        if (in_array($path, $this->allowPaths, true) || $path === '') {
            return $next($request);
        }

        if (AuthService::id() < 1) {
            // 接口类请求返回 JSON 提示，页面请求跳转登录页
            if ($this->isAjaxRequest($request)) {
                return json(['code' => 0, 'msg' => '登录状态已失效，请重新登录', 'url' => '/login']);
            }
            return redirect('/login');
        }

        return $next($request);
    }

    /**
     * 判断是否为接口类请求
     */
    protected function isAjaxRequest(Request $request): bool
    {
        if ($request->isJson() || $request->isAjax()) {
            return true;
        }
        // 未登录时无法从会话判断，POST 请求统一按接口处理
        return $request->method() === 'POST';
    }
}
