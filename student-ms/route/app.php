<?php
// +----------------------------------------------------------------------
// | 应用路由定义
// +----------------------------------------------------------------------

use app\controller\AdminController;
use app\controller\AuthController;
use app\controller\ClassController;
use app\controller\CourseController;
use app\controller\DepartmentController;
use app\controller\IndexController;
use app\controller\LoginController;
use app\controller\LoginLogController;
use app\controller\OperationLogController;
use app\controller\RoleController;
use app\controller\ScoreController;
use app\controller\SelectController;
use app\controller\StudentController;
use app\controller\TeacherController;
use think\facade\Route;

// ---------------------------------------------------------------------------
// 登录与框架入口
// ---------------------------------------------------------------------------
Route::get('/', 'index/main')->name('main');
Route::get('login', 'login/index')->name('login_page');
Route::post('login/do', 'login/doLogin')->name('login_submit');
Route::get('logout', 'login/logout')->name('logout');
Route::get('login/menus', 'login/menus')->name('login_menus');

// ---------------------------------------------------------------------------
// 控制台与个人中心
// ---------------------------------------------------------------------------
Route::get('dashboard', 'index/index')->name('dashboard');
Route::get('dashboard/data', 'index/dashboard')->name('dashboard_data');
Route::get('profile', 'index/profile')->name('profile');
Route::post('profile/save', 'index/saveProfile')->name('profile_save');
Route::post('profile/password', 'index/changePassword')->name('profile_password');

// ---------------------------------------------------------------------------
// 账号管理
// ---------------------------------------------------------------------------
Route::get('admin', 'admin/index')->name('admin_page');
Route::get('admin/list', 'admin/lists')->name('admin_list');
Route::post('admin/save', 'admin/save')->name('admin_save');
Route::post('admin/delete', 'admin/delete')->name('admin_delete');
Route::post('admin/reset', 'admin/resetPassword')->name('admin_reset');

// ---------------------------------------------------------------------------
// 角色与权限
// ---------------------------------------------------------------------------
Route::get('role', 'role/index')->name('role_page');
Route::get('role/list', 'role/lists')->name('role_list');
Route::get('role/all', 'role/all')->name('role_all');
Route::get('role/auths', 'role/authIds')->name('role_auths');
Route::post('role/save', 'role/save')->name('role_save');
Route::post('role/assign', 'role/assignAuth')->name('role_assign');
Route::post('role/delete', 'role/delete')->name('role_delete');

Route::get('auth', 'auth/index')->name('auth_page');
Route::get('auth/list', 'auth/lists')->name('auth_list');
Route::post('auth/save', 'auth/save')->name('auth_save');
Route::post('auth/delete', 'auth/delete')->name('auth_delete');

// ---------------------------------------------------------------------------
// 学生与班级
// ---------------------------------------------------------------------------
Route::get('student', 'student/index')->name('student_page');
Route::get('student/list', 'student/lists')->name('student_list');
Route::get('student/options', 'student/options')->name('student_options');
Route::get('student/detail', 'student/detail')->name('student_detail');
Route::get('student/export', 'student/export')->name('student_export');
Route::post('student/save', 'student/save')->name('student_save');
Route::post('student/delete', 'student/delete')->name('student_delete');

Route::get('class', 'class/index')->name('class_page');
Route::get('class/list', 'class/lists')->name('class_list');
Route::post('class/save', 'class/save')->name('class_save');
Route::post('class/delete', 'class/delete')->name('class_delete');

Route::get('department', 'department/index')->name('department_page');
Route::get('department/list', 'department/lists')->name('department_list');
Route::post('department/save', 'department/save')->name('department_save');
Route::post('department/delete', 'department/delete')->name('department_delete');

Route::get('teacher', 'teacher/index')->name('teacher_page');
Route::get('teacher/list', 'teacher/lists')->name('teacher_list');
Route::post('teacher/save', 'teacher/save')->name('teacher_save');
Route::post('teacher/delete', 'teacher/delete')->name('teacher_delete');

// ---------------------------------------------------------------------------
// 课程、选课与成绩
// ---------------------------------------------------------------------------
Route::get('course', 'course/index')->name('course_page');
Route::get('course/list', 'course/lists')->name('course_list');
Route::post('course/save', 'course/save')->name('course_save');
Route::post('course/delete', 'course/delete')->name('course_delete');

Route::get('select', 'select/index')->name('select_page');
Route::get('select/list', 'select/lists')->name('select_list');
Route::post('select/save', 'select/save')->name('select_save');
Route::post('select/delete', 'select/delete')->name('select_delete');

Route::get('score', 'score/index')->name('score_page');
Route::get('score/list', 'score/lists')->name('score_list');
Route::get('score/statistics', 'score/statistics')->name('score_statistics');
Route::post('score/save', 'score/save')->name('score_save');
Route::post('score/batch', 'score/batchSave')->name('score_batch');

// ---------------------------------------------------------------------------
// 日志
// ---------------------------------------------------------------------------
Route::get('loginlog', 'loginLog/index')->name('loginlog_page');
Route::get('loginlog/list', 'loginLog/lists')->name('loginlog_list');
Route::post('loginlog/clear', 'loginLog/clear')->name('loginlog_clear');

Route::get('operationlog', 'operationLog/index')->name('operationlog_page');
Route::get('operationlog/list', 'operationLog/lists')->name('operationlog_list');
Route::post('operationlog/clear', 'operationLog/clear')->name('operationlog_clear');
