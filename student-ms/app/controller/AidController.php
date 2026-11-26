<?php

namespace app\controller;

use app\model\Aid;
use app\model\Student;
use think\Request;

/**
 * 资助管理控制器（辅导员 / 管理员）
 */
class AidController
{
    /**
     * 资助管理页
     */
    public function index()
    {
        return view('aid/index', [
            'title' => '学生管理系统 - 资助管理',
        ]);
    }

    /**
     * 资助记录列表
     */
    public function lists(Request $request)
    {
        $page    = max(1, (int) $request->param('page', 1));
        $limit   = min(100, max(1, (int) $request->param('limit', 15)));
        $keyword = trim((string) $request->param('keyword', ''));
        $type    = (int) $request->param('type', 0);
        $status  = $request->param('status', '');

        $query = Aid::alias('a')
            ->join('ms_student s', 's.id = a.student_id')
            ->leftJoin('ms_class c', 'c.id = s.class_id')
            ->field('a.*, s.student_no, s.name AS student_name, c.name AS class_name')
            ->order('a.id', 'desc');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('s.student_no', "%{$keyword}%")
                  ->whereOr('s.name', 'like', "%{$keyword}%");
            });
        }
        if ($type > 0) {
            $query->where('a.type', $type);
        }
        if ($status !== '' && $status !== null) {
            $query->where('a.status', (int) $status);
        }

        $total = (clone $query)->count();
        $list  = $query->page($page, $limit)->select();

        $data = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['type_text']   = $item->type_text;
            $row['status_text'] = $item->status_text;
            $data[] = $row;
        }

        return json(['code' => 0, 'count' => $total, 'data' => $data]);
    }

    /**
     * 新增 / 编辑资助记录
     */
    public function save(Request $request)
    {
        $id        = (int) $request->post('id', 0);
        $studentId = (int) $request->post('student_id', 0);
        $type      = (int) $request->post('type', 1);
        $semester  = trim((string) $request->post('semester', ''));
        $amount    = (float) $request->post('amount', 0);
        $status    = (int) $request->post('status', 0);
        $remark    = trim((string) $request->post('remark', ''));

        if ($studentId < 1 || !Student::find($studentId)) {
            return json(['code' => 0, 'msg' => '请选择学生']);
        }
        if ($semester === '') {
            return json(['code' => 0, 'msg' => '请填写学期']);
        }
        if ($amount < 0) {
            return json(['code' => 0, 'msg' => '金额不能为负数']);
        }

        $data = [
            'student_id' => $studentId,
            'type'       => $type,
            'semester'   => $semester,
            'amount'     => $amount,
            'status'     => $status,
            'remark'     => $remark,
        ];

        if ($id > 0) {
            $aid = Aid::find($id);
            if (!$aid) {
                return json(['code' => 0, 'msg' => '资助记录不存在']);
            }
            $aid->save($data);
            $action = '编辑资助';
        } else {
            Aid::create($data);
            $action = '新增资助';
        }

        return json(['code' => 1, 'msg' => $action . '成功']);
    }

    /**
     * 删除资助记录
     */
    public function delete(Request $request)
    {
        $id = (int) $request->post('id', 0);

        if (!Aid::find($id)) {
            return json(['code' => 0, 'msg' => '资助记录不存在']);
        }

        Aid::destroy($id);

        return json(['code' => 1, 'msg' => '删除成功']);
    }
}
