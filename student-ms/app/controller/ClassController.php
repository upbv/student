<?php

namespace app\controller;

use app\model\Department;
use app\model\SchoolClass;
use app\model\Student;
use app\service\LogService;
use think\Request;

/**
 * 班级管理控制器
 */
class ClassController
{
    /**
     * 班级列表页
     */
    public function index()
    {
        return view('class/index', [
            'title'       => '学生管理系统 - 班级管理',
            'departments' => Department::order('id', 'asc')->select(),
        ]);
    }

    /**
     * 获取班级列表
     */
    public function lists(Request $request)
    {
        $page    = max(1, (int) $request->param('page', 1));
        $limit   = min(100, max(1, (int) $request->param('limit', 15)));
        $keyword = trim((string) $request->param('keyword', ''));
        $deptId  = (int) $request->param('department_id', 0);

        $query = SchoolClass::withTrashed()->order('id', 'asc');

        if ($keyword !== '') {
            $query->whereLike('name', "%{$keyword}%");
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
            $row['department_name'] = $deptMap[$item->department_id] ?? '未设置';
            $row['status_text']     = $item->status == 1 ? '在读' : '毕业';
            $row['real_num']        = Student::where('class_id', $item->id)->count();
            $data[] = $row;
        }

        return json(['code' => 0, 'count' => $total, 'data' => $data]);
    }

    /**
     * 新增/编辑班级
     */
    public function save(Request $request)
    {
        $id         = (int) $request->post('id', 0);
        $name       = trim((string) $request->post('name', ''));
        $grade      = trim((string) $request->post('grade', ''));
        $deptId     = (int) $request->post('department_id', 0);
        $headTeacher = trim((string) $request->post('head_teacher', ''));
        $status     = (int) $request->post('status', 1);

        if ($name === '') {
            return json(['code' => 0, 'msg' => '班级名称不能为空']);
        }
        if ($deptId < 1) {
            return json(['code' => 0, 'msg' => '请选择所属院系']);
        }

        $existQuery = SchoolClass::withTrashed()->where('name', $name);
        if ($id > 0) {
            $existQuery->where('id', '<>', $id);
        }
        if ($existQuery->find()) {
            return json(['code' => 0, 'msg' => '班级名称已存在']);
        }

        $data = [
            'name'         => $name,
            'grade'        => $grade,
            'department_id' => $deptId,
            'head_teacher' => $headTeacher,
            'status'       => $status,
        ];

        if ($id > 0) {
            $class = SchoolClass::find($id);
            if (!$class) {
                return json(['code' => 0, 'msg' => '班级不存在']);
            }
            $class->save($data);
            $action = '编辑班级';
        } else {
            SchoolClass::create($data);
            $action = '新增班级';
        }

        LogService::writeFromRequest($request, '班级管理', $action);

        return json(['code' => 1, 'msg' => $action . '成功']);
    }

    /**
     * 删除班级
     */
    public function delete(Request $request)
    {
        $id = (int) $request->post('id', 0);

        if (Student::where('class_id', $id)->count() > 0) {
            return json(['code' => 0, 'msg' => '该班级下仍有学生，无法删除']);
        }

        SchoolClass::destroy($id);
        LogService::writeFromRequest($request, '班级管理', '删除班级');

        return json(['code' => 1, 'msg' => '删除成功']);
    }
}
