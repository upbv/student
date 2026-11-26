# 学生管理系统

基于 ThinkPHP 6.1.5 + Layui 2.9.21 的学生管理系统，数据库使用 MariaDB 11，
运行环境通过 Docker Compose 编排（Nginx + PHP-FPM + MariaDB）。

## 快速启动

安装 Docker 与 Docker Compose 后，在项目根目录执行：

```bash
docker compose up -d
```

首次启动会自动创建数据库、导入 `database/student_ms.sql` 中的表结构与示例数据。
启动完成后访问：

```
http://localhost:8080
```

演示账号：

| 角色 | 账号 | 密码 | 登录后界面 |
| --- | --- | --- | --- |
| 超级管理员 | admin | admin123 | 全校管理工作台 |
| 教务管理员 | — | — | 教务管理工作台 |
| 辅导员 | counselor | counselor123 | 学生事务工作台（请假审批、资助管理） |
| 教师 | teacher | teacher123 | 教学主页（我的授课、我的学生、成绩录入） |
| 学生 | 2400012811 | student123 | 学生主页（我的成绩、请假、资助） |

系统初始化时会为每名学生创建登录账号（账号为学号，初始密码 student123，角色为学生）；
在「学生管理」新增学生时也会自动创建对应账号。

停止服务：

```bash
docker compose stop
```

清除全部数据并重新初始化：

```bash
docker compose down -v && docker compose up -d
```

## 目录说明

```
app/                 应用代码
├── controller/      控制器（14 个）
├── middleware/      中间件：登录校验、权限校验、操作日志
├── model/           模型（13 个）
├── service/         认证、权限、日志服务
config/              框架配置
database/            数据库建表与初始化脚本
docker/              Nginx、PHP-FPM 容器配置
extend/              ThinkPHP 模板引擎适配驱动
public/              入口文件与静态资源（Layui、自定义样式与脚本）
route/               路由定义
view/                页面模板
docs/                毕业设计交付材料（论文、任务记录、答辩 PPT、插图）
tools/               交付材料生成脚本
```

## 功能模块

- 系统管理：账号管理、角色管理、权限管理
- 学生管理：学生列表、班级管理、院系管理、教师管理、请假管理、资助管理
- 教务管理：课程管理、选课管理、成绩管理（教师仅能操作本人所授课程）
- 学生服务：我的成绩、我的请假、我的资助
- 教师工作台：我的授课、我的学生
- 日志管理：登录日志、操作日志

权限采用 RBAC 模型，菜单权限控制导航展示，操作权限控制接口调用；
登录后按角色进入不同的工作台：管理员看全校数据，辅导员聚焦学生事务，
教师只看本人授课与相关学生，学生只看本人数据。
