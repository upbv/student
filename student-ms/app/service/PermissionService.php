<?php

namespace app\service;

use app\model\AdminRole;
use app\model\Auth;
use app\model\RoleAuth;

/**
 * 权限服务：基于 RBAC 的菜单与按钮权限控制
 */
class PermissionService
{
    /** 超级管理员角色标识，拥有全部权限 */
    const SUPER_ROLE = 'admin';

    /**
     * 取得指定账号拥有的权限ID集合
     * @return array<int>
     */
    public static function authIds(int $adminId): array
    {
        $roleIds = AdminRole::where('admin_id', $adminId)->column('role_id');
        if (empty($roleIds)) {
            return [];
        }

        // 超级管理员直接拥有全部权限
        if (self::hasSuperRole($roleIds)) {
            return Auth::column('id');
        }

        return RoleAuth::whereIn('role_id', $roleIds)->distinct(true)->column('auth_id');
    }

    /**
     * 判断角色集合中是否包含超级管理员
     */
    public static function hasSuperRole(array $roleIds): bool
    {
        $codes = \app\model\Role::whereIn('id', $roleIds)->column('code');
        return in_array(self::SUPER_ROLE, $codes, true);
    }

    /**
     * 判断账号是否拥有指定权限
     */
    public static function check(int $adminId, string $path): bool
    {
        $authIds = self::authIds($adminId);
        if (empty($authIds)) {
            return false;
        }

        $path = trim($path, '/');
        if ($path === '') {
            return true;
        }

        // 先按完整路径匹配
        $auth = Auth::where('path', $path)->find();
        if ($auth) {
            return in_array((int) $auth->id, $authIds, true);
        }

        // 页面路由（如 student）统一指向该功能的查看权限（如 student/list）
        $listAuth = Auth::where('path', $path . '/list')->find();
        if ($listAuth) {
            return in_array((int) $listAuth->id, $authIds, true);
        }

        // 路径不在权限表中时按放行处理，由具体业务再做判断
        return true;
    }

    /**
     * 获取账号可见的菜单树（只包含菜单类型，type=1）
     */
    public static function menuTree(int $adminId): array
    {
        $authIds = self::authIds($adminId);
        if (empty($authIds)) {
            return [];
        }

        $auths = Auth::where('status', 1)
            ->where('type', 1)
            ->whereIn('id', $authIds)
            ->order('sort', 'asc')
            ->select();

        // 授权时通常只勾选到具体功能菜单，这里把它们的各级上级目录一并补上，
        // 否则菜单树会因为缺少根节点而无法组装
        $list  = [];
        $seen  = [];
        foreach ($auths as $item) {
            $chain = self::ancestorIds((int) $item->pid);
            foreach (array_merge($chain, [$item->id]) as $id) {
                if (isset($seen[$id])) {
                    continue;
                }
                $seen[$id] = true;
            }
        }

        if (!empty($seen)) {
            $parents = Auth::where('status', 1)
                ->where('type', 1)
                ->whereIn('id', array_keys($seen))
                ->order('sort', 'asc')
                ->select();
            foreach ($parents as $item) {
                $list[] = $item->toArray();
            }
        }

        // 个人页面只对绑定了学生/教师记录的账号显示
        $personal = [
            'mygrade'   => AuthService::studentId() > 0,
            'myleave'   => AuthService::studentId() > 0,
            'myaid'     => AuthService::studentId() > 0,
            'mycourse'  => AuthService::teacherId() > 0,
            'mystudents'=> AuthService::teacherId() > 0,
        ];
        $list = array_values(array_filter($list, function ($item) use ($personal) {
            $path = $item['path'] ?? '';
            return !isset($personal[$path]) || $personal[$path];
        }));

        return self::buildTree($list);
    }

    /**
     * 逐级向上取出祖先菜单ID
     */
    protected static function ancestorIds(int $pid): array
    {
        $ids = [];
        while ($pid > 0) {
            $ids[] = $pid;
            $pid = (int) Auth::where('id', $pid)->value('pid');
            if (count($ids) > 20) {
                break;
            }
        }
        return $ids;
    }

    /**
     * 将扁平列表组装为树形结构
     */
    protected static function buildTree(array $list, int $pid = 0): array
    {
        $tree = [];
        foreach ($list as $item) {
            if ((int) $item['pid'] !== $pid) {
                continue;
            }
            // 顶级分组在过滤后没有子菜单时不再显示
            if ((int) $item['pid'] === 0 && ($item['path'] ?? '') === '') {
                $children = self::buildTree($list, (int) $item['id']);
                if (empty($children)) {
                    continue;
                }
                $item['children'] = $children;
                $tree[] = $item;
                continue;
            }
            $children = self::buildTree($list, (int) $item['id']);
            if ($children) {
                $item['children'] = $children;
            }
            $tree[] = $item;
        }
        return $tree;
    }

    /**
     * 取得账号拥有的角色ID数组
     */
    public static function roleIds(int $adminId): array
    {
        return AdminRole::where('admin_id', $adminId)->column('role_id');
    }
}
