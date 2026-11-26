<?php

namespace app\controller;

use app\model\Course;
use app\model\Department;
use app\model\Teacher;
use think\Request;

/**
 * 课程管理控制器
 */
class CourseController
{
    /**
     * 课程列表页
     */
    public function index()
    {
        return view('course/index', [
            'title'       => '学生管理系统 - 课程管理',
            'teachers'    => Teacher::order('id', 'asc')->select(),
            'departments' => Department::order('id', 'asc')->select(),
        ]);
    }

    /**
     * 获取课程列表
     */
    public function lists(Request $request)
    {
        $page     = max(1, (int) $request->param('page', 1));
        $limit    = min(100, max(1, (int) $request->param('limit', 15)));
        $keyword  = trim((string) $request->param('keyword', ''));
        $teacherId = (int) $request->param('teacher_id', 0);
        $semester  = trim((string) $request->param('semester', ''));

        $query = Course::order('id', 'asc');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('course_no', "%{$keyword}%")
                  ->whereOr('name', 'like', "%{$keyword}%");
            });
        }
        if ($teacherId > 0) {
            $query->where('teacher_id', $teacherId);
        }
        if ($semester !== '') {
            $query->where('semester', $semester);
        }

        $total = (clone $query)->count();
        $list  = $query->page($page, $limit)->select();

        $teacherMap = Teacher::column('name', 'id');
        $deptMap    = Department::column('name', 'id');
        $data = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['course_type_text'] = $item->course_type_text;
            $row['teacher_name']     = $teacherMap[$item->teacher_id] ?? '未指定';
            $row['department_name']  = $deptMap[$item->department_id] ?? '未设置';
            $row['select_count']     = \app\model\SelectCourse::where('course_id', $item->id)->where('status', 1)->count();
            $data[] = $row;
        }

        return json(['code' => 0, 'count' => $total, 'data' => $data]);
    }

    /**
     * 新增/编辑课程
     */
    public function save(Request $request)
    {
        $id        = (int) $request->post('id', 0);
        $courseNo  = trim((string) $request->post('course_no', ''));
        $name      = trim((string) $request->post('name', ''));
        $credit    = (float) $request->post('credit', 0);
        $hours     = (int) $request->post('hours', 0);
        $courseType = (int) $request->post('course_type', 1);
        $teacherId = (int) $request->post('teacher_id', 0);
        $deptId    = (int) $request->post('department_id', 0);
        $semester  = trim((string) $request->post('semester', ''));
        $status    = (int) $request->post('status', 1);
        $remark    = trim((string) $request->post('remark', ''));

        if ($courseNo === '' || $name === '') {
            return json(['code' => 0, 'msg' => '课程编号和名称不能为空']);
        }
        if ($credit <= 0 || $credit > 20) {
            return json(['code' => 0, 'msg' => '学分取值应在 0.1 到 20 之间']);
        }
        if ($semester === '') {
            return json(['code' => 0, 'msg' => '请填写开课学期']);
        }

        $existQuery = Course::withTrashed()->where('course_no', $courseNo);
        if ($id > 0) {
            $existQuery->where('id', '<>', $id);
        }
        if ($existQuery->find()) {
            return json(['code' => 0, 'msg' => '课程编号已存在']);
        }

        $data = [
            'course_no'     => $courseNo,
            'name'          => $name,
            'credit'        => $credit,
            'hours'         => $hours,
            'course_type'   => $courseType,
            'teacher_id'    => $teacherId,
            'department_id' => $deptId,
            'semester'      => $semester,
            'status'        => $status,
            'remark'        => $remark,
        ];

        if ($id > 0) {
            $course = Course::find($id);
            if (!$course) {
                return json(['code' => 0, 'msg' => '课程不存在']);
            }
            $course->save($data);
            $action = '编辑课程';
        } else {
            Course::create($data);
            $action = '新增课程';
        }


        return json(['code' => 1, 'msg' => $action . '成功']);
    }

    /**
     * 删除课程
     */
    public function delete(Request $request)
    {
        $id = (int) $request->post('id', 0);

        // 已退课的记录不阻止删除，仅统计仍处于已选状态的记录
        if (\app\model\SelectCourse::where('course_id', $id)->where('status', 1)->count() > 0) {
            return json(['code' => 0, 'msg' => '该课程已有学生选课，无法删除']);
        }

        Course::destroy($id);

        return json(['code' => 1, 'msg' => '删除成功']);
    }
}
