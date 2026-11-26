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
     * 控制台内容页：按登录角色选择不同的工作台
     */
    public function index()
    {
        $title = '学生管理系统 - 控制台';

        switch (AuthService::primaryRole()) {
            case 'student':
                return view('index/student', ['title' => $title]);
            case 'teacher':
                return view('index/teacher', ['title' => $title]);
            case 'counselor':
                return view('index/counselor', ['title' => $title]);
            default:
                return view('index/home', ['title' => $title]);
        }
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
        $names = $admin ? $admin->roleNames() : [];
        $info['role_names'] = $names ? implode('、', $names) : '未分配角色';

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

    /* ==================== 角色工作台数据 ==================== */

    /**
     * 学生面板：我的课程、成绩、请假与资助概览
     */
    public function studentPanel()
    {
        $studentId = AuthService::studentId();
        if ($studentId < 1) {
            return json(['code' => 1, 'data' => [
                'profile' => [], 'stats' => [], 'grades' => [], 'leaves' => [], 'aids' => [],
            ]]);
        }

        $student = \app\model\Student::find($studentId);
        $class   = $student ? \app\model\SchoolClass::find($student->class_id) : null;

        $records = \app\model\SelectCourse::alias('sc')
            ->join('ms_course c', 'c.id = sc.course_id')
            ->leftJoin('ms_teacher t', 't.id = c.teacher_id')
            ->where('sc.student_id', $studentId)
            ->field('sc.id, sc.semester, sc.score, sc.status, c.name AS course_name,
                     c.credit, t.name AS teacher_name')
            ->order('sc.id', 'asc')
            ->select();

        $grades = [];
        $credits = 0.0;
        $sum = 0.0;
        $scored = 0;
        $selected = 0;
        foreach ($records as $item) {
            if ($item->status != 1) {
                continue;
            }
            $selected++;
            $credits += (float) $item->credit;
            if ($item->score !== null) {
                $sum += (float) $item->score;
                $scored++;
            }
            $grades[] = [
                'course_name'  => $item->course_name,
                'credit'       => $item->credit,
                'teacher_name' => $item->teacher_name,
                'semester'     => $item->semester,
                'score'        => $item->score,
            ];
        }

        $leaves = [];
        foreach (\app\model\Leave::where('student_id', $studentId)->order('id', 'desc')->limit(5)->select() as $l) {
            $leaves[] = [
                'type_text'   => $l->type_text,
                'start_date'  => $l->start_date,
                'end_date'    => $l->end_date,
                'days'        => $l->days,
                'status'      => (int) $l->status,
                'status_text' => $l->status_text,
            ];
        }

        $aids = [];
        foreach (\app\model\Aid::where('student_id', $studentId)->order('id', 'desc')->limit(5)->select() as $a) {
            $aids[] = [
                'type_text'   => $a->type_text,
                'semester'    => $a->semester,
                'amount'      => $a->amount,
                'status'      => (int) $a->status,
                'status_text' => $a->status_text,
            ];
        }

        $leavePending = \app\model\Leave::where('student_id', $studentId)->where('status', 0)->count();

        return json(['code' => 1, 'data' => [
            'profile' => [
                'name'        => $student->name ?? '',
                'student_no'  => $student->student_no ?? '',
                'class_name'  => $class->name ?? '未分班',
                'status_text' => $student ? $student->status_text : '',
                'phone'       => $student->phone ?? '',
            ],
            'stats' => [
                'courseCount'  => $selected,
                'creditTotal'  => round($credits, 1),
                'avgScore'     => $scored ? round($sum / $scored, 1) : '-',
                'leavePending' => $leavePending,
                'aidCount'     => \app\model\Aid::where('student_id', $studentId)->count(),
            ],
            'grades' => $grades,
            'leaves' => $leaves,
            'aids'   => $aids,
        ]]);
    }

    /**
     * 教师面板：我的授课与我的学生
     */
    public function teacherPanel()
    {
        $teacherId = AuthService::teacherId();
        if ($teacherId < 1) {
            return json(['code' => 1, 'data' => [
                'profile' => [], 'stats' => [], 'courses' => [], 'students' => [],
            ]]);
        }

        $teacher = \app\model\Teacher::find($teacherId);
        $dept    = $teacher ? \app\model\Department::find($teacher->department_id) : null;

        $courses = [];
        $studentIds = [];
        $ungradedTotal = 0;
        $scoreSum = 0.0;
        $scoreCount = 0;

        $courseList = \app\model\Course::where('teacher_id', $teacherId)->order('id', 'asc')->select();
        foreach ($courseList as $course) {
            $recordQuery = \app\model\SelectCourse::where('course_id', $course->id)->where('status', 1);
            $count    = (clone $recordQuery)->count();
            $ungraded = (clone $recordQuery)->whereNull('score')->count();
            $avg      = (clone $recordQuery)->whereNotNull('score')->avg('score');
            $ungradedTotal += $ungraded;
            if ($avg !== null) {
                $scoreSum += (float) $avg;
                $scoreCount++;
            }
            foreach ((clone $recordQuery)->column('student_id') as $sid) {
                $studentIds[$sid] = true;
            }

            $courses[] = [
                'name'           => $course->name,
                'course_no'      => $course->course_no,
                'semester'       => $course->semester,
                'student_count'  => $count,
                'ungraded'       => $ungraded,
                'avg_score'      => $avg === null ? '-' : round((float) $avg, 1),
            ];
        }

        $students = [];
        $classMap = \app\model\SchoolClass::column('name', 'id');
        if ($studentIds) {
            foreach (\app\model\Student::whereIn('id', array_keys($studentIds))->order('student_no', 'asc')->limit(8)->select() as $stu) {
                $students[] = [
                    'student_no' => $stu->student_no,
                    'name'       => $stu->name,
                    'class_name' => $classMap[$stu->class_id] ?? '未分班',
                    'phone'      => $stu->phone,
                ];
            }
        }

        return json(['code' => 1, 'data' => [
            'profile' => [
                'name'       => $teacher->name ?? '',
                'title'      => $teacher->title ?? '',
                'department' => $dept->name ?? '',
            ],
            'stats' => [
                'courseCount'   => count($courses),
                'studentCount'  => count($studentIds),
                'ungradedTotal' => $ungradedTotal,
                'avgScore'      => $scoreCount ? round($scoreSum / $scoreCount, 1) : '-',
            ],
            'courses'  => $courses,
            'students' => $students,
        ]]);
    }

    /**
     * 辅导员面板：学生事务概览与待审批请假
     */
    public function counselorPanel()
    {
        $studentCount = \app\model\Student::count();
        $classCount   = \app\model\SchoolClass::count();
        $leavePending = \app\model\Leave::where('status', 0)->count();
        $leaveTotal   = \app\model\Leave::count();
        $aidPending   = \app\model\Aid::where('status', 0)->count();
        $aidAmount    = (float) \app\model\Aid::where('status', 1)->sum('amount');

        $pendingLeaves = [];
        $rows = \app\model\Leave::alias('l')
            ->join('ms_student s', 's.id = l.student_id')
            ->leftJoin('ms_class c', 'c.id = s.class_id')
            ->where('l.status', 0)
            ->field('l.id, l.type, l.start_date, l.end_date, l.days, l.reason,
                     s.student_no, s.name AS student_name, c.name AS class_name')
            ->order('l.id', 'asc')
            ->limit(6)
            ->select();
        foreach ($rows as $r) {
            $pendingLeaves[] = [
                'id'           => $r->id,
                'type_text'    => $r->type == 1 ? '事假' : ($r->type == 2 ? '病假' : '其他'),
                'student_name' => $r->student_name,
                'student_no'   => $r->student_no,
                'class_name'   => $r->class_name,
                'start_date'   => $r->start_date,
                'end_date'     => $r->end_date,
                'days'         => $r->days,
                'reason'       => $r->reason,
            ];
        }

        $recentAids = [];
        $rows = \app\model\Aid::alias('a')
            ->join('ms_student s', 's.id = a.student_id')
            ->field('a.id, a.type, a.semester, a.amount, a.status, s.name AS student_name, s.student_no')
            ->order('a.id', 'desc')
            ->limit(6)
            ->select();
        foreach ($rows as $r) {
            $recentAids[] = [
                'type_text'    => $r->type == 1 ? '助学金' : ($r->type == 2 ? '奖学金' : ($r->type == 3 ? '困难补助' : '勤工助学')),
                'student_name' => $r->student_name,
                'student_no'   => $r->student_no,
                'semester'     => $r->semester,
                'amount'       => $r->amount,
                'status'       => (int) $r->status,
                'status_text'  => $r->status == 1 ? '已通过' : ($r->status == 2 ? '未通过' : '申请中'),
            ];
        }

        return json(['code' => 1, 'data' => [
            'stats' => [
                'student'      => $studentCount,
                'class'        => $classCount,
                'leavePending' => $leavePending,
                'leaveTotal'   => $leaveTotal,
                'aidPending'   => $aidPending,
                'aidAmount'    => round($aidAmount, 2),
            ],
            'pendingLeaves' => $pendingLeaves,
            'recentAids'    => $recentAids,
        ]]);
    }
}
