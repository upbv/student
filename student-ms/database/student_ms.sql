-- ----------------------------------------------------------------------------
-- 基于 ThinkPHP6 + Layui 的学生管理系统 数据库结构
-- 数据库: student_ms        字符集: utf8mb4        引擎: InnoDB
-- ----------------------------------------------------------------------------

DROP DATABASE IF EXISTS `student_ms`;
CREATE DATABASE `student_ms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `student_ms`;

-- ----------------------------------------------------------------------------
-- 系统管理
-- ----------------------------------------------------------------------------

-- 管理员/教师/学生统一账号表
DROP TABLE IF EXISTS `ms_admin`;
CREATE TABLE `ms_admin` (
  `id`            int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `username`      varchar(30)  NOT NULL                COMMENT '登录账号',
  `password`      char(32)     NOT NULL                COMMENT '密码(md5加盐)',
  `salt`          varchar(32) NOT NULL DEFAULT ''     COMMENT '密码盐值',
  `nickname`      varchar(30)  NOT NULL DEFAULT ''     COMMENT '姓名',
  `avatar`        varchar(120) NOT NULL DEFAULT ''     COMMENT '头像地址',
  `email`         varchar(60)  NOT NULL DEFAULT ''     COMMENT '邮箱',
  `phone`         varchar(20)  NOT NULL DEFAULT ''     COMMENT '联系电话',
  `sex`           tinyint(1)   NOT NULL DEFAULT 0      COMMENT '性别 0未知 1男 2女',
  `status`        tinyint(1)   NOT NULL DEFAULT 1      COMMENT '状态 1正常 0停用',
  `login_fail`    int(6)       NOT NULL DEFAULT 0      COMMENT '连续登录失败次数',
  `last_login`    int(11)      DEFAULT NULL            COMMENT '最后登录时间',
  `last_login_ip` varchar(46)  NOT NULL DEFAULT ''     COMMENT '最后登录IP',
  `create_time`   int(11)      NOT NULL DEFAULT 0      COMMENT '创建时间',
  `update_time`   int(11)      NOT NULL DEFAULT 0      COMMENT '更新时间',
  `delete_time`   int(11)      DEFAULT NULL            COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统账号表';

-- 角色表
DROP TABLE IF EXISTS `ms_role`;
CREATE TABLE `ms_role` (
  `id`          int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name`        varchar(30)  NOT NULL                COMMENT '角色名称',
  `code`        varchar(30)  NOT NULL                COMMENT '角色标识',
  `remark`      varchar(200) NOT NULL DEFAULT ''     COMMENT '备注',
  `status`      tinyint(1)   NOT NULL DEFAULT 1      COMMENT '状态 1启用 0禁用',
  `sort`        int(6)       NOT NULL DEFAULT 0      COMMENT '排序',
  `create_time` int(11)      NOT NULL DEFAULT 0      COMMENT '创建时间',
  `update_time` int(11)      NOT NULL DEFAULT 0      COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色表';

-- 权限(菜单)表
DROP TABLE IF EXISTS `ms_auth`;
CREATE TABLE `ms_auth` (
  `id`          int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid`         int(10) unsigned NOT NULL DEFAULT 0  COMMENT '上级权限ID',
  `name`        varchar(50)  NOT NULL                COMMENT '权限名称',
  `type`        tinyint(1)   NOT NULL DEFAULT 1      COMMENT '类型 1菜单 2按钮',
  `path`        varchar(120) NOT NULL DEFAULT ''     COMMENT '路由地址',
  `icon`        varchar(50)  NOT NULL DEFAULT ''     COMMENT '菜单图标',
  `sort`        int(6)       NOT NULL DEFAULT 0      COMMENT '排序',
  `status`      tinyint(1)   NOT NULL DEFAULT 1      COMMENT '状态 1启用 0禁用',
  `create_time` int(11)      NOT NULL DEFAULT 0      COMMENT '创建时间',
  `update_time` int(11)      NOT NULL DEFAULT 0      COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限菜单表';

-- 账号角色关联表
DROP TABLE IF EXISTS `ms_admin_role`;
CREATE TABLE `ms_admin_role` (
  `id`      int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `admin_id` int(10) unsigned NOT NULL              COMMENT '账号ID',
  `role_id` int(10) unsigned NOT NULL                COMMENT '角色ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_admin_role` (`admin_id`,`role_id`),
  KEY `idx_role` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='账号角色关联表';

-- 角色权限关联表
DROP TABLE IF EXISTS `ms_role_auth`;
CREATE TABLE `ms_role_auth` (
  `id`      int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `role_id` int(10) unsigned NOT NULL                COMMENT '角色ID',
  `auth_id` int(10) unsigned NOT NULL                COMMENT '权限ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_auth` (`role_id`,`auth_id`),
  KEY `idx_auth` (`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色权限关联表';

-- ----------------------------------------------------------------------------
-- 教务管理
-- ----------------------------------------------------------------------------

-- 院系/专业表
DROP TABLE IF EXISTS `ms_department`;
CREATE TABLE `ms_department` (
  `id`          int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name`        varchar(50)  NOT NULL                COMMENT '院系名称',
  `code`        varchar(20)  NOT NULL DEFAULT ''     COMMENT '院系代码',
  `leader`      varchar(30)  NOT NULL DEFAULT ''     COMMENT '负责人',
  `sort`        int(6)       NOT NULL DEFAULT 0      COMMENT '排序',
  `status`      tinyint(1)   NOT NULL DEFAULT 1      COMMENT '状态',
  `create_time` int(11)      NOT NULL DEFAULT 0      COMMENT '创建时间',
  `update_time` int(11)      NOT NULL DEFAULT 0      COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='院系表';

-- 班级表
DROP TABLE IF EXISTS `ms_class`;
CREATE TABLE `ms_class` (
  `id`           int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name`         varchar(50)  NOT NULL                COMMENT '班级名称',
  `grade`        varchar(20)  NOT NULL DEFAULT ''     COMMENT '年级 如2024',
  `department_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '所属院系ID',
  `head_teacher` varchar(30)  NOT NULL DEFAULT ''     COMMENT '班主任',
  `student_num`  int(6)       NOT NULL DEFAULT 0      COMMENT '学生人数',
  `status`       tinyint(1)   NOT NULL DEFAULT 1      COMMENT '状态 1在读 0毕业',
  `create_time`  int(11)      NOT NULL DEFAULT 0      COMMENT '创建时间',
  `update_time`  int(11)      NOT NULL DEFAULT 0      COMMENT '更新时间',
  `delete_time`  int(11)      DEFAULT NULL            COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_department` (`department_id`),
  KEY `idx_grade` (`grade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='班级表';

-- 学生表
DROP TABLE IF EXISTS `ms_student`;
CREATE TABLE `ms_student` (
  `id`           int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `student_no`   varchar(20)  NOT NULL                COMMENT '学号',
  `name`         varchar(30)  NOT NULL                COMMENT '姓名',
  `gender`       tinyint(1)   NOT NULL DEFAULT 0      COMMENT '性别 0未知 1男 2女',
  `birthday`     date         DEFAULT NULL            COMMENT '出生日期',
  `id_card`      varchar(18)  NOT NULL DEFAULT ''     COMMENT '身份证号',
  `phone`        varchar(20)  NOT NULL DEFAULT ''     COMMENT '联系电话',
  `email`        varchar(60)  NOT NULL DEFAULT ''     COMMENT '邮箱',
  `address`      varchar(120) NOT NULL DEFAULT ''     COMMENT '家庭住址',
  `class_id`     int(10) unsigned NOT NULL DEFAULT 0  COMMENT '班级ID',
  `enroll_date`  date         DEFAULT NULL            COMMENT '入学日期',
  `status`       tinyint(1)   NOT NULL DEFAULT 1      COMMENT '状态 1在读 0休学 2毕业 3退学',
  `remark`       varchar(200) NOT NULL DEFAULT ''     COMMENT '备注',
  `create_time`  int(11)      NOT NULL DEFAULT 0      COMMENT '创建时间',
  `update_time`  int(11)      NOT NULL DEFAULT 0      COMMENT '更新时间',
  `delete_time`  int(11)      DEFAULT NULL            COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_student_no` (`student_no`),
  KEY `idx_class` (`class_id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学生表';

-- 教师表
DROP TABLE IF EXISTS `ms_teacher`;
CREATE TABLE `ms_teacher` (
  `id`           int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `teacher_no`   varchar(20)  NOT NULL                COMMENT '工号',
  `name`         varchar(30)  NOT NULL                COMMENT '姓名',
  `gender`       tinyint(1)   NOT NULL DEFAULT 0      COMMENT '性别',
  `title`        varchar(30)  NOT NULL DEFAULT ''     COMMENT '职称',
  `phone`        varchar(20)  NOT NULL DEFAULT ''     COMMENT '联系电话',
  `email`        varchar(60)  NOT NULL DEFAULT ''     COMMENT '邮箱',
  `department_id` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '所属院系ID',
  `create_time`  int(11)      NOT NULL DEFAULT 0      COMMENT '创建时间',
  `update_time`  int(11)      NOT NULL DEFAULT 0      COMMENT '更新时间',
  `delete_time`  int(11)      DEFAULT NULL            COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_teacher_no` (`teacher_no`),
  KEY `idx_department` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='教师表';

-- 课程表
DROP TABLE IF EXISTS `ms_course`;
CREATE TABLE `ms_course` (
  `id`           int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `course_no`    varchar(20)  NOT NULL                COMMENT '课程编号',
  `name`         varchar(60)  NOT NULL                COMMENT '课程名称',
  `credit`       decimal(3,1) NOT NULL DEFAULT '0.0'   COMMENT '学分',
  `hours`        int(6)       NOT NULL DEFAULT 0      COMMENT '总学时',
  `course_type`  tinyint(1)   NOT NULL DEFAULT 1      COMMENT '类型 1必修 2选修 3公共课',
  `teacher_id`   int(10) unsigned NOT NULL DEFAULT 0  COMMENT '授课教师ID',
  `department_id` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '开课院系ID',
  `semester`     varchar(20)  NOT NULL DEFAULT ''     COMMENT '学期 如2024-2025-1',
  `status`       tinyint(1)   NOT NULL DEFAULT 1      COMMENT '状态 1开课 0停开',
  `remark`       varchar(200) NOT NULL DEFAULT ''     COMMENT '课程简介',
  `create_time`  int(11)      NOT NULL DEFAULT 0      COMMENT '创建时间',
  `update_time`  int(11)      NOT NULL DEFAULT 0      COMMENT '更新时间',
  `delete_time`  int(11)      DEFAULT NULL            COMMENT '软删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_course_no` (`course_no`),
  KEY `idx_teacher` (`teacher_id`),
  KEY `idx_semester` (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程表';

-- 选课表(学生选课记录)
DROP TABLE IF EXISTS `ms_select_course`;
CREATE TABLE `ms_select_course` (
  `id`         int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `student_id` int(10) unsigned NOT NULL              COMMENT '学生ID',
  `course_id`  int(10) unsigned NOT NULL              COMMENT '课程ID',
  `semester`   varchar(20)  NOT NULL DEFAULT ''       COMMENT '学期',
  `score`      decimal(5,1) DEFAULT NULL              COMMENT '总评成绩',
  `status`     tinyint(1)   NOT NULL DEFAULT 1        COMMENT '状态 1已选 0已退',
  `create_time` int(11)     NOT NULL DEFAULT 0        COMMENT '创建时间',
  `update_time` int(11)     NOT NULL DEFAULT 0        COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_student_course` (`student_id`,`course_id`),
  KEY `idx_course` (`course_id`),
  KEY `idx_semester` (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='选课表';

-- ----------------------------------------------------------------------------
-- 系统日志
-- ----------------------------------------------------------------------------

-- 登录日志
DROP TABLE IF EXISTS `ms_login_log`;
CREATE TABLE `ms_login_log` (
  `id`         int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `admin_id`   int(10) unsigned NOT NULL DEFAULT 0    COMMENT '账号ID',
  `username`   varchar(30)  NOT NULL DEFAULT ''       COMMENT '登录账号',
  `ip`         varchar(46)  NOT NULL DEFAULT ''       COMMENT '登录IP',
  `user_agent` varchar(200) NOT NULL DEFAULT ''       COMMENT '浏览器信息',
  `status`     tinyint(1)   NOT NULL DEFAULT 1        COMMENT '结果 1成功 0失败',
  `msg`        varchar(200) NOT NULL DEFAULT ''       COMMENT '提示信息',
  `login_time` int(11)      NOT NULL DEFAULT 0        COMMENT '登录时间',
  PRIMARY KEY (`id`),
  KEY `idx_admin` (`admin_id`),
  KEY `idx_time` (`login_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='登录日志表';

-- 操作日志
DROP TABLE IF EXISTS `ms_operation_log`;
CREATE TABLE `ms_operation_log` (
  `id`          int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `admin_id`    int(10) unsigned NOT NULL DEFAULT 0   COMMENT '操作人ID',
  `username`    varchar(30)  NOT NULL DEFAULT ''      COMMENT '操作人账号',
  `module`      varchar(50)  NOT NULL DEFAULT ''      COMMENT '功能模块',
  `action`      varchar(50)  NOT NULL DEFAULT ''      COMMENT '操作动作',
  `method`      varchar(120) NOT NULL DEFAULT ''      COMMENT '请求方法',
  `url`         varchar(200) NOT NULL DEFAULT ''      COMMENT '请求地址',
  `ip`          varchar(46)  NOT NULL DEFAULT ''      COMMENT '操作IP',
  `params`      text         DEFAULT NULL             COMMENT '请求参数',
  `result`      text         DEFAULT NULL             COMMENT '返回结果摘要',
  `status`      tinyint(1)   NOT NULL DEFAULT 1       COMMENT '状态 1成功 0失败',
  `error_msg`   varchar(500) NOT NULL DEFAULT ''      COMMENT '错误信息',
  `operate_time` int(11)     NOT NULL DEFAULT 0       COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `idx_admin` (`admin_id`),
  KEY `idx_module` (`module`),
  KEY `idx_time` (`operate_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='操作日志表';

-- ----------------------------------------------------------------------------
-- 初始化数据
-- ----------------------------------------------------------------------------

-- 账号: 密码统一为 admin123 / teacher123 (md5(密码盐 + 明文))
INSERT INTO `ms_admin` (`username`,`password`,`salt`,`nickname`,`email`,`sex`,`status`,`create_time`,`update_time`) VALUES
('admin','d8ec77afa4256d2c3abd60a9e799faa9','sms8x2k','系统管理员','admin@student-ms.local',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('teacher','8cebe4b61ddaaa8b9d81435a95704f01','sms8x2k','毕柱兰','bzl@student-ms.local',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

-- 角色
INSERT INTO `ms_role` (`name`,`code`,`remark`,`status`,`sort`,`create_time`,`update_time`) VALUES
('超级管理员','admin','拥有系统全部权限',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('教务管理员','academic','负责院系、班级、学生、课程与成绩管理',1,2,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('教师','teacher','负责所授课程成绩录入与查询',1,3,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('学生','student','查询本人学籍与成绩信息',1,4,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

-- 权限菜单 (type=1 菜单, type=2 操作按钮; path 对应路由路径)
INSERT INTO `ms_auth` (`pid`,`name`,`type`,`path`,`icon`,`sort`,`status`,`create_time`,`update_time`) VALUES
(0,'系统管理',1,'','layui-icon-console',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(0,'学生管理',1,'','layui-icon-user',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(0,'教务管理',1,'','layui-icon-read',3,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(0,'日志管理',1,'','layui-icon-log',4,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(1,'账号管理',1,'admin','layui-icon-username',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(1,'角色管理',1,'role','layui-icon-user-solid',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(1,'权限管理',1,'auth','layui-icon-tree',3,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,'学生列表',1,'student','layui-icon-list',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,'班级管理',1,'class','layui-icon-group',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,'院系管理',1,'department','layui-icon-component',3,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,'教师管理',1,'teacher','layui-icon-avatar',4,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(3,'课程管理',1,'course','layui-icon-template',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(3,'选课管理',1,'select','layui-icon-cart-simple',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(3,'成绩管理',1,'score','layui-icon-chart',3,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(4,'登录日志',1,'loginlog','layui-icon-notice',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(4,'操作日志',1,'operationlog','layui-icon-engine',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
-- 操作级权限
(5,'查看账号',2,'admin/list','',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(5,'新增账号',2,'admin/save','',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(5,'删除账号',2,'admin/delete','',3,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(5,'重置密码',2,'admin/reset','',4,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(6,'查看角色',2,'role/list','',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(6,'新增角色',2,'role/save','',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(6,'删除角色',2,'role/delete','',3,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(7,'查看权限',2,'auth/list','',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(7,'新增权限',2,'auth/save','',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(7,'删除权限',2,'auth/delete','',3,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(8,'查看学生',2,'student/list','',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(8,'新增学生',2,'student/save','',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(8,'删除学生',2,'student/delete','',3,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(8,'导出名单',2,'student/export','',4,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(9,'查看班级',2,'class/list','',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(9,'新增班级',2,'class/save','',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(9,'删除班级',2,'class/delete','',3,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(10,'查看院系',2,'department/list','',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(10,'新增院系',2,'department/save','',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(10,'删除院系',2,'department/delete','',3,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(11,'查看教师',2,'teacher/list','',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(11,'新增教师',2,'teacher/save','',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(11,'删除教师',2,'teacher/delete','',3,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(12,'查看课程',2,'course/list','',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(12,'新增课程',2,'course/save','',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(12,'删除课程',2,'course/delete','',3,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(13,'查看选课',2,'select/list','',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(13,'新增选课',2,'select/save','',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(13,'学生退课',2,'select/delete','',3,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(14,'查看成绩',2,'score/list','',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(14,'录入成绩',2,'score/save','',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(14,'批量录入',2,'score/batch','',3,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(14,'成绩统计',2,'score/statistics','',4,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(15,'查看登录日志',2,'loginlog/list','',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(15,'清空登录日志',2,'loginlog/clear','',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(16,'查看操作日志',2,'operationlog/list','',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(16,'清空操作日志',2,'operationlog/clear','',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(6,'分配权限',2,'role/assign','',4,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

-- 账号角色关联
INSERT INTO `ms_admin_role` (`admin_id`,`role_id`) VALUES (1,1),(2,3);

-- 角色权限关联: 超管拥有全部权限
INSERT INTO `ms_role_auth` (`role_id`,`auth_id`)
SELECT 1, id FROM `ms_auth`;

-- 教师角色: 可查看学生、课程、选课、成绩，可录入成绩
INSERT INTO `ms_role_auth` (`role_id`,`auth_id`) VALUES
(3,8),(3,12),(3,13),(3,14),(3,27),(3,40),(3,43),(3,46),(3,47),(3,48);

-- 院系
INSERT INTO `ms_department` (`name`,`code`,`leader`,`sort`,`status`,`create_time`,`update_time`) VALUES
('计算机信息学院','CS','毕柱兰',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('信息工程学院','SE','李明',2,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

-- 班级
INSERT INTO `ms_class` (`name`,`grade`,`department_id`,`head_teacher`,`student_num`,`status`,`create_time`,`update_time`) VALUES
('信息安全2408班','2024',1,'毕柱兰',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('软件工程2405班','2024',1,'张伟',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('网络技术2403班','2024',2,'王芳',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

-- 教师
INSERT INTO `ms_teacher` (`teacher_no`,`name`,`gender`,`title`,`phone`,`email`,`department_id`,`create_time`,`update_time`) VALUES
('T1001','毕柱兰',2,'副教授','13800138001','bzl@student-ms.local',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('T1002','张伟',1,'讲师','13800138002','zw@student-ms.local',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('T1003','王芳',2,'讲师','13800138003','wf@student-ms.local',2,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

-- 学生
INSERT INTO `ms_student` (`student_no`,`name`,`gender`,`birthday`,`phone`,`email`,`address`,`class_id`,`enroll_date`,`status`,`create_time`,`update_time`) VALUES
('2400012811','郭佳辰',1,'2005-03-18','13812345678','gjc@student-ms.local','云南省昆明市',1,'2024-09-01',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('2400012812','李思远',1,'2005-07-22','13812345679','lsy@student-ms.local','云南省昭通市',1,'2024-09-01',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('2400012813','王雨欣',2,'2006-01-09','13812345680','wyx@student-ms.local','云南省曲靖市',1,'2024-09-01',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('2400012814','赵子豪',1,'2005-11-30','13812345681','zzh@student-ms.local','云南省玉溪市',1,'2024-09-01',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('2400012815','陈静怡',2,'2006-04-15','13812345682','cjy@student-ms.local','云南省楚雄州',1,'2024-09-01',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('2400015001','刘泽宇',1,'2005-09-05','13812345683','lzy@student-ms.local','云南省昆明市',2,'2024-09-01',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('2400015002','孙嘉怡',2,'2005-12-25','13812345684','sjy@student-ms.local','四川省成都市',2,'2024-09-01',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('2400015003','周文博',1,'2005-06-08','13812345685','zwb@student-ms.local','贵州省贵阳市',2,'2024-09-01',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('2400021001','吴梦琪',2,'2005-08-19','13812345686','wmq@student-ms.local','云南省昆明市',3,'2024-09-01',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('2400021002','郑浩然',1,'2006-02-27','13812345687','zhr@student-ms.local','云南省红河州',3,'2024-09-01',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

-- 课程
INSERT INTO `ms_course` (`course_no`,`name`,`credit`,`hours`,`course_type`,`teacher_id`,`department_id`,`semester`,`status`,`remark`,`create_time`,`update_time`) VALUES
('C001','信息安全概论',3.0,48,1,1,1,'2024-2025-1',1,'介绍信息安全领域的基本概念、体系结构与发展趋势',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('C002','Web应用开发',4.0,64,1,2,1,'2024-2025-1',1,'基于 ThinkPHP6 与 Layui 的Web应用开发实践',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('C003','数据库原理及应用',3.5,56,1,2,1,'2024-2025-1',1,'关系数据库原理、SQL语言与数据库设计',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('C004','计算机网络',3.0,48,1,3,2,'2024-2025-1',1,'计算机网络体系结构与通信协议',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('C005','数据结构',4.0,64,1,2,1,'2024-2025-2',1,'线性表、树、图等线性结构与算法实现',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('C006','信息安全法律法规',2.0,32,3,1,1,'2024-2025-1',1,'网络安全法、数据安全法与个人信息保护法',UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

-- 选课与成绩
INSERT INTO `ms_select_course` (`student_id`,`course_id`,`semester`,`score`,`status`,`create_time`,`update_time`) VALUES
(1,1,'2024-2025-1',88.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(1,2,'2024-2025-1',92.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(1,3,'2024-2025-1',85.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(1,6,'2024-2025-1',90.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,1,'2024-2025-1',79.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,2,'2024-2025-1',81.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(2,3,'2024-2025-1',76.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(3,1,'2024-2025-1',95.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(3,2,'2024-2025-1',89.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(3,3,'2024-2025-1',93.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(4,1,'2024-2025-1',68.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(4,2,'2024-2025-1',72.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(5,1,'2024-2025-1',91.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(5,2,'2024-2025-1',87.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(5,3,'2024-2025-1',84.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(6,1,'2024-2025-1',86.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(6,2,'2024-2025-1',83.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(6,5,'2024-2025-2',78.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(7,1,'2024-2025-1',94.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(7,2,'2024-2025-1',90.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(7,3,'2024-2025-1',88.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(8,1,'2024-2025-1',75.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(8,2,'2024-2025-1',70.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(9,1,'2024-2025-1',82.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(9,4,'2024-2025-1',80.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(10,1,'2024-2025-1',77.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
(10,4,'2024-2025-1',85.0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

-- 统计班级人数
UPDATE `ms_class` c SET c.student_num = (
  SELECT COUNT(*) FROM `ms_student` s WHERE s.class_id = c.id AND s.delete_time IS NULL
) WHERE c.id IN (1,2,3);
