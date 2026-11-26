<?php

namespace app\controller;

use app\model\Course;
use app\model\SelectCourse;
use app\model\Student;
use app\service\LogService;
use think\Request;

/**
 * 选课管理控制器
 */
class SelectController
{
    /**
     * 选课管理页
     */
    public function index()
    {
        return view('select/index', [
            'title'   => '学生管理系统 - 选课管理',
            'courses' => Course::where('status', 1)->order('id', 'asc')->select(),
        ]);
    }

    /**
     * 获取选课记录
     */
    public function lists(Request $request)
    {
        $page     = max(1, (int) $request->param('page', 1));
        $limit    = min(100, max(1, (int) $request->param('limit', 15)));
        $keyword  = trim((string) $request->param('keyword', ''));
        $courseId = (int) $request->param('course_id', 0);
        $semester = trim((string) $request->param('semester', ''));

        $query = SelectCourse::alias('sc')
            ->join('ms_student s', 's.id = sc.student_id')
            ->join('ms_course c', 'c.id = sc.course_id')
            ->field('sc.id, sc.student_id, sc.course_id, sc.semester, sc.score, sc.status,
                     s.student_no, s.name AS student_name, c.name AS course_name, c.credit')
            ->order('sc.id', 'asc');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('s.student_no', "%{$keyword}%")
                  ->whereOr('s.name', 'like', "%{$keyword}%")
                  ->whereOr('c.name', 'like', "%{$keyword}%");
            });
        }
        if ($courseId > 0) {
            $query->where('sc.course_id', $courseId);
        }
        if ($semester !== '') {
            $query->where('sc.semester', $semester);
        }

        $total = (clone $query)->count();
        $list  = $query->page($page, $limit)->select();

        $data = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['level'] = $row['score'] === null ? '未录入' : $this->level((float) $row['score']);
            $data[] = $row;
        }

        return json(['code' => 0, 'count' => $total, 'data' => $data]);
    }

    /**
     * 成绩等级换算
     */
    protected function level(float $score): string
    {
        if ($score >= 90) return '优秀';
        if ($score >= 80) return '良好';
        if ($score >= 70) return '中等';
        if ($score >= 60) return '及格';
        return '不及格';
    }

    /**
     * 新增选课
     */
    public function save(Request $request)
    {
        $studentId = (int) $request->post('student_id', 0);
        $courseId  = (int) $request->post('course_id', 0);

        if ($studentId < 1 || $courseId < 1) {
            return json(['code' => 0, 'msg' => '请选择学生和课程']);
        }

        $student = Student::find($studentId);
        $course  = Course::find($courseId);
        if (!$student || !$course) {
            return json(['code' => 0, 'msg' => '学生或课程不存在']);
        }

        $exist = SelectCourse::where('student_id', $studentId)->where('course_id', $courseId)->find();
        if ($exist) {
            if ($exist->status == 1) {
                return json(['code' => 0, 'msg' => '该学生已选此课程，不能重复选课']);
            }
            // 已退选的记录恢复为已选
            $exist->save(['status' => 1, 'semester' => $course->semester]);
        } else {
            SelectCourse::create([
                'student_id' => $studentId,
                'course_id'  => $courseId,
                'semester'   => $course->semester,
                'status'     => 1,
            ]);
        }

        LogService::writeFromRequest($request, '选课管理', '新增选课');

        return json(['code' => 1, 'msg' => '选课成功']);
    }

    /**
     * 退课
     */
    public function delete(Request $request)
    {
        $id = (int) $request->post('id', 0);

        $record = SelectCourse::find($id);
        if (!$record) {
            return json(['code' => 0, 'msg' => '选课记录不存在']);
        }

        $record->save(['status' => 0]);
        LogService::writeFromRequest($request, '选课管理', '学生退课');

        return json(['code' => 1, 'msg' => '退课成功']);
    }
}
