<?php

namespace app\controller;

use app\model\Aid;
use app\model\Leave;
use app\model\SelectCourse;
use app\service\AuthService;
use think\Request;

/**
 * 学生自助服务控制器：我的成绩、我的请假、我的资助
 */
class MyController
{
    /**
     * 当前账号绑定的学生ID
     */
    protected function studentId(): int
    {
        return AuthService::studentId();
    }

    protected function emptyList()
    {
        return json(['code' => 0, 'count' => 0, 'data' => []]);
    }

    /* ==================== 我的成绩 ==================== */

    public function grade()
    {
        return view('my/grade', [
            'title' => '学生管理系统 - 我的成绩',
        ]);
    }

    public function gradeList()
    {
        $studentId = $this->studentId();
        if ($studentId < 1) {
            return json(['code' => 1, 'data' => ['list' => [], 'stats' => [
                'courseCount' => 0, 'creditTotal' => 0, 'avgScore' => '-',
            ]]]);
        }

        $list = SelectCourse::alias('sc')
            ->join('ms_course c', 'c.id = sc.course_id')
            ->leftJoin('ms_teacher t', 't.id = c.teacher_id')
            ->where('sc.student_id', $studentId)
            ->field('sc.id, sc.semester, sc.score, sc.status, c.name AS course_name,
                     c.credit, c.course_type, t.name AS teacher_name')
            ->order('sc.id', 'asc')
            ->select();

        $data    = [];
        $credits = 0.0;
        $sum     = 0.0;
        $scored  = 0;
        $selected = 0;

        foreach ($list as $item) {
            $row = $item->toArray();
            $row['level']       = $row['score'] === null ? '未录入' : $this->level((float) $row['score']);
            $row['status_text'] = $item->status == 1 ? '已选' : '已退';

            if ($item->status == 1) {
                $selected++;
                $credits += (float) $row['credit'];
                if ($row['score'] !== null) {
                    $sum += (float) $row['score'];
                    $scored++;
                }
            }
            $data[] = $row;
        }

        return json(['code' => 1, 'data' => [
            'list'  => $data,
            'stats' => [
                'courseCount' => $selected,
                'creditTotal' => round($credits, 1),
                'avgScore'    => $scored ? round($sum / $scored, 1) : '-',
            ],
        ]]);
    }

    protected function level(float $score): string
    {
        if ($score >= 90) return '优秀';
        if ($score >= 80) return '良好';
        if ($score >= 70) return '中等';
        if ($score >= 60) return '及格';
        return '不及格';
    }

    /* ==================== 我的请假 ==================== */

    public function leave()
    {
        return view('my/leave', [
            'title' => '学生管理系统 - 我的请假',
        ]);
    }

    public function leaveList()
    {
        $studentId = $this->studentId();
        if ($studentId < 1) {
            return $this->emptyList();
        }

        $list = Leave::where('student_id', $studentId)
            ->order('id', 'desc')
            ->limit(50)
            ->select();

        $data = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['type_text']   = $item->type_text;
            $row['status_text'] = $item->status_text;
            $data[] = $row;
        }

        return json(['code' => 0, 'count' => count($data), 'data' => $data]);
    }

    public function leaveSave(Request $request)
    {
        $studentId = $this->studentId();
        if ($studentId < 1) {
            return json(['code' => 0, 'msg' => '当前账号未绑定学生信息，无法申请请假']);
        }

        $type   = (int) $request->post('type', 1);
        $start  = trim((string) $request->post('start_date', ''));
        $end    = trim((string) $request->post('end_date', ''));
        $reason = trim((string) $request->post('reason', ''));

        if (!in_array($type, [1, 2, 3], true)) {
            return json(['code' => 0, 'msg' => '请选择请假类型']);
        }

        $startTs = strtotime($start);
        $endTs   = strtotime($end);
        if (!$startTs || !$endTs) {
            return json(['code' => 0, 'msg' => '请选择请假起止日期']);
        }
        if ($endTs < $startTs) {
            return json(['code' => 0, 'msg' => '结束日期不能早于开始日期']);
        }
        if ($reason === '') {
            return json(['code' => 0, 'msg' => '请填写请假事由']);
        }

        $days = round(($endTs - $startTs) / 86400 + 1, 1);

        Leave::create([
            'student_id' => $studentId,
            'type'       => $type,
            'start_date' => date('Y-m-d', $startTs),
            'end_date'   => date('Y-m-d', $endTs),
            'days'       => $days,
            'reason'     => $reason,
            'status'     => 0,
        ]);

        return json(['code' => 1, 'msg' => '请假申请已提交，等待辅导员审批']);
    }

    /* ==================== 我的资助 ==================== */

    public function aid()
    {
        return view('my/aid', [
            'title' => '学生管理系统 - 我的资助',
        ]);
    }

    public function aidList()
    {
        $studentId = $this->studentId();
        if ($studentId < 1) {
            return $this->emptyList();
        }

        $list = Aid::where('student_id', $studentId)
            ->order('id', 'desc')
            ->select();

        $data = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['type_text']   = $item->type_text;
            $row['status_text'] = $item->status_text;
            $data[] = $row;
        }

        return json(['code' => 0, 'count' => count($data), 'data' => $data]);
    }
}
