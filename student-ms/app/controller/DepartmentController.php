<?php

namespace app\controller;

use app\model\Department;
use app\service\LogService;
use think\Request;

/**
 * 院系管理控制器
 */
class DepartmentController
{
    /**
     * 院系列表页
     */
    public function index()
    {
        return view('department/index', [
            'title' => '学生管理系统 - 院系管理',
        ]);
    }

    /**
     * 获取院系列表
     */
    public function lists(Request $request)
    {
        $page    = max(1, (int) $request->param('page', 1));
        $limit   = min(100, max(1, (int) $request->param('limit', 15)));
        $keyword = trim((string) $request->param('keyword', ''));

        $query = Department::order('sort', 'asc');
        if ($keyword !== '') {
            $query->whereLike('name', "%{$keyword}%");
        }

        $total = (clone $query)->count();
        $list  = $query->page($page, $limit)->select();

        return json(['code' => 0, 'count' => $total, 'data' => $list]);
    }

    /**
     * 新增/编辑院系
     */
    public function save(Request $request)
    {
        $id     = (int) $request->post('id', 0);
        $name   = trim((string) $request->post('name', ''));
        $code   = trim((string) $request->post('code', ''));
        $leader = trim((string) $request->post('leader', ''));
        $sort   = (int) $request->post('sort', 0);
        $status = (int) $request->post('status', 1);

        if ($name === '') {
            return json(['code' => 0, 'msg' => '院系名称不能为空']);
        }

        $existQuery = Department::where('name', $name);
        if ($id > 0) {
            $existQuery->where('id', '<>', $id);
        }
        if ($existQuery->find()) {
            return json(['code' => 0, 'msg' => '院系名称已存在']);
        }

        $data = [
            'name'   => $name,
            'code'   => $code,
            'leader' => $leader,
            'sort'   => $sort,
            'status' => $status,
        ];

        if ($id > 0) {
            $dept = Department::find($id);
            if (!$dept) {
                return json(['code' => 0, 'msg' => '院系不存在']);
            }
            $dept->save($data);
            $action = '编辑院系';
        } else {
            Department::create($data);
            $action = '新增院系';
        }

        LogService::writeFromRequest($request, '院系管理', $action);

        return json(['code' => 1, 'msg' => $action . '成功']);
    }

    /**
     * 删除院系
     */
    public function delete(Request $request)
    {
        $id = (int) $request->post('id', 0);

        if (\app\model\SchoolClass::where('department_id', $id)->count() > 0) {
            return json(['code' => 0, 'msg' => '该院系下仍有班级，无法删除']);
        }

        Department::destroy($id);
        LogService::writeFromRequest($request, '院系管理', '删除院系');

        return json(['code' => 1, 'msg' => '删除成功']);
    }
}
