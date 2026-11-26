<?php

namespace app\controller;

use app\model\OperationLog;
use think\Request;

/**
 * 操作日志控制器
 */
class OperationLogController
{
    /**
     * 操作日志页
     */
    public function index()
    {
        return view('operationlog/index', [
            'title' => '学生管理系统 - 操作日志',
        ]);
    }

    /**
     * 获取操作日志列表
     */
    public function lists(Request $request)
    {
        $page    = max(1, (int) $request->param('page', 1));
        $limit   = min(100, max(1, (int) $request->param('limit', 15)));
        $keyword = trim((string) $request->param('keyword', ''));
        $module  = trim((string) $request->param('module', ''));
        $status  = $request->param('status', '');
        $start   = trim((string) $request->param('start_date', ''));
        $end     = trim((string) $request->param('end_date', ''));

        $query = OperationLog::order('id', 'desc');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('username', "%{$keyword}%")
                  ->whereOr('action', 'like', "%{$keyword}%")
                  ->whereOr('ip', 'like', "%{$keyword}%");
            });
        }
        if ($module !== '') {
            $query->where('module', $module);
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        if ($start !== '') {
            $query->where('operate_time', '>=', strtotime($start . ' 00:00:00'));
        }
        if ($end !== '') {
            $query->where('operate_time', '<=', strtotime($end . ' 23:59:59'));
        }

        $total = (clone $query)->count();
        $list  = $query->page($page, $limit)->select();

        $data = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['status_text']  = $item->status_text;
            $row['operate_time'] = date('Y-m-d H:i:s', $item->operate_time);
            $data[] = $row;
        }

        return json(['code' => 0, 'count' => $total, 'data' => $data]);
    }

    /**
     * 清空操作日志
     */
    public function clear(Request $request)
    {
        $num = OperationLog::where('id', '>', 0)->count();
        OperationLog::where('id', '>', 0)->delete();

        \app\service\LogService::writeFromRequest($request, '操作日志', '清空日志', 1, '', "清空 {$num} 条操作日志");

        return json(['code' => 1, 'msg' => '已清空 ' . $num . ' 条操作日志']);
    }
}
