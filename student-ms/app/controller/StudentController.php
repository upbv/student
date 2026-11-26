<?php

namespace app\controller;

use app\model\SchoolClass;
use app\model\Student;
use think\Request;

/**
 * 学生管理控制器
 */
class StudentController
{
    /**
     * 学生列表页
     */
    public function index()
    {
        return view('student/index', [
            'title'    => '学生管理系统 - 学生管理',
            'classes'  => SchoolClass::order('id', 'asc')->select(),
        ]);
    }

    /**
     * 获取学生列表
     */
    public function lists(Request $request)
    {
        $page    = max(1, (int) $request->param('page', 1));
        $limit   = min(100, max(1, (int) $request->param('limit', 15)));
        $keyword = trim((string) $request->param('keyword', ''));
        $classId = (int) $request->param('class_id', 0);
        $status  = $request->param('status', '');
        $gender  = $request->param('gender', '');

        $query = Student::withTrashed()->order('id', 'asc');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('student_no', "%{$keyword}%")
                  ->whereOr('name', 'like', "%{$keyword}%")
                  ->whereOr('phone', 'like', "%{$keyword}%");
            });
        }
        if ($classId > 0) {
            $query->where('class_id', $classId);
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        if ($gender !== '' && $gender !== null) {
            $query->where('gender', (int) $gender);
        }

        $total = (clone $query)->count();
        $list  = $query->page($page, $limit)->select();

        $classMap = SchoolClass::column('name', 'id');
        $data = [];
        foreach ($list as $item) {
            $row = $item->toArray();
            $row['gender_text'] = $item->gender_text;
            $row['status_text'] = $item->status_text;
            $row['class_name']  = $classMap[$item->class_id] ?? '未分班';
            $data[] = $row;
        }

        return json(['code' => 0, 'count' => $total, 'data' => $data]);
    }

    /**
     * 按关键字查询学生，供选课等场景下拉选择
     */
    public function options(Request $request)
    {
        $keyword = trim((string) $request->param('keyword', ''));

        $query = Student::order('student_no', 'asc');
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('student_no', "%{$keyword}%")
                  ->whereOr('name', 'like', "%{$keyword}%");
            });
        }

        $list = $query->limit(50)->select();
        $data = [];
        foreach ($list as $item) {
            $data[] = [
                'id'         => $item->id,
                'student_no' => $item->student_no,
                'name'       => $item->name,
                'label'      => $item->student_no . ' ' . $item->name,
            ];
        }

        return json(['code' => 1, 'data' => $data]);
    }

    /**
     * 获取学生详情（含选课与成绩）
     */
    public function detail(Request $request)
    {
        $id      = (int) $request->param('id', 0);
        $student = Student::withTrashed()->find($id);
        if (!$student) {
            return json(['code' => 0, 'msg' => '学生不存在']);
        }

        $row = $student->toArray();
        $row['gender_text'] = $student->gender_text;
        $row['status_text'] = $student->status_text;
        $row['class_name']  = SchoolClass::where('id', $student->class_id)->value('name') ?: '未分班';

        $row['courses'] = \app\model\SelectCourse::alias('sc')
            ->join('ms_course c', 'c.id = sc.course_id')
            ->leftJoin('ms_teacher t', 't.id = c.teacher_id')
            ->where('sc.student_id', $id)
            ->field('sc.id, sc.score, sc.semester, c.name AS course_name, c.credit, t.name AS teacher_name')
            ->order('sc.id', 'asc')
            ->select();

        return json(['code' => 1, 'data' => $row]);
    }

    /**
     * 新增/编辑学生
     */
    public function save(Request $request)
    {
        $id        = (int) $request->post('id', 0);
        $studentNo = trim((string) $request->post('student_no', ''));
        $name      = trim((string) $request->post('name', ''));
        $gender    = (int) $request->post('gender', 0);
        $birthday  = trim((string) $request->post('birthday', ''));
        $phone     = trim((string) $request->post('phone', ''));
        $email     = trim((string) $request->post('email', ''));
        $address   = trim((string) $request->post('address', ''));
        $classId   = (int) $request->post('class_id', 0);
        $enroll    = trim((string) $request->post('enroll_date', ''));
        $status    = (int) $request->post('status', 1);
        $remark    = trim((string) $request->post('remark', ''));

        if ($studentNo === '' || $name === '') {
            return json(['code' => 0, 'msg' => '学号和姓名不能为空']);
        }
        if (!preg_match('/^[0-9A-Za-z]{4,20}$/', $studentNo)) {
            return json(['code' => 0, 'msg' => '学号格式不正确']);
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json(['code' => 0, 'msg' => '邮箱格式不正确']);
        }
        if ($classId < 1) {
            return json(['code' => 0, 'msg' => '请选择所属班级']);
        }

        $existQuery = Student::withTrashed()->where('student_no', $studentNo);
        if ($id > 0) {
            $existQuery->where('id', '<>', $id);
        }
        if ($existQuery->find()) {
            return json(['code' => 0, 'msg' => '学号已存在']);
        }

        $data = [
            'student_no'  => $studentNo,
            'name'        => $name,
            'gender'      => $gender,
            'birthday'    => $birthday ?: null,
            'phone'       => $phone,
            'email'       => $email,
            'address'     => $address,
            'class_id'    => $classId,
            'enroll_date' => $enroll ?: null,
            'status'      => $status,
            'remark'      => $remark,
        ];

        if ($id > 0) {
            $student = Student::find($id);
            if (!$student) {
                return json(['code' => 0, 'msg' => '学生不存在']);
            }
            $student->save($data);
            $action = '编辑学生';
        } else {
            Student::create($data);
            $action = '新增学生';
            $this->syncClassNum($classId);
        }

        if ($id > 0) {
            $this->syncClassNum($classId);
        }


        return json(['code' => 1, 'msg' => $action . '成功']);
    }

    /**
     * 同步班级人数
     */
    protected function syncClassNum(int $classId): void
    {
        if ($classId < 1) {
            return;
        }
        $num = Student::where('class_id', $classId)->count();
        SchoolClass::where('id', $classId)->update(['student_num' => $num]);
    }

    /**
     * 删除学生
     */
    public function delete(Request $request)
    {
        $ids = $request->post('ids', []);
        if (!is_array($ids)) {
            $ids = explode(',', (string) $ids);
        }
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return json(['code' => 0, 'msg' => '请选择要删除的学生']);
        }

        $classIds = Student::whereIn('id', $ids)->column('class_id');
        Student::destroy($ids);
        foreach (array_unique($classIds) as $classId) {
            $this->syncClassNum((int) $classId);
        }


        return json(['code' => 1, 'msg' => '删除成功']);
    }

    /**
     * 导出学生名单（CSV）
     */
    public function export(Request $request)
    {
        $keyword = trim((string) $request->param('keyword', ''));
        $classId = (int) $request->param('class_id', 0);

        $query = Student::order('student_no', 'asc');
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('student_no', "%{$keyword}%")
                  ->whereOr('name', 'like', "%{$keyword}%");
            });
        }
        if ($classId > 0) {
            $query->where('class_id', $classId);
        }

        $list  = $query->select();
        $classMap = SchoolClass::column('name', 'id');

        $filename = 'student_' . date('YmdHis') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['学号', '姓名', '性别', '班级', '联系电话', '邮箱', '入学日期', '学籍状态']);

        foreach ($list as $item) {
            fputcsv($output, [
                $item->student_no,
                $item->name,
                $item->gender_text,
                $classMap[$item->class_id] ?? '未分班',
                $item->phone,
                $item->email,
                $item->enroll_date ?: '',
                $item->status_text,
            ]);
        }
        fclose($output);


        exit;
    }
}
