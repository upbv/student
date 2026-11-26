# -*- coding: utf-8 -*-
"""生成《毕业设计任务执行情况记录》——按学院模板每周一页。"""
import os
from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

OUT = os.path.join(os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs")),
                   "毕业设计任务执行情况记录.docx")


def set_font(run, cn="宋体", en="Times New Roman", size=12, bold=False):
    run.font.name = en
    run.font.size = Pt(size)
    run.font.bold = bold
    run._element.rPr.rFonts.set(qn("w:eastAsia"), cn)


def p(doc, text="", cn="宋体", size=12, bold=False,
      align=WD_ALIGN_PARAGRAPH.LEFT, before=0, after=0, line=1.3, indent=0):
    par = doc.add_paragraph()
    pf = par.paragraph_format
    pf.alignment = align
    pf.line_spacing = line
    pf.space_before = Pt(before)
    pf.space_after = Pt(after)
    if indent:
        pf.first_line_indent = Pt(size * indent)
    if text:
        r = par.add_run(text)
        set_font(r, cn, "Times New Roman", size, bold)
    return par


def cell_text(cell, text, size=11, bold=False, align=WD_ALIGN_PARAGRAPH.LEFT, cn="宋体"):
    cell.text = ""
    par = cell.paragraphs[0]
    par.alignment = align
    par.paragraph_format.line_spacing = 1.3
    lines = text.split("\n")
    for i, line in enumerate(lines):
        if i > 0:
            par = cell.add_paragraph()
            par.alignment = align
            par.paragraph_format.line_spacing = 1.3
        r = par.add_run(line)
        set_font(r, cn, "Times New Roman", size, bold)


WEEKS = [
    dict(no=12, date="2026年11月16日—11月22日",
         task="确定选题及指导老师。结合专业方向与个人兴趣，查阅学生管理系统相关的资料，"
              "与指导老师沟通选题的可行性和范围，明确课题名称、开发技术路线和最终提交材料清单。",
         work="本周与指导教师毕柱兰老师进行了两次沟通，最终确定课题为“基于ThinkPHP6+Layui"
              "的学生管理系统的设计与实现”。明确了系统的使用对象为学院教务管理人员、教师和"
              "系统管理员，开发技术采用 ThinkPHP6 框架与 Layui 前端组件库，数据库选用 "
              "MariaDB，运行环境使用 Docker 容器化部署。根据学院下发的《每周执行情况要求》，"
              "整理了毕业设计需要提交的材料：任务执行情况记录、毕业论文、答辩PPT和源代码，"
              "并制定了后续各周的工作计划。",
         self="优", note="选题贴合专业培养目标，技术路线明确，同意按计划开展。"),
    dict(no=13, date="2026年11月23日—11月29日",
         task="开展需求分析与资料学习。调研学院学生管理的业务流程，梳理系统功能需求，"
              "学习 ThinkPHP6 框架的路由、中间件、ORM 机制和 Layui 组件库的使用方法。",
         work="通过走访学院教务办公室、查阅现有学生管理表格，梳理出账号权限、学生信息、"
              "班级院系、教师课程、选课成绩、日志六类业务需求。完成需求分析文档，明确超级"
              "管理员、教务管理员、教师三类角色的职责范围。同时搭建了 ThinkPHP6 学习环境，"
              "跑通了框架自带的示例，重点学习了中间件管道机制和 RBAC 权限模型的实现思路，"
              "为后续编码打下基础。",
         self="优", note="需求分析较为完整，角色划分合理，继续深入学习框架。"),
    dict(no=14, date="2026年11月30日—12月6日",
         task="搭建系统开发环境，创建数据库表；开发登录模块；同步撰写毕业设计论文"
              "（绪论、需求分析部分）。",
         work="使用 Docker Compose 搭建了 Nginx + PHP-FPM + MariaDB 的开发环境，"
              "编写 Dockerfile 与入口脚本解决运行目录权限问题，配置 Nginx 的 PATH_INFO "
              "转发规则。设计了 student_ms 数据库，共 15 张表：账号、角色、权限、账号角色、"
              "角色权限、院系、班级、学生、教师、课程、选课、请假、资助、登录日志、操作日志，"
              "并编写初始化 SQL 脚本和示例数据。完成登录模块开发，包括登录页、登录接口、"
              "密码加盐校验、失败锁定和登录日志记录。论文第1章绪论、第2章需求分析完成初稿。",
         self="优", note="环境搭建规范，数据库设计合理，登录功能已可演示。"),
    dict(no=15, date="2026年12月7日—12月13日",
         task="开发用户与权限管理模块、学生信息管理模块；同步撰写毕业设计论文"
              "（系统设计部分）。",
         work="完成账号管理、角色管理、权限管理三个模块的开发，实现账号增删改查、"
              "密码重置、角色分配、权限树分配等功能。设计了 CheckLogin 与 CheckPermission "
              "两个中间件，实现登录状态校验和接口级权限校验；权限树按菜单与操作两级组织，"
              "菜单权限用于生成侧边导航，操作权限用于拦截未授权的接口调用。完成学生信息"
              "管理模块，支持组合条件查询、分页、新增编辑、软删除、批量删除、详情查看和 "
              "CSV 名单导出。论文第3章系统总体设计框架基本成型。",
         self="优", note="权限控制方案设计合理，学生模块功能完整。"),
    dict(no=16, date="2026年12月14日—12月20日",
         task="开发班级管理模块、课程与成绩管理模块；同步撰写毕业设计论文"
              "（数据库设计、系统实现部分）。",
         work="完成院系管理、班级管理模块，实现院系与班级的增删改查，班级人数随学生变更"
              "自动同步，删除前做关联数据检查，防止产生孤立数据。完成教师管理、课程管理、"
              "选课管理模块，选课支持重复选课拦截和退课后恢复。完成成绩管理模块，支持单条"
              "录入、按课程批量录入（以“学号 成绩”格式粘贴）和成绩统计。论文第3章数据库"
              "设计部分完成 13 张表的字段说明。",
         self="优", note="教务模块业务逻辑完整，边界数据处理到位。"),
    dict(no=17, date="2026年12月21日—12月27日",
         task="开发系统日志与安全、公共功能模块，完成系统集成与优化；完成毕业设计论文初稿。",
         work="完成登录日志和操作日志模块，操作日志通过中间件自动记录新增、修改、删除类"
              "请求的操作人、模块、动作、参数与结果，并对密码等敏感参数做掩码处理。完成"
              "个人资料、修改密码、控制台数据面板等公共功能。对系统进行集成优化，统一了"
              "各页面的查询栏、表格和分页样式，修复了多标签页切换、列表数据关联查询等问题。"
              "同时完成请假管理、资助管理以及按角色区分的工作台（管理员、辅导员、教师、"
              "学生分别进入不同的首页与数据范围），并为教师成绩录入增加课程归属校验。"
              "论文全文初稿完成，共五章及总结、致谢、参考文献、附录，交付指导教师审阅。",
         self="优", note="日志审计较为完整，论文初稿结构符合模板要求。"),
    dict(no=18, date="2026年12月28日—2027年1月3日",
         task="系统功能测试与修复；系统部署与网站发布；完善毕业设计论文，准备答辩PPT。",
         work="设计并执行了 21 个测试用例，覆盖登录校验、权限拦截、学生与教务业务、"
              "成绩统计和日志管理，全部通过。测试过程中修复了班级人数未同步、批量成绩"
              "按学号匹配等问题。使用 Docker Compose 完成系统部署，执行一条命令即可启动"
              "全部服务，编写了部署说明。根据指导教师意见修改论文，补充测试章节和图表；"
              "完成答辩 PPT 初稿。",
         self="优", note="测试覆盖较全面，部署方式简洁，PPT 需再精炼。"),
    dict(no=19, date="2027年1月4日—1月10日",
         task="毕业设计论文定稿；参加毕业答辩（现场演示系统功能）。",
         work="根据指导教师第二轮修改意见，调整了论文的章节结构和图表编号，补充了权限"
              "模型和测试结果的说明，论文定稿。按答辩要求准备了演示环境，依次演示登录、"
              "权限分配、学生管理、选课与成绩录入、操作日志等功能，并对答辩组提出的系统"
              "并发能力、数据备份等问题进行了回答。答辩后按答辩组意见整理了修改记录。",
         self="优", note="现场演示流畅，答辩准备充分，按意见完善材料。"),
    dict(no=20, date="2027年1月11日—1月17日",
         task="提交全部成果及过程性材料，完成成果材料归档。",
         work="整理并提交毕业设计全部材料：毕业设计任务执行情况记录、毕业论文、答辩 PPT "
              "和源代码。源代码按目录结构打包，附部署说明与数据库脚本；论文按学院要求命名"
              "并提交电子版和纸质版。完成材料归档，毕业设计工作结束。",
         self="优", note="材料齐全，归档规范。"),
]


def build():
    doc = Document()
    sec = doc.sections[0]
    sec.page_width, sec.page_height = Cm(21), Cm(29.7)
    sec.top_margin = sec.bottom_margin = Cm(2.2)
    sec.left_margin = sec.right_margin = Cm(2.4)

    style = doc.styles["Normal"]
    style.font.name = "Times New Roman"
    style.font.size = Pt(12)
    style.element.rPr.rFonts.set(qn("w:eastAsia"), "宋体")

    # ---------- 封面 ----------
    p(doc, "", size=12, before=60)
    p(doc, "毕业设计任务执行情况记录", cn="黑体", size=26, bold=True,
      align=WD_ALIGN_PARAGRAPH.CENTER, after=50)
    for label, value in [("姓　　名", "郭佳辰"), ("学　　号", "2400012811"),
                         ("专　　业", "信息安全"), ("班　　级", "信息安全2408班"),
                         ("指导教师", "毕柱兰")]:
        par = p(doc, "", cn="黑体", size=16, align=WD_ALIGN_PARAGRAPH.CENTER,
                before=8, line=1.8)
        r1 = par.add_run(label + "：")
        set_font(r1, "黑体", "Times New Roman", 16)
        r2 = par.add_run("　" + value + "　")
        set_font(r2, "黑体", "Times New Roman", 16)
        r2.font.underline = True
    p(doc, "", size=12, before=40)
    p(doc, "计算机信息学院", cn="黑体", size=16, align=WD_ALIGN_PARAGRAPH.CENTER, before=30)
    p(doc, "2027年1月", cn="黑体", size=16, align=WD_ALIGN_PARAGRAPH.CENTER, before=8)
    doc.add_page_break()

    # ---------- 填写说明 ----------
    p(doc, "填 写 说 明", cn="黑体", size=16, bold=True,
      align=WD_ALIGN_PARAGRAPH.CENTER, after=18)
    for line in [
        "本手册是学生执行毕业设计任务的过程性记录，由学生和指导教师填写。",
        "本手册将作为毕业设计成绩过程性成绩评定的依据，占毕业设计成绩50%。",
        "过程性成绩评定由每周的教师评价成绩折算的平均分得到，教师无指导意见签字，视为无成绩。",
        "本手册以纸质形式填写，毕业设计结束后与成果性文件一起提交。",
    ]:
        p(doc, line, size=12, before=4, line=1.6, indent=2)
    doc.add_page_break()

    # ---------- 每周记录 ----------
    for i, w in enumerate(WEEKS):
        table = doc.add_table(rows=6, cols=2)
        table.style = "Table Grid"
        table.alignment = WD_TABLE_ALIGNMENT.CENTER
        table.columns[0].width = Cm(4.2)
        table.columns[1].width = Cm(12.2)

        # 第X周 起止时间
        row = table.rows[0]
        row.cells[0].merge(row.cells[1])
        cell_text(row.cells[0],
                  "第 %d 周　　　　　　　　起止时间：%s" % (w["no"], w["date"]),
                  size=12, bold=True, align=WD_ALIGN_PARAGRAPH.CENTER)

        cell_text(table.rows[1].cells[0], "本周任务、要求：", size=11, bold=True)
        cell_text(table.rows[1].cells[1], w["task"], size=11)
        table.rows[1].cells[0].width = Cm(4.2)

        cell_text(table.rows[2].cells[0], "任务计划执行情况：", size=11, bold=True)
        cell_text(table.rows[2].cells[1], w["work"], size=11)

        # 自评 / 教师评价
        row3 = table.rows[3]
        r3c0 = row3.cells[0]
        cell_text(r3c0, "任务完成自评成绩：\n（优、良、中、差）", size=11, bold=True)
        # 拆分为两列不方便，改用单独两行小表
        cell_text(row3.cells[1], w["self"], size=11, align=WD_ALIGN_PARAGRAPH.CENTER)

        row4 = table.rows[4]
        cell_text(row4.cells[0], "教师评价成绩：\n（优、良、中、差）", size=11, bold=True)
        cell_text(row4.cells[1], "", size=11, align=WD_ALIGN_PARAGRAPH.CENTER)

        row5 = table.rows[5]
        cell_text(row5.cells[0], "指导教师指导意见：\n\n\n签名：\n时间：　　年　　月　　日", size=11, bold=True)
        cell_text(row5.cells[1], w["note"], size=11)

        # 设置行高
        table.rows[1].height = Cm(3.2)
        table.rows[2].height = Cm(7.2)
        table.rows[3].height = Cm(1.2)
        table.rows[4].height = Cm(1.2)
        table.rows[5].height = Cm(3.6)

        # 表格后留白，除最后一页外分页
        if i < len(WEEKS) - 1:
            doc.add_page_break()

    doc.save(OUT)
    print("saved:", OUT)


if __name__ == "__main__":
    build()
