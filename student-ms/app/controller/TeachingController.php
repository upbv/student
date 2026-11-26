<?php

namespace app\controller;

use app\model\Course;
use app\model\SchoolClass;
use app\model\SelectCourse;
use app\model\Student;
use app\model\Teacher;
use app\service\AuthService;
use think\Request;

/**
 * 教师工作台：我的授课、我的学生
 */
class TeachingController
{
    protected function teacherId(): int
    {
        return AuthService::teacherId();
    }

    /* ==================== 我的授课 ==================== */

    public function courses()
    {
        return view('teaching/courses', [
            'title' => '学生管理系统 - 我的授课',
        ]);
    }

    public function courseList(Request $request)
    {
        $teacherId = $this->teacherId();
        if ($teacherId < 1) {
            return json(['code' => 0, 'count' => 0, 'data' => []]);
        }

        $page    = max(1, (int) $request->param('page', 1));
        $limit   = min(100, max(1, (int) $request->param('limit', 15)));
        $keyword = trim((string) $request->param('keyword', ''));

        $query = Course::where('teacher_id', $teacherId)->order('id', 'asc');
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('course_no', "%{$keyword}%")
                  ->whereOr('name', 'like', "%{$keyword}%");
            });
        }

        $total = (clone $query)->count();
        $list  = $query->page($page, $limit)->select();

        $data = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['course_type_text'] = $item->course_type_text;

            $recordQuery = SelectCourse::where('course_id', $item->id)->where('status', 1);
            $row['student_count'] = (clone $recordQuery)->count();
            $row['ungraded']      = (clone $recordQuery)->whereNull('score')->count();
            $row['avg_score']     = (clone $recordQuery)->whereNotNull('score')->avg('score');

            if ($row['avg_score'] !== null) {
                $row['avg_score'] = round((float) $row['avg_score'], 1);
            } else {
                $row['avg_score'] = '-';
            }

            $data[] = $row;
        }

        return json(['code' => 0, 'count' => $total, 'data' => $data]);
    }

    /* ==================== 我的学生 ==================== */

    public function students()
    {
        return view('teaching/students', [
            'title' => '学生管理系统 - 我的学生',
        ]);
    }

    public function studentList(Request $request)
    {
        $teacherId = $this->teacherId();
        if ($teacherId < 1) {
            return json(['code' => 0, 'count' => 0, 'data' => []]);
        }

        $page    = max(1, (int) $request->param('page', 1));
        $limit   = min(100, max(1, (int) $request->param('limit', 15)));
        $keyword = trim((string) $request->param('keyword', ''));

        // 选了该教师课程的学生
        $idsFromCourse = SelectCourse::alias('sc')
            ->join('ms_course c', 'c.id = sc.course_id')
            ->where('c.teacher_id', $teacherId)
            ->where('sc.status', 1)
            ->column('sc.student_id');

        // 该教师担任班主任的班级学生
        $teacher = Teacher::find($teacherId);
        $idsFromClass = [];
        if ($teacher && $teacher->name !== '') {
            $classIds = SchoolClass::where('head_teacher', $teacher->name)->column('id');
            if ($classIds) {
                $idsFromClass = Student::whereIn('class_id', $classIds)->column('id');
            }
        }

        $ids = array_values(array_unique(array_merge($idsFromCourse, $idsFromClass)));
        if (empty($ids)) {
            return json(['code' => 0, 'count' => 0, 'data' => []]);
        }

        $query = Student::whereIn('id', $ids)->order('student_no', 'asc');
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('student_no', "%{$keyword}%")
                  ->whereOr('name', 'like', "%{$keyword}%");
            });
        }

        $total = (clone $query)->count();
        $list  = $query->page($page, $limit)->select();

        $classMap = SchoolClass::column('name', 'id');

        // 每个学生在当前教师课程中的选课数
        $courseCountMap = [];
        $rows = SelectCourse::alias('sc')
            ->join('ms_course c', 'c.id = sc.course_id')
            ->where('c.teacher_id', $teacherId)
            ->where('sc.status', 1)
            ->whereIn('sc.student_id', $ids)
            ->field('sc.student_id, COUNT(*) AS num')
            ->group('sc.student_id')
            ->select();
        foreach ($rows as $r) {
            $courseCountMap[(int) $r->student_id] = (int) $r->num;
        }

        $data = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['gender_text']  = $item->gender_text;
            $row['class_name']   = $classMap[$item->class_id] ?? '未分班';
            $row['course_count'] = $courseCountMap[(int) $item->id] ?? 0;
            $data[] = $row;
        }

        return json(['code' => 0, 'count' => $total, 'data' => $data]);
    }
}
