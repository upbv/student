<?php

namespace app\controller;

use app\model\Leave;
use app\model\Student;
use app\service\AuthService;
use think\Request;

/**
 * 请假管理控制器（辅导员 / 管理员）
 */
class LeaveController
{
    /**
     * 请假管理页
     */
    public function index()
    {
        return view('leave/index', [
            'title' => '学生管理系统 - 请假管理',
        ]);
    }

    /**
     * 请假记录列表
     */
    public function lists(Request $request)
    {
        $page    = max(1, (int) $request->param('page', 1));
        $limit   = min(100, max(1, (int) $request->param('limit', 15)));
        $keyword = trim((string) $request->param('keyword', ''));
        $status  = $request->param('status', '');
        $type    = (int) $request->param('type', 0);

        $query = Leave::alias('l')
            ->join('ms_student s', 's.id = l.student_id')
            ->leftJoin('ms_class c', 'c.id = s.class_id')
            ->field('l.*, s.student_no, s.name AS student_name, c.name AS class_name')
            ->order('l.id', 'desc');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('s.student_no', "%{$keyword}%")
                  ->whereOr('s.name', 'like', "%{$keyword}%");
            });
        }
        if ($status !== '' && $status !== null) {
            $query->where('l.status', (int) $status);
        }
        if ($type > 0) {
            $query->where('l.type', $type);
        }

        $total = (clone $query)->count();
        $list  = $query->page($page, $limit)->select();

        $data = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['type_text']   = $item->type_text;
            $row['status_text'] = $item->status_text;
            $row['approve_time_text'] = $item->approve_time ? date('Y-m-d H:i', (int) $item->approve_time) : '';
            $data[] = $row;
        }

        return json(['code' => 0, 'count' => $total, 'data' => $data]);
    }

    /**
     * 审批请假
     */
    public function approve(Request $request)
    {
        $id     = (int) $request->post('id', 0);
        $status = (int) $request->post('status', 0);
        $remark = trim((string) $request->post('remark', ''));

        if (!in_array($status, [1, 2], true)) {
            return json(['code' => 0, 'msg' => '请选择审批结果']);
        }

        $leave = Leave::find($id);
        if (!$leave) {
            return json(['code' => 0, 'msg' => '请假记录不存在']);
        }

        $leave->save([
            'status'       => $status,
            'approver'     => (string) \think\facade\Session::get('nickname', ''),
            'approve_time' => time(),
            'remark'       => $remark,
        ]);

        return json(['code' => 1, 'msg' => $status === 1 ? '已批准该请假申请' : '已驳回该请假申请']);
    }

    /**
     * 删除请假记录
     */
    public function delete(Request $request)
    {
        $id = (int) $request->post('id', 0);

        $leave = Leave::find($id);
        if (!$leave) {
            return json(['code' => 0, 'msg' => '请假记录不存在']);
        }

        Leave::destroy($id);

        return json(['code' => 1, 'msg' => '删除成功']);
    }
}
