<?php

namespace app\controller;

use app\model\Course;
use app\model\SchoolClass;
use app\model\SelectCourse;
use app\model\Student;
use think\Request;

/**
 * 成绩管理控制器
 */
class ScoreController
{
    /**
     * 教师账号只能操作本人所授课程；管理员、教务与辅导员不受限
     */
    protected function scopedTeacherId(): int
    {
        if (!\app\service\AuthService::hasRole('teacher')) {
            return 0;
        }
        foreach (['admin', 'academic', 'counselor'] as $code) {
            if (\app\service\AuthService::hasRole($code)) {
                return 0;
            }
        }
        return \app\service\AuthService::teacherId();
    }

    /**
     * 成绩管理页
     */
    public function index()
    {
        $teacherId = $this->scopedTeacherId();
        $query = Course::order('id', 'asc');
        if ($teacherId > 0) {
            $query->where('teacher_id', $teacherId);
        }

        return view('score/index', [
            'title'   => '学生管理系统 - 成绩管理',
            'courses' => $query->select(),
        ]);
    }

    /**
     * 获取成绩列表
     */
    public function lists(Request $request)
    {
        $page     = max(1, (int) $request->param('page', 1));
        $limit    = min(100, max(1, (int) $request->param('limit', 15)));
        $keyword  = trim((string) $request->param('keyword', ''));
        $courseId = (int) $request->param('course_id', 0);
        $classId  = (int) $request->param('class_id', 0);
        $semester = trim((string) $request->param('semester', ''));

        $query = SelectCourse::alias('sc')
            ->join('ms_student s', 's.id = sc.student_id')
            ->join('ms_course c', 'c.id = sc.course_id')
            ->field('sc.id, sc.student_id, sc.course_id, sc.semester, sc.score,
                     s.student_no, s.name AS student_name, s.class_id, c.name AS course_name, c.credit')
            ->where('sc.status', 1)
            ->order('sc.id', 'asc');

        $teacherId = $this->scopedTeacherId();
        if ($teacherId > 0) {
            $query->where('c.teacher_id', $teacherId);
        }

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('s.student_no', "%{$keyword}%")
                  ->whereOr('s.name', 'like', "%{$keyword}%");
            });
        }
        if ($courseId > 0) {
            $query->where('sc.course_id', $courseId);
        }
        if ($classId > 0) {
            $query->where('s.class_id', $classId);
        }
        if ($semester !== '') {
            $query->where('sc.semester', $semester);
        }

        $total = (clone $query)->count();
        $list  = $query->page($page, $limit)->select();

        $classMap = SchoolClass::column('name', 'id');
        $data = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['class_name'] = $classMap[$item->class_id] ?? '未分班';
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
     * 录入成绩
     */
    public function save(Request $request)
    {
        $id    = (int) $request->post('id', 0);
        $score = trim((string) $request->post('score', ''));

        $record = SelectCourse::find($id);
        if (!$record) {
            return json(['code' => 0, 'msg' => '选课记录不存在']);
        }

        $teacherId = $this->scopedTeacherId();
        if ($teacherId > 0 && (int) Course::where('id', $record->course_id)->value('teacher_id') !== $teacherId) {
            return json(['code' => 0, 'msg' => '只能录入本人所授课程的成绩']);
        }

        if ($score === '') {
            $record->save(['score' => null]);
            $action = '清空成绩';
        } else {
            if (!is_numeric($score)) {
                return json(['code' => 0, 'msg' => '成绩必须是数字']);
            }
            $value = (float) $score;
            if ($value < 0 || $value > 100) {
                return json(['code' => 0, 'msg' => '成绩取值范围为 0 到 100']);
            }
            $record->save(['score' => $value]);
            $action = '录入成绩';
        }


        return json(['code' => 1, 'msg' => $action . '成功']);
    }

    /**
     * 批量录入某门课程的成绩
     *
     * scores 的键为学号，值为成绩；也兼容直接传选课记录 ID。
     */
    public function batchSave(Request $request)
    {
        $courseId = (int) $request->post('course_id', 0);
        $scores   = $request->post('scores', []);

        if ($courseId < 1) {
            return json(['code' => 0, 'msg' => '请选择课程']);
        }
        if (!is_array($scores) || empty($scores)) {
            return json(['code' => 0, 'msg' => '没有需要保存的成绩数据']);
        }

        $teacherId = $this->scopedTeacherId();
        if ($teacherId > 0 && (int) Course::where('id', $courseId)->value('teacher_id') !== $teacherId) {
            return json(['code' => 0, 'msg' => '只能录入本人所授课程的成绩']);
        }

        // 取出该课程下所有选课记录，按学号建立索引
        $records = SelectCourse::alias('sc')
            ->join('ms_student s', 's.id = sc.student_id')
            ->where('sc.course_id', $courseId)
            ->where('sc.status', 1)
            ->field('sc.id, s.student_no')
            ->select();

        $idMap = [];
        foreach ($records as $record) {
            $idMap[(string) $record->student_no] = (int) $record->id;
            $idMap['#' . $record->id]            = (int) $record->id;
        }

        $success = 0;
        $skipped = [];
        foreach ($scores as $key => $score) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }
            $recordId = $idMap[$key] ?? ($idMap['#' . $key] ?? 0);
            if ($recordId < 1) {
                $skipped[] = $key;
                continue;
            }

            $record = SelectCourse::find($recordId);
            if (!$record) {
                continue;
            }

            if ($score === '' || $score === null) {
                $record->save(['score' => null]);
                $success++;
                continue;
            }
            if (!is_numeric($score)) {
                $skipped[] = $key;
                continue;
            }
            $value = (float) $score;
            if ($value < 0 || $value > 100) {
                $skipped[] = $key;
                continue;
            }
            $record->save(['score' => $value]);
            $success++;
        }

        $msg = "成功保存 {$success} 条成绩";
        if ($skipped) {
            $msg .= '，' . count($skipped) . ' 条未匹配到选课记录';
        }


        return json(['code' => 1, 'msg' => $msg]);
    }

    /**
     * 成绩统计：按课程汇总平均分与及格率
     */
    public function statistics(Request $request)
    {
        $semester = trim((string) $request->param('semester', ''));

        $query = SelectCourse::alias('sc')
            ->join('ms_course c', 'c.id = sc.course_id')
            ->where('sc.status', 1)
            ->whereNotNull('sc.score')
            ->field('c.id, c.name AS course_name, c.credit,
                     COUNT(sc.id) AS total,
                     ROUND(AVG(sc.score),1) AS avg_score,
                     ROUND(SUM(CASE WHEN sc.score >= 60 THEN 1 ELSE 0 END) * 100 / COUNT(sc.id),1) AS pass_rate,
                     MAX(sc.score) AS max_score,
                     MIN(sc.score) AS min_score')
            ->group('c.id, c.name, c.credit')
            ->order('c.id', 'asc');

        $teacherId = $this->scopedTeacherId();
        if ($teacherId > 0) {
            $query->where('c.teacher_id', $teacherId);
        }

        if ($semester !== '') {
            $query->where('sc.semester', $semester);
        }

        $list = $query->select();

        return json(['code' => 1, 'data' => $list]);
    }
}
