<?php

namespace app\controller;

use app\model\AdminRole;
use app\model\Role;
use app\model\RoleAuth;
use app\service\LogService;
use think\Request;

/**
 * 角色管理控制器
 */
class RoleController
{
    /**
     * 角色列表页
     */
    public function index()
    {
        return view('role/index', [
            'title' => '学生管理系统 - 角色管理',
        ]);
    }

    /**
     * 获取角色列表
     */
    public function lists(Request $request)
    {
        $page  = max(1, (int) $request->param('page', 1));
        $limit = min(100, max(1, (int) $request->param('limit', 15)));
        $keyword = trim((string) $request->param('keyword', ''));

        $query = Role::order('sort', 'asc')->order('id', 'asc');
        if ($keyword !== '') {
            $query->whereLike('name', "%{$keyword}%");
        }

        $total = (clone $query)->count();
        $list  = $query->page($page, $limit)->select();

        $data = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['admin_count'] = AdminRole::where('role_id', $item->id)->count();
            $row['auth_count']  = RoleAuth::where('role_id', $item->id)->count();
            $data[] = $row;
        }

        return json(['code' => 0, 'count' => $total, 'data' => $data]);
    }

    /**
     * 取得全部角色（供下拉选择）
     */
    public function all()
    {
        $list = Role::where('status', 1)->order('sort', 'asc')->select();
        return json(['code' => 1, 'data' => $list]);
    }

    /**
     * 新增/编辑角色
     */
    public function save(Request $request)
    {
        $id     = (int) $request->post('id', 0);
        $name   = trim((string) $request->post('name', ''));
        $code   = trim((string) $request->post('code', ''));
        $remark = trim((string) $request->post('remark', ''));
        $status = (int) $request->post('status', 1);
        $sort   = (int) $request->post('sort', 0);

        if ($name === '' || $code === '') {
            return json(['code' => 0, 'msg' => '角色名称和标识不能为空']);
        }
        if (!preg_match('/^[a-z][a-z0-9_]{1,29}$/', $code)) {
            return json(['code' => 0, 'msg' => '角色标识需以小写字母开头，仅含小写字母、数字和下划线']);
        }

        $existQuery = Role::where('code', $code);
        if ($id > 0) {
            $existQuery->where('id', '<>', $id);
        }
        if ($existQuery->find()) {
            return json(['code' => 0, 'msg' => '角色标识已存在']);
        }

        if ($id > 0) {
            $role = Role::find($id);
            if (!$role) {
                return json(['code' => 0, 'msg' => '角色不存在']);
            }
            $role->save([
                'name'   => $name,
                'code'   => $code,
                'remark' => $remark,
                'status' => $status,
                'sort'   => $sort,
            ]);
            $action = '编辑角色';
        } else {
            $role = Role::create([
                'name'   => $name,
                'code'   => $code,
                'remark' => $remark,
                'status' => $status,
                'sort'   => $sort,
            ]);
            $action = '新增角色';
        }

        // 保存权限关联
        $authIds = $request->post('auth_ids', []);
        if (!is_array($authIds)) {
            $authIds = $authIds === '' ? [] : explode(',', (string) $authIds);
        }
        $authIds = array_map('intval', $authIds);
        RoleAuth::where('role_id', $role->id)->delete();
        foreach ($authIds as $authId) {
            RoleAuth::create(['role_id' => $role->id, 'auth_id' => $authId]);
        }

        LogService::writeFromRequest($request, '角色管理', $action);

        return json(['code' => 1, 'msg' => $action . '成功']);
    }

    /**
     * 单独保存角色权限
     */
    public function assignAuth(Request $request)
    {
        $roleId = (int) $request->post('id', 0);
        if ($roleId < 1) {
            return json(['code' => 0, 'msg' => '请选择角色']);
        }
        if (!Role::find($roleId)) {
            return json(['code' => 0, 'msg' => '角色不存在']);
        }

        $authIds = $request->post('auth_ids', []);
        if (!is_array($authIds)) {
            $authIds = $authIds === '' ? [] : explode(',', (string) $authIds);
        }
        $authIds = array_map('intval', $authIds);
        $authIds = array_values(array_filter($authIds));

        RoleAuth::where('role_id', $roleId)->delete();
        foreach ($authIds as $authId) {
            RoleAuth::create(['role_id' => $roleId, 'auth_id' => $authId]);
        }

        LogService::writeFromRequest($request, '角色管理', '分配权限', 1, '', '共分配 ' . count($authIds) . ' 项权限');

        return json(['code' => 1, 'msg' => '权限保存成功']);
    }

    /**
     * 获取角色已分配的权限ID
     */
    public function authIds(Request $request)
    {
        $roleId = (int) $request->param('role_id', 0);
        $ids    = RoleAuth::where('role_id', $roleId)->column('auth_id');
        return json(['code' => 1, 'data' => $ids]);
    }

    /**
     * 删除角色
     */
    public function delete(Request $request)
    {
        $id = (int) $request->post('id', 0);

        $role = Role::find($id);
        if (!$role) {
            return json(['code' => 0, 'msg' => '角色不存在']);
        }
        if ($role->code === 'admin') {
            return json(['code' => 0, 'msg' => '超级管理员角色不允许删除']);
        }
        if (AdminRole::where('role_id', $id)->count() > 0) {
            return json(['code' => 0, 'msg' => '该角色下仍有账号，请先解除关联']);
        }

        Role::destroy($id);
        RoleAuth::where('role_id', $id)->delete();

        LogService::writeFromRequest($request, '角色管理', '删除角色');

        return json(['code' => 1, 'msg' => '删除成功']);
    }
}
