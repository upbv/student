<?php

namespace app\middleware;

use app\service\LogService;
use think\Request;

/**
 * 操作日志中间件：自动记录数据变更类操作
 */
class OperationLog
{
    /**
     * 需要记录日志的模块
     */
    protected $modules = [
        'admin'      => '账号管理',
        'role'       => '角色管理',
        'auth'       => '权限管理',
        'student'    => '学生管理',
        'class'      => '班级管理',
        'department' => '院系管理',
        'teacher'    => '教师管理',
        'course'     => '课程管理',
        'select'     => '选课管理',
        'score'      => '成绩管理',
        'loginlog'   => '登录日志',
        'operationlog' => '操作日志',
        'profile'    => '个人中心',
    ];

    /**
     * 处理请求
     */
    public function handle(Request $request, \Closure $next)
    {
        $path = trim($request->pathinfo(), '/');
        $seg  = explode('/', $path);
        $module = $seg[0] ?? '';

        // 仅记录写操作
        $writeMethods = ['POST', 'PUT', 'DELETE'];
        if (!isset($this->modules[$module]) || !in_array($request->method(), $writeMethods, true)) {
            return $next($request);
        }

        // 登录接口单独由登录日志记录
        if ($module === 'login') {
            return $next($request);
        }

        $response = $next($request);
        $status = 1;
        $msg    = '';
        $result = '';

        if ($response instanceof \think\Response) {
            $content = $response->getContent();
            $data = json_decode((string) $content, true);
            if (is_array($data) && isset($data['code'])) {
                $status = (int) $data['code'] === 1 ? 1 : 0;
                $msg    = (string) ($data['msg'] ?? '');
            }
            $result = mb_substr(strip_tags((string) $content), 0, 500);
        }

        LogService::write(
            $this->modules[$module],
            $this->describeAction($seg),
            $request->method(),
            $request->url(),
            $request->ip(),
            $request->param(),
            $status,
            $msg,
            $result
        );

        return $response;
    }

    /**
     * 根据请求路径生成可读的操作动作
     */
    protected function describeAction(array $seg): string
    {
        $actionNames = [
            'save'       => '保存',
            'delete'     => '删除',
            'reset'      => '重置密码',
            'assign'     => '分配权限',
            'batch'      => '批量保存',
            'clear'      => '清空',
            'export'     => '导出',
            'password'   => '修改密码',
        ];

        return $actionNames[$seg[1] ?? ''] ?? '操作';
    }
}
