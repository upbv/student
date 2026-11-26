<?php

namespace app\controller;

use app\model\Department;
use app\model\Teacher;
use app\service\LogService;
use think\Request;

/**
 * 教师管理控制器
 */
class TeacherController
{
    /**
     * 教师列表页
     */
    public function index()
    {
        return view('teacher/index', [
            'title'       => '学生管理系统 - 教师管理',
            'departments' => Department::order('id', 'asc')->select(),
        ]);
    }

    /**
     * 获取教师列表
     */
    public function lists(Request $request)
    {
        $page    = max(1, (int) $request->param('page', 1));
        $limit   = min(100, max(1, (int) $request->param('limit', 15)));
        $keyword = trim((string) $request->param('keyword', ''));
        $deptId  = (int) $request->param('department_id', 0);

        $query = Teacher::withTrashed()->order('id', 'asc');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('teacher_no', "%{$keyword}%")
                  ->whereOr('name', 'like', "%{$keyword}%")
                  ->whereOr('phone', 'like', "%{$keyword}%");
            });
        }
        if ($deptId > 0) {
            $query->where('department_id', $deptId);
        }

        $total = (clone $query)->count();
        $list  = $query->page($page, $limit)->select();

        $deptMap = Department::column('name', 'id');
        $data = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['gender_text']     = $item->gender_text;
            $row['department_name'] = $deptMap[$item->department_id] ?? '未设置';
            $row['course_count']    = \app\model\Course::where('teacher_id', $item->id)->count();
            $data[] = $row;
        }

        return json(['code' => 0, 'count' => $total, 'data' => $data]);
    }

    /**
     * 新增/编辑教师
     */
    public function save(Request $request)
    {
        $id        = (int) $request->post('id', 0);
        $teacherNo = trim((string) $request->post('teacher_no', ''));
        $name      = trim((string) $request->post('name', ''));
        $gender    = (int) $request->post('gender', 0);
        $title     = trim((string) $request->post('title', ''));
        $phone     = trim((string) $request->post('phone', ''));
        $email     = trim((string) $request->post('email', ''));
        $deptId    = (int) $request->post('department_id', 0);

        if ($teacherNo === '' || $name === '') {
            return json(['code' => 0, 'msg' => '工号和姓名不能为空']);
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json(['code' => 0, 'msg' => '邮箱格式不正确']);
        }

        $existQuery = Teacher::withTrashed()->where('teacher_no', $teacherNo);
        if ($id > 0) {
            $existQuery->where('id', '<>', $id);
        }
        if ($existQuery->find()) {
            return json(['code' => 0, 'msg' => '工号已存在']);
        }

        $data = [
            'teacher_no'    => $teacherNo,
            'name'          => $name,
            'gender'        => $gender,
            'title'         => $title,
            'phone'         => $phone,
            'email'         => $email,
            'department_id' => $deptId,
        ];

        if ($id > 0) {
            $teacher = Teacher::find($id);
            if (!$teacher) {
                return json(['code' => 0, 'msg' => '教师不存在']);
            }
            $teacher->save($data);
            $action = '编辑教师';
        } else {
            Teacher::create($data);
            $action = '新增教师';
        }

        LogService::writeFromRequest($request, '教师管理', $action);

        return json(['code' => 1, 'msg' => $action . '成功']);
    }

    /**
     * 删除教师
     */
    public function delete(Request $request)
    {
        $id = (int) $request->post('id', 0);

        if (\app\model\Course::where('teacher_id', $id)->count() > 0) {
            return json(['code' => 0, 'msg' => '该教师仍承担授课任务，无法删除']);
        }

        Teacher::destroy($id);
        LogService::writeFromRequest($request, '教师管理', '删除教师');

        return json(['code' => 1, 'msg' => '删除成功']);
    }
}
