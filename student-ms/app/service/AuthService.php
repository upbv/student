<?php

namespace app\service;

use app\model\Admin;
use app\model\AdminRole;
use app\model\Auth;
use app\model\RoleAuth;
use think\facade\Session;

/**
 * 认证服务：登录、登出、当前登录者信息
 */
class AuthService
{
    /** 登录失败锁定阈值 */
    const MAX_FAIL = 5;

    /** 锁定时长（秒） */
    const LOCK_TIME = 900;

    /**
     * 执行登录
     * @return array [是否成功, 提示信息]
     */
    public static function login(string $username, string $password, string $ip = '', string $ua = ''): array
    {
        $admin = Admin::where('username', $username)->find();

        if (!$admin) {
            self::writeLoginLog(0, $username, $ip, $ua, 0, '账号不存在');
            return [false, '账号或密码错误'];
        }

        // 停用账号直接拒绝
        if ($admin->status != 1) {
            self::writeLoginLog($admin->id, $username, $ip, $ua, 0, '账号已停用');
            return [false, '账号已被停用，请联系管理员'];
        }

        // 连续失败达到阈值则临时锁定
        if ($admin->login_fail >= self::MAX_FAIL) {
            $space = time() - (int) $admin->update_time;
            if ($space < self::LOCK_TIME) {
                $remain = ceil((self::LOCK_TIME - $space) / 60);
                self::writeLoginLog($admin->id, $username, $ip, $ua, 0, '账号锁定中');
                return [false, "密码连续错误次数过多，请 {$remain} 分钟后再试"];
            }
            // 锁定已过期，重置失败计数
            Admin::where('id', $admin->id)->update(['login_fail' => 0]);
        }

        // 校验密码
        if (!self::verifyPassword($password, $admin->password, $admin->salt)) {
            Admin::where('id', $admin->id)->update([
                'login_fail' => $admin->login_fail + 1,
                'update_time' => time(),
            ]);
            $left = self::MAX_FAIL - ($admin->login_fail + 1);
            self::writeLoginLog($admin->id, $username, $ip, $ua, 0, '密码错误');
            return [false, "账号或密码错误，还可尝试 {$left} 次"];
        }

        // 登录成功，写入会话
        Admin::where('id', $admin->id)->update([
            'login_fail'   => 0,
            'last_login'   => time(),
            'last_login_ip' => $ip,
            'update_time'  => time(),
        ]);

        Session::set('admin_id', $admin->id);
        Session::set('username', $admin->username);
        Session::set('nickname', $admin->nickname);
        Session::set('login_time', time());

        self::writeLoginLog($admin->id, $username, $ip, $ua, 1, '登录成功');

        return [true, '登录成功'];
    }

    /**
     * 退出登录
     */
    public static function logout(): void
    {
        Session::clear();
        Session::regenerate(true);
    }

    /**
     * 当前登录账号ID，未登录返回 0
     */
    public static function id(): int
    {
        return (int) Session::get('admin_id', 0);
    }

    /**
     * 当前登录账号名
     */
    public static function username(): string
    {
        return (string) Session::get('username', '');
    }

    /**
     * 当前账号的角色标识集合
     */
    public static function roleCodes(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $adminId = self::id();
        if ($adminId < 1) {
            return $cache = [];
        }

        $roleIds = \app\model\AdminRole::where('admin_id', $adminId)->column('role_id');
        if (empty($roleIds)) {
            return $cache = [];
        }

        return $cache = \app\model\Role::whereIn('id', $roleIds)->column('code');
    }

    /**
     * 是否拥有指定角色
     */
    public static function hasRole(string $code): bool
    {
        return in_array($code, self::roleCodes(), true);
    }

    /**
     * 当前账号的主要身份，用于选择工作台
     */
    public static function primaryRole(): string
    {
        $codes = self::roleCodes();
        foreach (['admin', 'academic', 'counselor', 'teacher', 'student'] as $code) {
            if (in_array($code, $codes, true)) {
                return $code;
            }
        }
        return '';
    }

    /**
     * 当前登录账号记录（含学生/教师绑定）
     */
    public static function profile(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $adminId = self::id();
        if ($adminId < 1) {
            return $cache = [];
        }

        $admin = \app\model\Admin::find($adminId);
        return $cache = $admin ? $admin->toArray() : [];
    }

    /**
     * 学生账号绑定的学生ID
     */
    public static function studentId(): int
    {
        return (int) (self::profile()['student_id'] ?? 0);
    }

    /**
     * 教师账号绑定的教师ID
     */
    public static function teacherId(): int
    {
        return (int) (self::profile()['teacher_id'] ?? 0);
    }

    /**
     * 校验密码
     */
    public static function verifyPassword(string $password, string $hash, string $salt): bool
    {
        return md5($salt . $password) === $hash;
    }

    /**
     * 生成密码散列
     */
    public static function makePassword(string $password, string $salt = 'studentms'): string
    {
        return md5($salt . $password);
    }

    /**
     * 记录登录日志
     */
    public static function writeLoginLog(int $adminId, string $username, string $ip, string $ua, int $status, string $msg): void
    {
        \app\model\LoginLog::create([
            'admin_id'   => $adminId,
            'username'   => $username,
            'ip'         => $ip,
            'user_agent' => mb_substr($ua, 0, 190),
            'status'     => $status,
            'msg'        => $msg,
            'login_time' => time(),
        ]);
    }
}
