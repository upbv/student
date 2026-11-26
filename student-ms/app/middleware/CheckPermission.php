<?php

namespace app\middleware;

use app\service\AuthService;
use app\service\PermissionService;
use think\Request;

/**
 * 权限校验中间件
 */
class CheckPermission
{
    /**
     * 无需权限校验的路径
     */
    protected $allowPaths = [
        'login',
        'login/do',
        'logout',
        'login/menus',
        'dashboard',
        'dashboard/data',
        'dashboard/student',
        'dashboard/teacher',
        'dashboard/counselor',
        'profile',
        'profile/save',
        'profile/password',
    ];

    /**
     * 处理请求
     */
    public function handle(Request $request, \Closure $next)
    {
        $path = trim($request->pathinfo(), '/');

        // 公共路径直接放行
        if (in_array($path, $this->allowPaths, true) || $path === '') {
            return $next($request);
        }

        // 未登录的请求交由登录校验中间件处理，此处不做权限判定
        $adminId = AuthService::id();
        if ($adminId < 1) {
            return $next($request);
        }

        if (!PermissionService::check($adminId, $path)) {
            $msg = '您没有访问该功能的权限';

            if ($request->isJson() || $request->isAjax() || $request->method() === 'POST') {
                return json(['code' => 0, 'msg' => $msg]);
            }

            return view('error/no_permission', ['title' => '学生管理系统 - 无权访问', 'msg' => $msg]);
        }

        return $next($request);
    }
}
