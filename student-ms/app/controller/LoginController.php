<?php

namespace app\controller;

use app\service\AuthService;
use app\service\PermissionService;
use think\Request;

/**
 * 登录控制器
 */
class LoginController
{
    /**
     * 登录页
     */
    public function index(Request $request)
    {
        if (AuthService::id() > 0) {
            return redirect('/');
        }

        return view('login/index', [
            'title' => '学生管理系统 - 用户登录',
        ]);
    }

    /**
     * 处理登录请求
     */
    public function doLogin(Request $request)
    {
        $username = trim((string) $request->post('username', ''));
        $password = (string) $request->post('password', '');

        if ($username === '' || $password === '') {
            return json(['code' => 0, 'msg' => '账号和密码不能为空']);
        }

        // 简单频率限制：同一 IP 10 秒内仅允许一次登录尝试
        $ip = $request->ip();
        $lastTime = (int) \think\facade\Cache::get('login_try_' . md5($ip));
        if ($lastTime && time() - $lastTime < 10) {
            return json(['code' => 0, 'msg' => '操作过于频繁，请稍后再试']);
        }
        \think\facade\Cache::set('login_try_' . md5($ip), time(), 30);

        [$ok, $msg] = AuthService::login($username, $password, $ip, $request->header('user-agent', ''));

        if ($ok) {
            return json(['code' => 1, 'msg' => '登录成功', 'url' => '/']);
        }

        return json(['code' => 0, 'msg' => $msg]);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        AuthService::logout();
        return redirect('/login');
    }

    /**
     * 获取当前登录者的菜单，用于渲染后台框架
     */
    public function menus()
    {
        $adminId = AuthService::id();
        if ($adminId < 1) {
            return json(['code' => 0, 'msg' => '未登录']);
        }

        return json([
            'code' => 1,
            'data' => PermissionService::menuTree($adminId),
        ]);
    }
}
