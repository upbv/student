<?php

namespace app\controller;

use app\service\AuthService;
use think\Request;

/**
 * 后台主框架控制器
 */
class IndexController
{
    /**
     * 控制台内容页（供后台框架内嵌加载）
     */
    public function index()
    {
        return view('index/home', [
            'title' => '学生管理系统 - 控制台',
        ]);
    }

    /**
     * 后台框架页
     */
    public function main()
    {
        $nickname = (string) \think\facade\Session::get('nickname', '');
        if ($nickname === '') {
            $nickname = '管理员';
        }

        return view('index/index', [
            'title'            => '学生管理系统 - 控制台',
            'nickname'         => $nickname,
            'nickname_initial' => mb_substr($nickname, 0, 1),
            'adminId'          => AuthService::id(),
        ]);
    }

    /**
     * 首页数据面板
     */
    public function dashboard()
    {
        $studentCount = \app\model\Student::count();
        $classCount   = \app\model\SchoolClass::count();
        $courseCount  = \app\model\Course::count();
        $teacherCount = \app\model\Teacher::count();
        $adminCount   = \app\model\Admin::count();
        $loginCount   = \app\model\LoginLog::where('status', 1)->where('login_time', '>=', strtotime('-7 days'))->count();

        // 各班人数分布
        $classStat = \app\model\SchoolClass::alias('c')
            ->leftJoin('ms_student s', 's.class_id = c.id AND s.delete_time IS NULL')
            ->field('c.name, COUNT(s.id) AS total')
            ->group('c.id,c.name')
            ->order('c.id', 'asc')
            ->select();

        // 课程平均分
        $courseStat = \app\model\SelectCourse::alias('sc')
            ->join('ms_course c', 'c.id = sc.course_id')
            ->whereNotNull('sc.score')
            ->field('c.name, ROUND(AVG(sc.score),1) AS avg_score, MAX(sc.score) AS max_score,
                     MIN(sc.score) AS min_score, COUNT(sc.id) AS total')
            ->group('c.id,c.name')
            ->order('avg_score', 'desc')
            ->select();

        // 最近登录记录
        $recentLogin = [];
        foreach (\app\model\LoginLog::order('id', 'desc')->limit(8)->select() as $log) {
            $recentLogin[] = [
                'username'    => $log->username,
                'status'      => $log->status,
                'status_text' => $log->status_text,
                'login_time'  => date('m-d H:i', (int) $log->login_time),
            ];
        }

        return json([
            'code' => 1,
            'data' => [
                'student' => $studentCount,
                'class'   => $classCount,
                'course'  => $courseCount,
                'teacher' => $teacherCount,
                'admin'   => $adminCount,
                'login7'  => $loginCount,
                'classStat'   => $classStat,
                'courseStat'  => $courseStat,
                'recentLogin' => $recentLogin,
            ],
        ]);
    }

    /**
     * 个人资料
     */
    public function profile()
    {
        $admin = \app\model\Admin::find(AuthService::id());

        $info = $admin ? $admin->toArray() : [];
        $info['status_text'] = $admin ? $admin->status_text : '未知';
        $info['status_dot']  = $admin && $admin->status == 1 ? 'status-on' : 'status-off';
        $info['last_login_text'] = !empty($admin->last_login)
            ? date('Y-m-d H:i:s', $admin->last_login)
            : '尚未登录';
        $info['last_login_ip']   = $admin->last_login_ip ?: '-';
        $info['create_time_text'] = !empty($admin->create_time) ? (string) $admin->create_time : '-';
        $info['nickname_initial'] = mb_substr($info['nickname'] ?? '管', 0, 1);
        $info['role_names']       = $admin ? implode('、', $admin->roleNames()) : '未分配角色';

        return view('index/profile', [
            'title' => '学生管理系统 - 个人资料',
            'admin' => $info,
        ]);
    }

    /**
     * 保存个人资料
     */
    public function saveProfile(Request $request)
    {
        $adminId = AuthService::id();
        $admin   = \app\model\Admin::find($adminId);
        if (!$admin) {
            return json(['code' => 0, 'msg' => '账号不存在']);
        }

        $nickname = trim((string) $request->post('nickname', ''));
        $email    = trim((string) $request->post('email', ''));
        $phone    = trim((string) $request->post('phone', ''));

        if ($nickname === '') {
            return json(['code' => 0, 'msg' => '姓名不能为空']);
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json(['code' => 0, 'msg' => '邮箱格式不正确']);
        }

        $admin->save([
            'nickname' => $nickname,
            'email'    => $email,
            'phone'    => $phone,
        ]);

        \think\facade\Session::set('nickname', $nickname);

        return json(['code' => 1, 'msg' => '保存成功']);
    }

    /**
     * 修改密码
     */
    public function changePassword(Request $request)
    {
        $adminId = AuthService::id();
        $admin   = \app\model\Admin::find($adminId);
        if (!$admin) {
            return json(['code' => 0, 'msg' => '账号不存在']);
        }

        $oldPassword = (string) $request->post('old_password', '');
        $newPassword = (string) $request->post('new_password', '');
        $confirm     = (string) $request->post('new_password_confirm', '');

        if ($oldPassword === '' || $newPassword === '') {
            return json(['code' => 0, 'msg' => '原密码和新密码不能为空']);
        }
        if ($newPassword !== $confirm) {
            return json(['code' => 0, 'msg' => '两次输入的新密码不一致']);
        }
        if (mb_strlen($newPassword) < 6) {
            return json(['code' => 0, 'msg' => '新密码长度不能少于 6 位']);
        }
        if (!AuthService::verifyPassword($oldPassword, $admin->password, $admin->salt)) {
            return json(['code' => 0, 'msg' => '原密码不正确']);
        }

        $admin->save([
            'password'    => AuthService::makePassword($newPassword, $admin->salt),
            'login_fail'  => 0,
        ]);


        return json(['code' => 1, 'msg' => '密码修改成功']);
    }
}
