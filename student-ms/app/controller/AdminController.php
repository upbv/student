<?php

namespace app\controller;

use app\model\Admin;
use app\model\AdminRole;
use app\model\Role;
use app\service\AuthService;
use think\Request;

/**
 * 账号管理控制器
 */
class AdminController
{
    /**
     * 账号列表页
     */
    public function index()
    {
        return view('admin/index', [
            'title' => '学生管理系统 - 账号管理',
            'roles' => Role::where('status', 1)->order('sort', 'asc')->select(),
        ]);
    }

    /**
     * 获取账号列表数据
     */
    public function lists(Request $request)
    {
        $page   = max(1, (int) $request->param('page', 1));
        $limit  = min(100, max(1, (int) $request->param('limit', 15)));
        $keyword = trim((string) $request->param('keyword', ''));
        $status = $request->param('status', '');
        $roleId = (int) $request->param('role_id', 0);

        $query = Admin::withTrashed()->order('id', 'asc');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('username', "%{$keyword}%")
                  ->whereOr('nickname', 'like', "%{$keyword}%");
            });
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        if ($roleId > 0) {
            $ids = AdminRole::where('role_id', $roleId)->column('admin_id');
            $query->whereIn('id', $ids ?: [0]);
        }

        $total = (clone $query)->count();
        $list  = $query->page($page, $limit)->select();

        $data = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['status_text'] = $item->status_text;
            $row['sex_text']    = $item->sex_text;
            $row['role_names'] = $item->roleNames() ? implode('、', $item->roleNames()) : '未分配';
            $row['role_ids']   = AdminRole::where('admin_id', $item->id)->column('role_id');
            $row['last_login'] = $item->last_login ? date('Y-m-d H:i:s', $item->last_login) : '从未登录';
            $data[] = $row;
        }

        return json([
            'code'  => 0,
            'count' => $total,
            'data'  => $data,
        ]);
    }

    /**
     * 新增/编辑账号
     */
    public function save(Request $request)
    {
        $id       = (int) $request->post('id', 0);
        $username = trim((string) $request->post('username', ''));
        $nickname = trim((string) $request->post('nickname', ''));
        $password = (string) $request->post('password', '');
        $email    = trim((string) $request->post('email', ''));
        $phone    = trim((string) $request->post('phone', ''));
        $sex      = (int) $request->post('sex', 0);
        $status   = (int) $request->post('status', 1);
        $roleIds  = $request->post('role_ids', []);
        if (!is_array($roleIds)) {
            $roleIds = $roleIds === '' ? [] : explode(',', (string) $roleIds);
        }
        $roleIds = array_map('intval', $roleIds);

        if ($username === '' || $nickname === '') {
            return json(['code' => 0, 'msg' => '账号和姓名不能为空']);
        }
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{3,29}$/', $username)) {
            return json(['code' => 0, 'msg' => '账号需以字母开头，由字母、数字、下划线组成，长度 4-30 位']);
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json(['code' => 0, 'msg' => '邮箱格式不正确']);
        }
        if ($id === 0 && mb_strlen($password) < 6) {
            return json(['code' => 0, 'msg' => '初始密码长度不能少于 6 位']);
        }
        if ($id > 0 && $password !== '' && mb_strlen($password) < 6) {
            return json(['code' => 0, 'msg' => '密码长度不能少于 6 位']);
        }

        // 账号唯一性校验
        $existQuery = Admin::where('username', $username);
        if ($id > 0) {
            $existQuery->where('id', '<>', $id);
        }
        if ($existQuery->find()) {
            return json(['code' => 0, 'msg' => '账号已存在']);
        }

        if ($id > 0) {
            $admin = Admin::find($id);
            if (!$admin) {
                return json(['code' => 0, 'msg' => '账号不存在']);
            }
            $data = [
                'username' => $username,
                'nickname' => $nickname,
                'email'    => $email,
                'phone'    => $phone,
                'sex'      => $sex,
                'status'   => $status,
            ];
            if ($password !== '') {
                $data['password']   = AuthService::makePassword($password, $admin->salt);
                $data['login_fail'] = 0;
            }
            $admin->save($data);
            $action = '编辑账号';
        } else {
            $salt = substr(md5(uniqid((string) microtime(true), true)), 0, 8);
            $admin = Admin::create([
                'username' => $username,
                'nickname' => $nickname,
                'password' => AuthService::makePassword($password, $salt),
                'salt'     => $salt,
                'email'    => $email,
                'phone'    => $phone,
                'sex'      => $sex,
                'status'   => $status,
            ]);
            $action = '新增账号';
        }

        // 保存角色关联
        AdminRole::where('admin_id', $admin->id)->delete();
        foreach ($roleIds as $roleId) {
            AdminRole::create(['admin_id' => $admin->id, 'role_id' => $roleId]);
        }


        return json(['code' => 1, 'msg' => $action . '成功']);
    }

    /**
     * 删除账号
     */
    public function delete(Request $request)
    {
        $ids = $request->post('ids', []);
        if (!is_array($ids)) {
            $ids = explode(',', (string) $ids);
        }
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);

        if (empty($ids)) {
            return json(['code' => 0, 'msg' => '请选择要删除的账号']);
        }
        if (in_array(1, $ids, true)) {
            return json(['code' => 0, 'msg' => '超级管理员账号不允许删除']);
        }

        Admin::destroy($ids);
        AdminRole::whereIn('admin_id', $ids)->delete();


        return json(['code' => 1, 'msg' => '删除成功']);
    }

    /**
     * 重置密码
     */
    public function resetPassword(Request $request)
    {
        $id       = (int) $request->post('id', 0);
        $password = (string) $request->post('password', 'admin123');

        if (mb_strlen($password) < 6) {
            return json(['code' => 0, 'msg' => '密码长度不能少于 6 位']);
        }

        $admin = Admin::find($id);
        if (!$admin) {
            return json(['code' => 0, 'msg' => '账号不存在']);
        }

        $admin->save([
            'password'   => AuthService::makePassword($password, $admin->salt),
            'login_fail' => 0,
        ]);


        return json(['code' => 1, 'msg' => '密码已重置为：' . $password]);
    }
}
