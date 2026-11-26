<?php

namespace app\controller;

use app\model\Auth;
use think\Request;

/**
 * 权限菜单管理控制器
 */
class AuthController
{
    /**
     * 权限列表页
     */
    public function index()
    {
        return view('auth/index', [
            'title' => '学生管理系统 - 权限管理',
        ]);
    }

    /**
     * 获取权限树
     */
    public function lists(Request $request)
    {
        $list = Auth::order('sort', 'asc')->order('id', 'asc')->select();
        $tree = $this->buildTree($list);

        return json(['code' => 0, 'count' => count($list), 'data' => $tree]);
    }

    /**
     * 组装树形结构
     */
    protected function buildTree($list, int $pid = 0): array
    {
        $tree = [];
        foreach ($list as $item) {
            if ((int) $item->pid === $pid) {
                $row = $item->toArray();
                $row['type_text'] = $item->type_text;
                $children = $this->buildTree($list, (int) $item->id);
                if ($children) {
                    $row['children'] = $children;
                }
                $tree[] = $row;
            }
        }
        return $tree;
    }

    /**
     * 新增/编辑权限
     */
    public function save(Request $request)
    {
        $id   = (int) $request->post('id', 0);
        $pid  = (int) $request->post('pid', 0);
        $name = trim((string) $request->post('name', ''));
        $type = (int) $request->post('type', 1);
        $path = trim((string) $request->post('path', ''));
        $icon = trim((string) $request->post('icon', ''));
        $sort = (int) $request->post('sort', 0);
        $status = (int) $request->post('status', 1);

        if ($name === '') {
            return json(['code' => 0, 'msg' => '权限名称不能为空']);
        }
        if ($type === 1 && $path === '') {
            return json(['code' => 0, 'msg' => '菜单类型必须填写路由地址']);
        }
        if ($id > 0 && $pid === $id) {
            return json(['code' => 0, 'msg' => '上级权限不能是自己']);
        }

        // 防止把节点挂到自己的子孙节点下，形成环
        if ($id > 0 && $pid > 0 && $this->isDescendant($pid, $id)) {
            return json(['code' => 0, 'msg' => '上级权限不能选择自己的下级']);
        }

        $data = [
            'pid'    => $pid,
            'name'   => $name,
            'type'   => $type,
            'path'   => $path,
            'icon'   => $icon,
            'sort'   => $sort,
            'status' => $status,
        ];

        if ($id > 0) {
            $auth = Auth::find($id);
            if (!$auth) {
                return json(['code' => 0, 'msg' => '权限不存在']);
            }
            $auth->save($data);
            $action = '编辑权限';
        } else {
            Auth::create($data);
            $action = '新增权限';
        }


        return json(['code' => 1, 'msg' => $action . '成功']);
    }

    /**
     * 判断 candidateId 是否是 parentId 的子孙节点
     */
    protected function isDescendant(int $candidateId, int $parentId): bool
    {
        $pid = Auth::where('id', $candidateId)->value('pid');
        while ($pid > 0) {
            if ($pid === $parentId) {
                return true;
            }
            if ($pid === $candidateId) {
                return true;
            }
            $pid = (int) Auth::where('id', $pid)->value('pid');
        }
        return false;
    }

    /**
     * 删除权限
     */
    public function delete(Request $request)
    {
        $id = (int) $request->post('id', 0);

        if (Auth::where('pid', $id)->count() > 0) {
            return json(['code' => 0, 'msg' => '该权限存在下级，无法删除']);
        }

        Auth::destroy($id);
        \app\model\RoleAuth::where('auth_id', $id)->delete();


        return json(['code' => 1, 'msg' => '删除成功']);
    }
}
