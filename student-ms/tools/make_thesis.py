# -*- coding: utf-8 -*-
"""
生成毕业设计论文《基于ThinkPHP6+Layui的学生管理系统的设计与实现》
按《计算机信息学院毕业设计论文格式模板》排版：
    章标题  三号黑体居中
    节标题  四号黑体
    小节    小四号黑体
    正文    小四号宋体，1.5 倍行距，段前段后 0 行，首行缩进 2 字符
    图名    图正下方，宋体小五号加粗，按章编号
    表题    表上方，宋体小五号加粗，按章编号
    页眉    昆明冶金职业大学 计算机信息学院毕业设计（论文）
"""
import os
from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_LINE_SPACING
from docx.enum.section import WD_SECTION
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

OUT = os.path.join(os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs")),
                   "基于ThinkPHP6+Layui的学生管理系统的设计与实现.docx")

PAGE_W, PAGE_H = Cm(21.0), Cm(29.7)      # A4
MARGIN_TB, MARGIN_L, MARGIN_R = Cm(2.54), Cm(3.0), Cm(2.6)


# --------------------------------------------------------------------------
# 基础工具
# --------------------------------------------------------------------------
def set_run_font(run, cn_font="宋体", en_font="Times New Roman", size=12,
                 bold=False, color=None):
    run.font.name = en_font
    run.font.size = Pt(size)
    run.font.bold = bold
    run._element.rPr.rFonts.set(qn("w:eastAsia"), cn_font)
    if color:
        run.font.color.rgb = color


def para(doc, text="", cn="宋体", en="Times New Roman", size=12, bold=False,
         align=WD_ALIGN_PARAGRAPH.JUSTIFY, indent_chars=0, line=1.5,
         before=0, after=0, style=None):
    p = doc.add_paragraph(style=style) if style else doc.add_paragraph()
    pf = p.paragraph_format
    pf.alignment = align
    pf.line_spacing = line
    pf.space_before = Pt(before)
    pf.space_after = Pt(after)
    if indent_chars:
        # 首行缩进按字符数换算（字号 size pt）
        pf.first_line_indent = Pt(size * indent_chars)
    if text:
        r = p.add_run(text)
        set_run_font(r, cn, en, size, bold)
    return p


def _heading(doc, text, level, size, align):
    """带大纲级别的标题，供目录域抓取"""
    p = doc.add_paragraph(style="Heading %d" % level)
    pf = p.paragraph_format
    pf.alignment = align
    pf.line_spacing = 1.5
    pf.space_before = Pt(12 if level == 1 else 6)
    pf.space_after = Pt(12 if level == 1 else 6)
    r = p.add_run(text)
    set_run_font(r, "黑体", "Times New Roman", size, bold=False)
    r.font.color.rgb = RGBColor(0, 0, 0)
    return p


def chapter_title(doc, text):
    """章标题：三号黑体居中"""
    return _heading(doc, text, 1, 16, WD_ALIGN_PARAGRAPH.CENTER)


def section_title(doc, text):
    """节标题：四号黑体"""
    return _heading(doc, text, 2, 14, WD_ALIGN_PARAGRAPH.LEFT)


def subsection_title(doc, text):
    """小节标题：小四号黑体"""
    return _heading(doc, text, 3, 12, WD_ALIGN_PARAGRAPH.LEFT)


def body(doc, text):
    """正文：小四号宋体，1.5 倍行距，首行缩进 2 字符"""
    return para(doc, text, cn="宋体", size=12, indent_chars=2)


def caption(doc, text):
    """图表题注：宋体小五号加粗居中"""
    return para(doc, text, cn="宋体", size=9, bold=True,
                align=WD_ALIGN_PARAGRAPH.CENTER, line=1.0, before=3, after=6)


def bullet(doc, text):
    """列举项：段首不缩进，用（1）（2）标号"""
    return para(doc, text, cn="宋体", size=12, indent_chars=2)


def page_break(doc):
    doc.add_page_break()


def add_header(section, text):
    header = section.header
    header.is_linked_to_previous = False
    p = header.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.text = ""
    r = p.add_run(text)
    set_run_font(r, "宋体", "Times New Roman", 9)
    # 页眉下划线
    pPr = p._p.get_or_add_pPr()
    pbdr = OxmlElement("w:pBdr")
    bottom = OxmlElement("w:bottom")
    bottom.set(qn("w:val"), "single")
    bottom.set(qn("w:sz"), "6")
    bottom.set(qn("w:space"), "1")
    bottom.set(qn("w:color"), "000000")
    pbdr.append(bottom)
    pPr.append(pbdr)


def add_page_number(section):
    """页脚居中页码"""
    footer = section.footer
    footer.is_linked_to_previous = False
    p = footer.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run = p.add_run()
    fld_begin = OxmlElement("w:fldChar")
    fld_begin.set(qn("w:fldCharType"), "begin")
    instr = OxmlElement("w:instrText")
    instr.set(qn("xml:space"), "preserve")
    instr.text = "PAGE"
    fld_end = OxmlElement("w:fldChar")
    fld_end.set(qn("w:fldCharType"), "end")
    run._r.append(fld_begin)
    run._r.append(instr)
    run._r.append(fld_end)
    set_run_font(run, "宋体", "Times New Roman", 10.5)


def setup_section(section):
    section.page_width = PAGE_W
    section.page_height = PAGE_H
    section.top_margin = MARGIN_TB
    section.bottom_margin = MARGIN_TB
    section.left_margin = MARGIN_L
    section.right_margin = MARGIN_R
    add_header(section, "昆明冶金职业大学 计算机信息学院毕业设计（论文）")
    add_page_number(section)


def make_table(doc, headers, rows, widths=None, font_size=9):
    table = doc.add_table(rows=1, cols=len(headers))
    table.style = "Table Grid"
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    hdr = table.rows[0].cells
    for i, h in enumerate(headers):
        hdr[i].text = ""
        p = hdr[i].paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r = p.add_run(h)
        set_run_font(r, "宋体", "Times New Roman", font_size, bold=True)
    for row in rows:
        cells = table.add_row().cells
        for i, val in enumerate(row):
            cells[i].text = ""
            p = cells[i].paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.LEFT if i > 0 else WD_ALIGN_PARAGRAPH.CENTER
            r = p.add_run(str(val))
            set_run_font(r, "宋体", "Times New Roman", font_size)
    if widths:
        for row in table.rows:
            for i, w in enumerate(widths):
                row.cells[i].width = Cm(w)
    return table


def add_picture(doc, path, width_cm, cap=None):
    if os.path.exists(path):
        doc.add_picture(path, width=Cm(width_cm))
        doc.paragraphs[-1].alignment = WD_ALIGN_PARAGRAPH.CENTER
        if cap:
            caption(doc, cap)


# --------------------------------------------------------------------------
# 文档构建
# --------------------------------------------------------------------------
def build():
    doc = Document()

    # 默认样式
    style = doc.styles["Normal"]
    style.font.name = "Times New Roman"
    style.font.size = Pt(12)
    style.element.rPr.rFonts.set(qn("w:eastAsia"), "宋体")

    sec = doc.sections[0]
    setup_section(sec)

    # ---------------- 封面 ----------------
    para(doc, "", size=12)
    para(doc, "昆明冶金职业大学", cn="黑体", size=22,
         align=WD_ALIGN_PARAGRAPH.CENTER, line=1.5, before=40)
    para(doc, "计算机信息学院毕业设计（论文）", cn="黑体", size=18,
         align=WD_ALIGN_PARAGRAPH.CENTER, line=1.5, after=40)

    para(doc, "基于ThinkPHP6+Layui的", cn="黑体", size=22,
         align=WD_ALIGN_PARAGRAPH.CENTER, line=1.8, before=40)
    para(doc, "学生管理系统的设计与实现", cn="黑体", size=22,
         align=WD_ALIGN_PARAGRAPH.CENTER, line=1.8, after=60)

    for label, value in [
        ("学生姓名", "郭佳辰"),
        ("学　　号", "2400012811"),
        ("专业班级", "信息安全2408班"),
        ("指导教师", "毕柱兰"),
    ]:
        p = para(doc, "", cn="黑体", size=16, line=1.8,
                 align=WD_ALIGN_PARAGRAPH.CENTER)
        r1 = p.add_run(label + "：")
        set_run_font(r1, "黑体", "Times New Roman", 16)
        r2 = p.add_run("　" + value + "　")
        set_run_font(r2, "黑体", "Times New Roman", 16)
        # 下划线
        r2.font.underline = True

    para(doc, "", size=12)
    para(doc, "2027年1月", cn="黑体", size=16,
         align=WD_ALIGN_PARAGRAPH.CENTER, line=1.5, before=60)

    page_break(doc)

    # ---------------- 诚信承诺 ----------------
    para(doc, "诚 信 承 诺", cn="黑体", size=16,
         align=WD_ALIGN_PARAGRAPH.CENTER, before=12, after=18)
    body(doc, "我谨在此承诺：本人所写的毕业论文《基于ThinkPHP6+Layui的学生管理系统的"
              "设计与实现》均系本人独立完成，没有抄袭行为，凡涉及其他作者的观点和材料，"
              "均作了注释，若有不实，后果由本人承担。")
    para(doc, "", size=12)
    para(doc, "", size=12)
    p = para(doc, "承诺人（签名）：", size=12, align=WD_ALIGN_PARAGRAPH.RIGHT)
    para(doc, "2027年1月", size=12, align=WD_ALIGN_PARAGRAPH.RIGHT)

    page_break(doc)

    # ---------------- 摘要 ----------------
    para(doc, "摘　要", cn="黑体", size=16,
         align=WD_ALIGN_PARAGRAPH.CENTER, before=6, after=12)
    body(doc, "随着高校招生规模的持续扩大，学生信息、班级编排、课程设置和成绩记录等教务数据"
              "不断增多，传统依靠电子表格和纸质材料的管理方式在数据一致性、权限控制和查询效率"
              "方面的问题日益突出。本文以某学院日常教务工作为背景，设计并实现了一套基于"
              "ThinkPHP6 框架与 Layui 前端组件库的学生管理系统。系统采用 B/S 架构与 MVC 分层"
              "思想，以 MariaDB 作为数据存储，围绕基于角色的访问控制（RBAC）模型构建了账号、"
              "角色、权限三级管理机制，实现了学生信息管理、班级与院系管理、课程管理、选课办理、"
              "成绩录入与统计、请假审批、资助管理、登录日志和操作日志等功能，并根据超级管理员、"
              "教务管理员、辅导员、教师和学生五类角色提供不同的工作台与数据范围，其中教师只能"
              "操作本人所授课程，学生只能查看本人数据。系统借助 Docker 将 Nginx、PHP-FPM 与"
              "数据库打包为容器化运行环境，实现一键部署。功能测试与权限测试结果表明，系统各"
              "模块运行稳定，权限拦截准确，能够满足学院日常学生管理工作的需要。")
    p = para(doc, "", size=12, line=1.5)
    r = p.add_run("关键词：")
    set_run_font(r, "黑体", "Times New Roman", 14)
    r2 = p.add_run("学生管理系统，ThinkPHP6，Layui，RBAC，Docker")
    set_run_font(r2, "宋体", "Times New Roman", 12)

    page_break(doc)

    # ---------------- 目录 ----------------
    para(doc, "目　录", cn="黑体", size=16,
         align=WD_ALIGN_PARAGRAPH.CENTER, before=6, after=12)
    # 插入 TOC 域
    p = doc.add_paragraph()
    run = p.add_run()
    fld_begin = OxmlElement("w:fldChar")
    fld_begin.set(qn("w:fldCharType"), "begin")
    instr = OxmlElement("w:instrText")
    instr.set(qn("xml:space"), "preserve")
    instr.text = 'TOC \\o "1-3" \\h \\z \\u'
    fld_sep = OxmlElement("w:fldChar")
    fld_sep.set(qn("w:fldCharType"), "separate")
    fld_end = OxmlElement("w:fldChar")
    fld_end.set(qn("w:fldCharType"), "end")
    run._r.append(fld_begin)
    run._r.append(instr)
    run._r.append(fld_sep)
    t = doc.add_paragraph("（打开文档后在此处点右键选择“更新域”生成目录）")
    for rr in t.runs:
        set_run_font(rr, "宋体", "Times New Roman", 10.5)
    run2 = doc.add_paragraph().add_run()
    run2._r.append(fld_end)

    page_break(doc)

    # ================= 第1章 绪论 =================
    chapter_title(doc, "第1章 绪论")
    section_title(doc, "1.1 研究背景与意义")
    body(doc, "学生管理是高校教务工作中最基础也最繁琐的一环。从新生入学建立学籍档案，到每学期"
              "编排班级、开设课程、组织选课，再到期末录入成绩、统计汇总，整个流程涉及学生、"
              "教师、教务人员和院系管理者等多类角色，数据量大、关联关系多、时效性要求高。"
              "不少学院的日常管理仍依赖 Excel 表格逐级下发和汇总，同一份学生名单在不同科室"
              "之间存在多个版本，学号、班级、课程等基础数据重复录入，既增加了工作量，也容易"
              "产生前后不一致的问题。")
    body(doc, "随着校园网络环境的完善，采用浏览器/服务器（B/S）架构的管理系统已经成为"
              "教务信息化的主流选择。B/S 系统不需要在客户端安装软件，教务人员、教师只要通过"
              "浏览器即可访问，维护和升级集中在服务器端完成。对于学生管理这一业务相对固定、"
              "用户数量中等的场景，选择成熟稳定的 Web 框架与前端组件库，可以在较短周期内"
              "完成系统开发，同时保证后续的可维护性。")
    body(doc, "本文所研究的系统面向某学院计算机信息学院的日常学生管理工作，目标是把分散在"
              "各类表格中的学生、班级、课程、成绩数据集中到统一的数据平台，通过角色划分和"
              "权限控制保证不同岗位人员只能访问职责范围内的数据，并保留完整的登录与操作"
              "记录，为教务管理提供可追溯的数据支撑。系统的实现对于减轻教务人员重复劳动、"
              "提高数据准确性和工作效率具有一定的现实意义。")

    section_title(doc, "1.2 国内外研究现状")
    body(doc, "国外高校信息化起步较早，学生信息系统（Student Information System，SIS）"
              "通常与学习管理系统（LMS）、财务系统深度集成，形成了较为完整的产品生态，"
              "如 Oracle 的 PeopleSoft Campus Solutions、Ellucian Banner 等。这类商业套件"
              "功能全面，但部署成本高、定制周期长，并不适合规模较小的院系直接使用。")
    body(doc, "国内高校近年来普遍建设了教务管理系统，多数采用 Java 技术栈或 PHP 技术栈"
              "进行二次开发。Java 方案生态完善但开发与部署相对沉重；PHP 方案上手快、"
              "部署简单，在中小规模系统中应用广泛。ThinkPHP 作为国内使用较多的 PHP 框架，"
              "提供了路由、ORM、中间件、模板引擎等完整能力；Layui 则是一套适合后台管理界面"
              "的国产前端组件库，组件风格统一、文档完善，配合 ThinkPHP 可以较快地搭建出"
              "功能完整的管理系统。已有不少毕业设计和中小项目采用这一组合，但多数实现侧重"
              "功能堆叠，在权限粒度、日志审计和部署便利性方面仍有改进空间。")

    section_title(doc, "1.3 研究内容")
    body(doc, "本文围绕学生管理系统的设计与实现展开，主要工作包括以下几个方面：")
    bullet(doc, "（1）分析学院学生管理业务的角色划分与功能需求，确定系统需要覆盖的学生、"
                "班级、院系、教师、课程、选课、成绩、日志等业务对象。")
    bullet(doc, "（2）设计基于 RBAC 模型的权限体系，将菜单权限与操作权限分离，实现按角色"
                "分配功能、按接口校验权限的控制粒度。")
    bullet(doc, "（3）基于 ThinkPHP6 框架完成系统后端开发，包括路由定义、控制器、模型、"
                "服务层与中间件，并以 Layui 组件库实现后台界面。")
    bullet(doc, "（4）设计并实现登录日志与操作日志，记录关键操作行为，为审计和问题排查提供依据。")
    bullet(doc, "（5）使用 Docker Compose 将 Nginx、PHP-FPM 与 MariaDB 编排为容器化环境，"
                "实现一键部署，降低系统上线和维护成本。")

    section_title(doc, "1.4 论文结构安排")
    body(doc, "全文共五章，各章内容安排如下：")
    body(doc, "第1章绪论，介绍课题的研究背景、国内外研究现状、研究内容以及论文的组织结构。")
    body(doc, "第2章需求分析，从可行性、功能需求和非功能需求三个方面对系统进行分析，"
              "明确系统的用户角色与功能边界。")
    body(doc, "第3章系统设计，给出系统的总体架构、功能模块划分、数据库设计和权限模型设计。")
    body(doc, "第4章系统实现，说明开发环境的搭建过程，并对登录认证、权限控制、学生管理、"
              "成绩管理等关键模块的实现方法进行阐述。")
    body(doc, "第5章系统测试与部署，介绍测试环境、测试用例设计与执行情况，说明系统的部署方式。")
    body(doc, "最后是总结与展望、致谢、参考文献和附录。")

    # ================= 第2章 需求分析 =================
    page_break(doc)
    chapter_title(doc, "第2章 需求分析")
    section_title(doc, "2.1 可行性分析")
    subsection_title(doc, "2.1.1 技术可行性")
    body(doc, "系统采用 B/S 架构，后端基于 ThinkPHP6 框架，前端基于 Layui 组件库，数据库"
              "选用 MariaDB，运行环境通过 Docker 容器编排。上述技术均为成熟的开源方案，"
              "社区资料丰富，ThinkPHP 与 Layui 的中文文档完善，开发过程中遇到的问题能够"
              "较快定位和解决。开发阶段使用 PHP 8.2、Nginx 1.27、MariaDB 11 的实际版本"
              "进行了联调验证，技术路线可行。")
    subsection_title(doc, "2.1.2 经济可行性")
    body(doc, "系统开发所使用的框架、组件库、数据库和容器工具均为免费开源软件，不需要"
              "支付授权费用。系统部署在一台普通服务器或教学用计算机上即可运行，对硬件"
              "要求不高，学院无需额外采购商业软件，经济上是可行的。")
    subsection_title(doc, "2.1.3 操作可行性")
    body(doc, "系统面向教务人员、教师和系统管理员，界面采用国内管理软件常见的左侧菜单加"
              "标签页布局，查询条件、数据表格和操作按钮的交互方式与办公软件接近，用户"
              "经过简单培训即可上手。系统同时提供演示账号，便于验收和答辩时直接演示。")

    section_title(doc, "2.2 用户角色分析")
    body(doc, "根据学院实际管理分工，系统将使用者划分为五类角色：")
    bullet(doc, "（1）超级管理员：拥有系统全部功能权限，负责账号、角色、权限的维护，"
                "以及院系、班级等基础数据的建立，登录后进入全校管理工作台。")
    bullet(doc, "（2）教务管理员：负责学生信息、班级信息、课程信息的日常维护，办理选课，"
                "录入和统计成绩，查看业务日志。")
    bullet(doc, "（3）辅导员：负责学生日常事务，审批学生请假、维护资助记录，可以查看"
                "学生、班级、课程与成绩信息，登录后进入学生事务工作台。")
    bullet(doc, "（4）教师：只能查看本人所授课程与相关学生，录入本人课程的成绩，登录后"
                "进入教学主页。")
    bullet(doc, "（5）学生：只能查看本人的课程、成绩、请假与资助记录，并可在系统中提交"
                "请假申请，登录后进入学生主页。")
    body(doc, "角色的权限通过角色管理模块配置，同一账号可以关联多个角色，最终权限取各角色"
              "权限的并集。超级管理员角色在系统中固定保留，不允许删除，避免出现无人可管理"
              "系统的情形。账号需要绑定学生或教师记录后，才能以学生或教师身份进入对应的"
              "工作台并访问本人数据。")

    section_title(doc, "2.3 功能需求分析")
    body(doc, "按照业务对象划分，系统需要实现以下功能模块：")
    subsection_title(doc, "2.3.1 账号与权限管理")
    body(doc, "账号管理支持账号的新增、编辑、删除、密码重置和角色分配，账号登录时校验"
              "状态与密码，连续五次密码错误将锁定十五分钟。角色管理维护角色名称、标识和"
              "状态，并通过权限树为角色分配菜单和操作权限。权限管理维护菜单与接口的对应"
              "关系，菜单类权限用于生成左侧导航，操作类权限用于接口级校验。")
    subsection_title(doc, "2.3.2 学生信息管理")
    body(doc, "学生信息包括学号、姓名、性别、出生日期、联系电话、邮箱、家庭住址、所属"
              "班级、入学日期和学籍状态等字段。支持按学号、姓名、电话模糊查询，按班级、"
              "性别、学籍状态筛选，支持新增、编辑、删除、批量删除、查看详情和导出 CSV "
              "名单。学生详情页同时展示该生的选课与成绩记录。")
    subsection_title(doc, "2.3.3 班级与院系管理")
    body(doc, "院系管理维护院系名称、代码、负责人和排序；班级管理维护班级名称、年级、"
              "所属院系、班主任和状态，并实时统计班级实际人数。删除院系前检查其下是否"
              "仍有班级，删除班级前检查其下是否仍有学生，防止产生孤立数据。")
    subsection_title(doc, "2.3.4 课程与教师管理")
    body(doc, "教师管理维护工号、姓名、性别、职称、联系电话、邮箱和所属院系，删除教师"
              "前检查其是否仍承担授课任务。课程管理维护课程编号、名称、学分、学时、课程"
              "类型、授课教师、开课院系和开课学期，列表中显示已选人数。")
    subsection_title(doc, "2.3.5 选课与成绩管理")
    body(doc, "选课管理支持教务人员为学生办理选课和退课，同一学生不能重复选择同一课程，"
              "退课采用状态标记而非物理删除，便于恢复。成绩管理支持单条录入和按课程批量"
              "录入，批量录入以“学号 成绩”的格式粘贴提交，系统按学号匹配选课记录；同时"
              "提供按课程汇总的平均分、最高分、最低分和及格率统计。")
    subsection_title(doc, "2.3.6 日志管理")
    body(doc, "登录日志记录每次登录的账号、时间、IP、浏览器信息和结果；操作日志通过中间件"
              "自动记录新增、修改、删除类请求的操作人、模块、动作、请求参数和执行结果，"
              "支持按关键字、模块、结果和日期范围检索，并提供查看详情与清空功能。")

    subsection_title(doc, "2.3.7 请假与资助管理")
    body(doc, "请假管理面向学生的日常请假事务。学生可填写请假类型、起止日期和事由提交"
              "申请，系统按起止日期计算请假天数；辅导员和管理员可以按班级、类型、状态"
              "检索请假记录，对申请进行批准或驳回，并填写审批意见，审批人与审批时间自动"
              "记录。资助管理由辅导员维护助学金、奖学金、困难补助和勤工助学等记录，"
              "包含学生、类型、学期、金额和状态等信息。")
    subsection_title(doc, "2.3.8 学生自助与角色工作台")
    body(doc, "学生登录后可以查看本人的课程成绩、请假记录和资助记录，并在“我的请假”"
              "中提交新的申请；教师登录后通过“我的授课”和“我的学生”查看本人承担的课程"
              "及相关学生；辅导员通过工作台直接处理待审批请假和资助事务；管理员则关注"
              "全校范围内的学生、班级、课程、成绩和日志数据。不同角色登录后看到的菜单、"
              "首页面板与可操作的数据范围均由服务端按角色权限决定。")

    section_title(doc, "2.4 非功能需求分析")
    body(doc, "除功能需求外，系统还应满足以下非功能要求：")
    bullet(doc, "（1）安全性：密码采用 MD5 加盐存储，登录失败次数达到阈值后临时锁定；"
                "所有需要登录的请求统一经中间件校验会话，权限不足的请求在服务端拦截，"
                "不能通过直接输入地址绕过。")
    bullet(doc, "（2）可靠性：数据库操作使用框架的事务与软删除机制，误删除的数据可以恢复；"
                "关键数据表设置唯一索引，防止学号、工号、课程编号重复。")
    bullet(doc, "（3）易用性：列表页统一提供查询、重置、分页和批量操作；表单校验在提交前"
                "于前端完成，服务端再做二次校验。")
    bullet(doc, "（4）可维护性：后端按控制器、服务、模型分层组织，权限、日志、认证等通用"
                "能力抽取为服务类和中间件，便于后续扩展。")
    bullet(doc, "（5）可部署性：提供 Docker Compose 编排文件，执行一条命令即可完成数据库"
                "初始化与服务启动。")
    bullet(doc, "（6）角色适配：不同角色登录后进入不同的工作台，菜单与可访问数据范围按"
                "角色权限自动调整，避免无关功能干扰操作。")

    section_title(doc, "2.5 本章小结")
    body(doc, "本章从技术、经济和操作三个方面论证了系统开发的可行性，划分了超级管理员、"
              "教务管理员、辅导员、教师和学生五类用户角色，梳理了账号权限、学生、班级院系、"
              "课程教师、选课成绩、请假资助和日志等功能需求，并提出了安全性、可靠性、易用性"
              "和角色适配等方面的非功能要求，为后续的系统设计提供了依据。")

    # ================= 第3章 系统设计 =================
    page_break(doc)
    chapter_title(doc, "第3章 系统设计")
    section_title(doc, "3.1 总体架构设计")
    body(doc, "系统采用 B/S 架构，整体分为表示层、业务逻辑层、数据访问层和数据存储层四层。"
              "表示层由 Layui 组件库构建的页面组成，负责数据展示与交互；业务逻辑层由"
              "ThinkPHP6 的控制器与服务类承担，负责参数校验、业务规则处理和权限判定；"
              "数据访问层基于 ThinkPHP 内置的 ORM 实现；数据存储层使用 MariaDB。")
    body(doc, "用户在浏览器中访问系统时，请求先经过 Nginx，由 Nginx 将 PHP 请求转发给"
              "PHP-FPM 处理，静态资源由 Nginx 直接返回。应用内部通过中间件管道依次完成"
              "会话初始化、登录校验、权限校验和操作日志记录，最后进入控制器执行具体业务。"
              "系统总体结构如图3.1所示。")
    add_picture(doc, os.path.join(os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs")),
                                 "figures", "arch.png"), 13.5,
                "图3.1 系统总体架构")

    section_title(doc, "3.2 功能模块设计")
    body(doc, "系统功能按模块划分为账号管理、角色管理、权限管理、学生管理、班级管理、"
              "院系管理、教师管理、课程管理、选课管理、成绩管理、请假管理、资助管理、"
              "登录日志和操作日志十四个业务模块，归入系统管理、学生管理、教务管理、"
              "日志管理四个导航分组；同时为学生提供我的成绩、我的请假、我的资助三个"
              "自助页面，为教师提供我的授课、我的学生两个教学页面。系统功能结构如"
              "图3.2所示。")
    add_picture(doc, os.path.join(os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs")),
                                 "figures", "modules.png"), 14, "图3.2 系统功能结构")

    section_title(doc, "3.3 数据库设计")
    subsection_title(doc, "3.3.1 概念结构设计")
    body(doc, "系统的核心实体包括账号、角色、权限、院系、班级、学生、教师、课程、选课记录、"
              "请假、资助和日志。账号与角色之间为多对多关系，一个账号可以拥有多个角色，一个"
              "角色可以分配给多个账号；角色与权限之间同样为多对多关系。院系与班级、班级与"
              "学生、教师与课程均为一对多关系。学生与课程之间通过选课记录建立多对多联系，"
              "选课记录上附加成绩和学期属性；学生与请假、资助记录之间为一对多关系。主要"
              "实体关系如图3.3所示。")
    add_picture(doc, os.path.join(os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs")),
                                 "figures", "er.png"), 14, "图3.3 系统主要实体关系")

    subsection_title(doc, "3.3.2 逻辑结构设计")
    body(doc, "系统数据库命名为 student_ms，共设计 15 张数据表，表名前缀统一为 ms_。"
              "各表结构如表3.1 至表3.15 所示。")

    tables = [
        ("表3.1 账号表（ms_admin）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["username", "varchar(30)", "否", "登录账号，唯一"],
          ["password", "char(32)", "否", "MD5 加密后的密码"],
          ["salt", "varchar(32)", "否", "密码盐值"],
          ["nickname", "varchar(30)", "否", "姓名"],
          ["email", "varchar(60)", "是", "邮箱"],
          ["phone", "varchar(20)", "是", "联系电话"],
          ["sex", "tinyint", "否", "性别：0 未知 1 男 2 女"],
          ["status", "tinyint", "否", "状态：1 正常 0 停用"],
          ["login_fail", "int", "否", "连续登录失败次数"],
          ["last_login", "int", "是", "最后登录时间戳"],
          ["last_login_ip", "varchar(46)", "是", "最后登录 IP"],
          ["create_time", "int", "否", "创建时间戳"],
          ["update_time", "int", "否", "更新时间戳"],
          ["delete_time", "int", "是", "软删除时间戳"]]),
        ("表3.2 角色表（ms_role）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["name", "varchar(30)", "否", "角色名称"],
          ["code", "varchar(30)", "否", "角色标识，唯一"],
          ["remark", "varchar(200)", "是", "备注"],
          ["status", "tinyint", "否", "状态：1 启用 0 停用"],
          ["sort", "int", "否", "排序值"],
          ["create_time", "int", "否", "创建时间戳"],
          ["update_time", "int", "否", "更新时间戳"]]),
        ("表3.3 权限表（ms_auth）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["pid", "int", "否", "上级权限 ID，0 为顶级"],
          ["name", "varchar(50)", "否", "权限名称"],
          ["type", "tinyint", "否", "类型：1 菜单 2 操作"],
          ["path", "varchar(120)", "否", "路由地址"],
          ["icon", "varchar(50)", "是", "菜单图标"],
          ["sort", "int", "否", "排序值"],
          ["status", "tinyint", "否", "状态：1 启用 0 停用"],
          ["create_time", "int", "否", "创建时间戳"],
          ["update_time", "int", "否", "更新时间戳"]]),
        ("表3.4 账号角色关联表（ms_admin_role）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["admin_id", "int", "否", "账号 ID"],
          ["role_id", "int", "否", "角色 ID"]]),
        ("表3.5 角色权限关联表（ms_role_auth）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["role_id", "int", "否", "角色 ID"],
          ["auth_id", "int", "否", "权限 ID"]]),
        ("表3.6 院系表（ms_department）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["name", "varchar(50)", "否", "院系名称，唯一"],
          ["code", "varchar(20)", "是", "院系代码"],
          ["leader", "varchar(30)", "是", "负责人"],
          ["sort", "int", "否", "排序值"],
          ["status", "tinyint", "否", "状态：1 启用 0 停用"],
          ["create_time", "int", "否", "创建时间戳"],
          ["update_time", "int", "否", "更新时间戳"]]),
        ("表3.7 班级表（ms_class）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["name", "varchar(50)", "否", "班级名称"],
          ["grade", "varchar(20)", "是", "年级"],
          ["department_id", "int", "否", "所属院系 ID"],
          ["head_teacher", "varchar(30)", "是", "班主任"],
          ["student_num", "int", "否", "班级人数"],
          ["status", "tinyint", "否", "状态：1 在读 0 毕业"],
          ["create_time", "int", "否", "创建时间戳"],
          ["update_time", "int", "否", "更新时间戳"]]),
        ("表3.8 学生表（ms_student）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["student_no", "varchar(20)", "否", "学号，唯一"],
          ["name", "varchar(30)", "否", "姓名"],
          ["gender", "tinyint", "否", "性别：0 未知 1 男 2 女"],
          ["birthday", "date", "是", "出生日期"],
          ["phone", "varchar(20)", "是", "联系电话"],
          ["email", "varchar(60)", "是", "邮箱"],
          ["address", "varchar(200)", "是", "家庭住址"],
          ["class_id", "int", "否", "所属班级 ID"],
          ["enroll_date", "date", "是", "入学日期"],
          ["status", "tinyint", "否", "学籍状态：1 在读 0 休学 2 毕业 3 退学"],
          ["remark", "varchar(500)", "是", "备注"],
          ["create_time", "int", "否", "创建时间戳"],
          ["update_time", "int", "否", "更新时间戳"],
          ["delete_time", "int", "是", "软删除时间戳"]]),
        ("表3.9 教师表（ms_teacher）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["teacher_no", "varchar(20)", "否", "工号，唯一"],
          ["name", "varchar(30)", "否", "姓名"],
          ["gender", "tinyint", "否", "性别"],
          ["title", "varchar(30)", "是", "职称"],
          ["phone", "varchar(20)", "是", "联系电话"],
          ["email", "varchar(60)", "是", "邮箱"],
          ["department_id", "int", "是", "所属院系 ID"],
          ["create_time", "int", "否", "创建时间戳"],
          ["update_time", "int", "否", "更新时间戳"]]),
        ("表3.10 课程表（ms_course）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["course_no", "varchar(20)", "否", "课程编号，唯一"],
          ["name", "varchar(60)", "否", "课程名称"],
          ["credit", "decimal(3,1)", "否", "学分"],
          ["hours", "int", "否", "学时"],
          ["course_type", "tinyint", "否", "类型：1 必修 2 选修 3 公共"],
          ["teacher_id", "int", "是", "授课教师 ID"],
          ["department_id", "int", "是", "开课院系 ID"],
          ["semester", "varchar(20)", "否", "开课学期"],
          ["status", "tinyint", "否", "状态：1 开课 0 停开"],
          ["remark", "varchar(500)", "是", "课程简介"],
          ["create_time", "int", "否", "创建时间戳"],
          ["update_time", "int", "否", "更新时间戳"]]),
        ("表3.11 选课表（ms_select_course）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["student_id", "int", "否", "学生 ID"],
          ["course_id", "int", "否", "课程 ID"],
          ["semester", "varchar(20)", "否", "学期"],
          ["score", "decimal(4,1)", "是", "成绩"],
          ["status", "tinyint", "否", "状态：1 已选 0 已退"],
          ["create_time", "int", "否", "创建时间戳"],
          ["update_time", "int", "否", "更新时间戳"]]),
        ("表3.12 登录日志表（ms_login_log）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["admin_id", "int", "否", "账号 ID"],
          ["username", "varchar(30)", "否", "登录账号"],
          ["ip", "varchar(46)", "否", "登录 IP"],
          ["user_agent", "varchar(200)", "是", "浏览器信息"],
          ["status", "tinyint", "否", "结果：1 成功 0 失败"],
          ["msg", "varchar(200)", "是", "提示信息"],
          ["login_time", "int", "否", "登录时间戳"]]),
        ("表3.13 操作日志表（ms_operation_log）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["admin_id", "int", "否", "操作人 ID"],
          ["username", "varchar(30)", "否", "操作人账号"],
          ["module", "varchar(50)", "否", "功能模块"],
          ["action", "varchar(50)", "否", "操作动作"],
          ["method", "varchar(120)", "否", "请求方法"],
          ["url", "varchar(200)", "否", "请求地址"],
          ["ip", "varchar(46)", "否", "操作 IP"],
          ["params", "text", "是", "请求参数"],
          ["result", "text", "是", "返回结果摘要"],
          ["status", "tinyint", "否", "结果：1 成功 0 失败"],
          ["error_msg", "varchar(500)", "是", "错误信息"],
          ["operate_time", "int", "否", "操作时间戳"]]),
        ("表3.14 请假表（ms_leave）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["student_id", "int", "否", "学生 ID"],
          ["type", "tinyint", "否", "类型：1 事假 2 病假 3 其他"],
          ["start_date", "date", "否", "开始日期"],
          ["end_date", "date", "否", "结束日期"],
          ["days", "decimal(4,1)", "否", "请假天数"],
          ["reason", "varchar(500)", "否", "请假事由"],
          ["status", "tinyint", "否", "状态：0 待审批 1 已批准 2 已驳回"],
          ["approver", "varchar(30)", "是", "审批人"],
          ["approve_time", "int", "是", "审批时间戳"],
          ["remark", "varchar(200)", "是", "审批意见"],
          ["create_time", "int", "否", "创建时间戳"],
          ["update_time", "int", "否", "更新时间戳"]]),
        ("表3.15 资助表（ms_aid）",
         ["字段名", "类型", "允许空", "说明"],
         [["id", "int", "否", "主键，自增"],
          ["student_id", "int", "否", "学生 ID"],
          ["type", "tinyint", "否", "类型：1 助学金 2 奖学金 3 困难补助 4 勤工助学"],
          ["semester", "varchar(20)", "否", "学期"],
          ["amount", "decimal(10,2)", "否", "资助金额"],
          ["status", "tinyint", "否", "状态：0 申请中 1 已通过 2 未通过"],
          ["remark", "varchar(200)", "是", "备注"],
          ["create_time", "int", "否", "创建时间戳"],
          ["update_time", "int", "否", "更新时间戳"]]),
    ]

    for cap, headers, rows in tables:
        caption(doc, cap)
        make_table(doc, headers, rows, widths=[3.4, 3.6, 2.0, 7.0], font_size=9)
        para(doc, "", size=9, line=1.0, after=6)

    section_title(doc, "3.4 权限模型设计")
    body(doc, "系统采用 RBAC（Role-Based Access Control，基于角色的访问控制）模型，"
              "将权限赋予角色、角色赋予账号，账号不直接持有权限。权限表 ms_auth 中通过"
              "type 字段区分两类权限：type=1 的菜单权限对应左侧导航中的一个节点，其 path "
              "字段为该菜单的访问地址；type=2 的操作权限对应一个具体的接口地址，如"
              "student/list、student/save。菜单权限按 pid 组织为两级树结构，一级为导航"
              "分组，二级为功能菜单。")
    body(doc, "权限校验分为两层。第一层在菜单生成时执行：根据当前账号的角色查询其拥有的"
              "菜单权限，再把菜单所属的上级目录一并补入，组装为导航树返回给前端。第二层"
              "在接口访问时执行：CheckPermission 中间件读取当前请求路径，在权限表中查找"
              "对应记录，若存在则检查当前账号是否拥有该权限，不存在则放行。页面级路由"
              "（如 student）在权限表中没有独立记录，中间件会转而校验其对应的查看权限"
              "（student/list），从而保证“页面上能打开”与“接口能调用”共用同一套权限数据。")
    body(doc, "在上述权限体系之上，系统为不同角色提供不同的工作台：管理员与教务管理员"
              "进入全校管理工作台，辅导员进入学生事务工作台，教师进入教学主页，学生进入"
              "学生主页。工作台的选择由服务端依据当前账号的角色标识决定，页面数据接口"
              "同样按角色过滤：教师只能获取本人所授课程及选课学生，学生只能获取本人"
              "绑定的学生记录。")
    body(doc, "为防止越权，账号列表、学生列表等接口在返回数据前均以当前登录账号的权限"
              "范围为准；删除账号时禁止删除超级管理员账号，删除角色时禁止删除超级管理员"
              "角色，并检查角色下是否仍有账号关联。")

    section_title(doc, "3.5 本章小结")
    body(doc, "本章完成了系统的总体设计。首先给出了系统的四层架构以及请求在 Nginx、"
              "PHP-FPM 和应用中间件之间的处理流程；其次按业务对象划分了十四个功能模块，"
              "并给出了面向五类角色的工作台划分；然后设计了包含 15 张数据表的数据库结构，"
              "并逐表说明了字段含义；最后阐述了基于 RBAC 的两级权限校验方案与按角色过滤"
              "数据范围的实现思路，为第4章的系统实现奠定了基础。")

    # ================= 第4章 系统实现 =================
    page_break(doc)
    chapter_title(doc, "第4章 系统实现")
    section_title(doc, "4.1 开发环境搭建")
    body(doc, "系统开发在 Linux 环境下进行，使用 Visual Studio Code 作为编辑器。为了"
              "避免不同机器上 PHP 版本和扩展差异带来的问题，开发与运行环境统一采用 "
              "Docker 容器化方式搭建，使用 Docker Compose 编排三个服务：nginx 服务负责"
              "静态资源与请求转发，php 服务基于 php:8.2-fpm 镜像构建并安装 pdo_mysql、"
              "gd 等扩展，mariadb 服务提供数据库。三个服务位于同一自定义网络，nginx "
              "通过服务名 php 访问 PHP-FPM。开发环境的主要配置如表4.1 所示。")
    caption(doc, "表4.1 开发环境配置")
    make_table(doc,
               ["项目", "版本/说明"],
               [["操作系统", "Arch Linux"],
                ["Web 服务器", "Nginx 1.27（nginx:alpine 镜像）"],
                ["PHP", "8.2（php:8.2-fpm 镜像）"],
                ["数据库", "MariaDB 11"],
                ["后端框架", "ThinkPHP 6.1.5"],
                ["前端组件库", "Layui 2.9.21"],
                ["容器编排", "Docker Compose"],
                ["浏览器", "Chrome / Firefox"]],
               widths=[4.5, 9.0], font_size=10)
    para(doc, "", size=9, line=1.0, after=6)

    body(doc, "数据库初始化脚本 database/student_ms.sql 随容器首次启动自动执行，完成"
              "建库建表并写入基础数据。项目根目录下的 docker-compose.yml 定义了三个服务"
              "的镜像、卷挂载、端口映射和依赖关系，执行 docker compose up -d 即可启动"
              "完整运行环境，浏览器访问 http://localhost:8080 进入登录页。")

    section_title(doc, "4.2 登录与认证模块")
    subsection_title(doc, "4.2.1 登录流程")
    body(doc, "登录页面由 Layui 表单组件构建，用户提交账号和密码后，前端通过 Ajax 将"
              "数据提交至 /login/do 接口。服务端 LoginController 接收请求后先做频率限制，"
              "同一 IP 在十秒内只允许发起一次登录请求，防止暴力尝试；随后调用 "
              "AuthService::login 完成账号查找、状态检查、锁定检查和密码校验。")
    body(doc, "密码采用 MD5 加盐的方式存储，数据库中保存的是 md5(salt + password) 的"
              "结果，盐值在创建账号时随机生成。登录成功后，账号 ID、账号名、姓名和登录"
              "时间写入 Session，同时向登录日志表写入一条成功记录；登录失败则累计失败"
              "次数，连续失败达到五次后账号锁定十五分钟，锁定期内即使密码正确也会被拒绝。")
    subsection_title(doc, "4.2.2 会话与登录校验")
    body(doc, "系统在应用级中间件中注册了 SessionInit、CheckLogin、CheckPermission 和 "
              "OperationLog 四个中间件，所有请求依次经过这条管道。CheckLogin 中间件读取"
              "Session 中的账号 ID，未登录时对页面请求返回 302 跳转至登录页，对 Ajax 和 "
              "POST 请求返回统一格式的 JSON 提示，前端收到提示后跳转登录页。登录页、登录"
              "接口和退出接口被列入白名单，不经过登录校验。")
    subsection_title(doc, "4.2.3 界面实现")
    body(doc, "登录页采用上下结构，顶部为系统标识条，中部为登录卡片，底部为学院与班级"
              "信息。登录页面如图4.1 所示。登录成功后进入后台主框架，框架页左侧为根据"
              "当前账号权限动态生成的导航菜单，右侧为多标签页内容区，顶部显示折叠菜单、"
              "刷新、全屏和账号下拉菜单等操作入口。后台主界面如图4.2 所示。")
    add_picture(doc, os.path.join(os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs")),
                                 "figures", "login.png"), 14, "图4.1 系统登录页面")
    add_picture(doc, os.path.join(os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs")),
                                 "figures", "frame.png"), 14, "图4.2 后台主界面")

    section_title(doc, "4.3 权限控制模块")
    body(doc, "权限控制模块由角色管理、权限管理和权限中间件三部分构成。角色管理页面"
              "以列表形式展示角色名称、标识、账号数、权限数和状态，通过“分配权限”操作"
              "打开权限分配面板。权限分配面板从 /auth/list 接口获取完整的权限树，再从"
              "/role/auths 接口获取该角色已分配的权限 ID，递归生成带勾选框的权限列表，"
              "保存时调用 /role/assign 接口批量重建角色与权限的关联。")
    body(doc, "权限管理页面以缩进列表的形式展示菜单和操作两级权限，支持新增、编辑和删除。"
              "为防止权限树出现环，编辑时校验新的上级权限不能是自身的下级节点；删除时"
              "检查该权限是否还有下级，有下级则不允许删除。")
    body(doc, "接口级权限由 CheckPermission 中间件执行。中间件首先放行登录、退出和控制台"
              "等公共路径；随后判断当前账号是否拥有请求路径对应的权限。若路径在权限表"
              "中存在对应记录，则要求账号权限集合包含该记录；若路径是页面路由，则检查其"
              "对应的查看权限；若路径不在权限表中，视为业务内部接口直接放行。权限不足时，"
              "页面请求返回无权访问提示页，接口请求返回统一格式的 JSON 错误信息。")

    section_title(doc, "4.4 学生信息管理模块")
    body(doc, "学生信息管理是系统的核心模块，页面由查询区、工具栏和数据表格三部分组成。"
              "查询区提供关键字、班级、性别和学籍状态四个条件，工具栏提供新增、批量删除"
              "和导出三个操作，数据表格按页展示学号、姓名、性别、班级、联系电话和学籍"
              "状态，每行提供详情、编辑、删除三个操作按钮。学生列表页面如图4.3 所示。")
    add_picture(doc, os.path.join(os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs")),
                                 "figures", "student.png"), 14, "图4.3 学生列表页面")

    body(doc, "列表数据通过 /student/list 接口分页获取。接口在服务端拼接查询条件，使用"
              "模型的 whereLike 与 whereOr 方法构造模糊查询，分页采用 page 方法，返回"
              "Layui 数据表格约定的 code、count、data 结构。为便于列表展示班级名称，"
              "接口在返回前一次性查询班级 ID 与名称的对应关系，在内存中完成关联，避免"
              "逐行查询造成 N+1 问题。")
    body(doc, "新增和编辑共用一个表单弹层，表单包含学号、姓名、性别、出生日期、所属班级、"
              "入学日期、联系电话、邮箱、学籍状态、家庭住址和备注字段。提交前在前端进行"
              "必填校验，服务端再做学号格式、邮箱格式、学号唯一性和班级必选等校验。保存"
              "成功后同步更新所属班级的人数。")
    body(doc, "删除操作采用框架的软删除机制，删除时只写入 delete_time 字段，数据仍保留"
              "在数据库中，必要时可以恢复。批量删除先遍历选中的学生取得其班级 ID，删除"
              "完成后再逐班刷新人数统计。学生详情通过 /student/detail 接口获取，弹层中"
              "以描述表的形式展示基本信息，并附上该生的选课与成绩列表。导出功能将筛选"
              "结果以 CSV 格式输出，文件带 UTF-8 BOM 头，用 Excel 打开不会出现中文乱码。")

    section_title(doc, "4.5 课程、选课与成绩模块")
    body(doc, "课程管理维护课程编号、名称、学分、学时、类型、授课教师、开课院系和开课"
              "学期，列表显示每门课程当前的选课人数。删除课程时检查是否仍有处于已选"
              "状态的学生，有则拒绝删除，已退课的记录不影响删除。")
    body(doc, "选课管理页面按学号、姓名或课程名称检索选课记录，展示学生、课程、学分、"
              "学期、成绩、等级和状态。办理选课时在弹出的表单中选择学生和课程，服务端"
              "校验学生与课程是否存在，若已有同学生同课程的记录且处于已选状态则拒绝，"
              "若是已退课的历史记录则将其恢复为已选状态，避免同一学生同一课程产生多条"
              "记录。退课只把记录状态改为已退，成绩随之不再计入统计。")
    body(doc, "成绩管理支持单条录入和批量录入两种方式。单条录入点击“录入”按钮弹出"
              "输入框，提交后校验成绩是否在 0 到 100 之间；批量录入按“学号 成绩”的"
              "格式粘贴多行文本，前端逐行解析为学号与成绩的映射，服务端根据课程 ID "
              "和学号定位选课记录并批量更新，最后返回成功条数和未匹配的记录数。成绩"
              "统计按课程汇总参与人数、平均分、最高分、最低分和及格率，以弹层表格展示。"
              "成绩管理页面如图4.4 所示。")
    add_picture(doc, os.path.join(os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs")),
                                 "figures", "score.png"), 14, "图4.4 成绩管理页面")

    section_title(doc, "4.6 日志模块")
    body(doc, "登录日志在每次登录成功或失败时写入，记录账号、IP、浏览器信息、结果和"
              "提示信息，页面支持按账号或 IP 检索、按结果筛选和按日期范围查询。操作日志"
              "由 OperationLog 中间件自动产生：中间件判断请求方法和所属模块，对账号、"
              "角色、权限、学生、班级、院系、教师、课程、选课、成绩等模块的写操作，在"
              "请求执行完成后记录操作人、模块与动作、请求地址、请求参数、执行结果和"
              "错误信息。为避免密码等敏感信息落库，写日志前对参数中的密码、令牌一类"
              "字段做了掩码处理。操作日志页面如图4.5 所示。")
    add_picture(doc, os.path.join(os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs")),
                                 "figures", "log.png"), 14, "图4.5 操作日志页面")

    section_title(doc, "4.7 请假与资助模块")
    body(doc, "请假与资助模块面向学生日常事务。学生端在“我的请假”页面填写请假类型、"
              "起止日期和事由后提交，服务端校验日期格式与先后顺序，按起止日期计算请假"
              "天数并将记录置为待审批状态；辅导员在“请假管理”页面按学号姓名、类型和"
              "状态检索记录，对申请执行批准或驳回，系统自动记录审批人和审批时间。"
              "资助管理页面由辅导员维护助学金、奖学金、困难补助和勤工助学等记录，"
              "学生可在“我的资助”页面查看本人资助情况。请假管理页面如图4.6 所示。")
    add_picture(doc, os.path.join(os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs")),
                                 "figures", "leave.png"), 14, "图4.6 请假管理页面")

    section_title(doc, "4.8 角色化工作台")
    body(doc, "系统在用户登录后依据账号角色选择不同的工作台。管理员与教务管理员进入"
              "全校管理工作台，查看学生、班级、课程、成绩、日志等全局数据；辅导员进入"
              "学生事务工作台，首页直接列出待审批请假和最近资助记录，可以在面板内完成"
              "审批；教师进入教学主页，只显示本人所授课程、授课学生和待录入成绩，成绩"
              "管理接口在服务端再次校验课程归属，避免教师操作他人课程；学生进入学生"
              "主页，展示本人课程、学分、平均成绩、请假和资助情况，并可发起请假申请。"
              "学生工作台和辅导员工作台分别如图4.7、图4.8 所示。")
    add_picture(doc, os.path.join(os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs")),
                                 "figures", "student_panel.png"), 14, "图4.7 学生工作台")
    add_picture(doc, os.path.join(os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs")),
                                 "figures", "counselor_panel.png"), 14, "图4.8 辅导员工作台")

    section_title(doc, "4.9 本章小结")
    body(doc, "本章介绍了系统的具体实现。首先说明了基于 Docker 的开发环境搭建方法和"
              "一键启动方式；然后依次阐述了登录认证、权限控制、学生信息管理、课程选课"
              "成绩管理、日志、请假资助和角色化工作台的实现过程，包括前后端交互方式、"
              "服务端校验规则、按角色过滤数据的处理逻辑。")

    # ================= 第5章 系统测试与部署 =================
    page_break(doc)
    chapter_title(doc, "第5章 系统测试与部署")
    section_title(doc, "5.1 测试环境与方法")
    body(doc, "系统测试在与开发环境一致的容器环境中进行，测试地址为 "
              "http://localhost:8080。测试采用黑盒测试方法，以需求分析阶段确定的功能"
              "点为依据设计测试用例，通过手工操作页面和调用接口两种方式验证功能；"
              "对权限控制部分，分别使用超级管理员和教师账号登录，验证不同角色访问同一"
              "功能时的表现。测试数据使用系统初始化脚本中的示例数据和测试过程中新增的"
              "临时数据。")

    section_title(doc, "5.2 功能测试")
    body(doc, "登录与权限相关的测试用例如表5.1 所示。")
    caption(doc, "表5.1 登录与权限测试用例")
    make_table(doc,
               ["编号", "测试内容", "操作步骤", "预期结果", "结果"],
               [["T01", "正确账号登录", "输入 admin/admin123 提交", "提示登录成功并进入后台", "通过"],
                ["T02", "错误密码登录", "输入 admin/123 提交", "提示账号或密码错误", "通过"],
                ["T03", "停用账号登录", "将 teacher 状态改为停用后登录", "提示账号已被停用", "通过"],
                ["T04", "连续错误密码锁定", "连续五次输入错误密码", "提示锁定并给出剩余分钟数", "通过"],
                ["T05", "未登录访问页面", "清除会话后访问 /student", "跳转至登录页", "通过"],
                ["T06", "教师访问账号管理", "以 teacher 登录后访问 /admin", "显示无权访问提示页", "通过"],
                ["T07", "教师访问成绩管理", "以 teacher 登录后访问 /score", "正常打开成绩列表", "通过"],
                ["T08", "教师调用删除接口", "以 teacher 身份 POST /student/delete", "返回无权限的 JSON 提示", "通过"]],
               widths=[1.4, 4.0, 5.2, 5.0, 1.4], font_size=9)
    para(doc, "", size=9, line=1.0, after=6)

    body(doc, "学生与教务管理相关的测试用例如表5.2 所示。")
    caption(doc, "表5.2 业务功能测试用例")
    make_table(doc,
               ["编号", "测试内容", "操作步骤", "预期结果", "结果"],
               [["T09", "新增学生", "填写完整表单后保存", "列表出现新学生，班级人数加一", "通过"],
                ["T10", "学号重复", "使用已存在的学号新增", "提示学号已存在", "通过"],
                ["T11", "学号格式非法", "学号输入“12”", "提示学号格式不正确", "通过"],
                ["T12", "条件查询", "按班级和性别组合查询", "只返回符合条件的学生", "通过"],
                ["T13", "批量删除学生", "勾选多名学生后批量删除", "列表数量相应减少", "通过"],
                ["T14", "导出名单", "点击导出按钮", "下载 CSV 文件且中文正常", "通过"],
                ["T15", "删除有学生的班级", "删除下有学生的班级", "提示仍有学生，无法删除", "通过"],
                ["T16", "重复选课", "对同一学生同一课程再次选课", "提示不能重复选课", "通过"],
                ["T17", "退课后重新选课", "对已退课的记录重新办理选课", "原记录恢复为已选", "通过"],
                ["T18", "批量录入成绩", "粘贴“学号 成绩”多行文本提交", "提示保存成功条数", "通过"],
                ["T19", "成绩越界", "录入 120 分", "提示取值范围为 0 到 100", "通过"],
                ["T20", "成绩统计", "点击成绩统计按钮", "显示各课程平均分与及格率", "通过"],
                ["T21", "清空操作日志", "确认清空后查看列表", "列表为空且记录清空动作", "通过"]],
               widths=[1.4, 4.2, 5.2, 4.8, 1.4], font_size=9)
    para(doc, "", size=9, line=1.0, after=6)

    body(doc, "角色与数据范围相关的测试用例如表5.3 所示。")
    caption(doc, "表5.3 角色化功能测试用例")
    make_table(doc,
               ["编号", "测试内容", "操作步骤", "预期结果", "结果"],
               [["T22", "学生查看本人数据", "以学生账号登录，打开我的成绩、请假、资助", "仅显示本人记录", "通过"],
                ["T23", "学生越权访问", "学生直接访问 /leave 或 /admin", "显示无权访问提示", "通过"],
                ["T24", "教师数据范围", "教师登录后打开成绩管理与授课页面", "课程与记录仅含本人所授课程", "通过"],
                ["T25", "辅导员审批请假", "在学生事务面板批准一条待审批请假", "状态变为已批准并记录审批人", "通过"]],
               widths=[1.4, 4.2, 5.2, 4.8, 1.4], font_size=9)
    para(doc, "", size=9, line=1.0, after=6)

    section_title(doc, "5.3 测试结果分析")
    body(doc, "上述 25 个测试用例全部通过。功能层面，各模块的新增、编辑、删除、查询、"
              "分页和批量操作均按预期工作，关键字段的服务端校验能够拦截非法数据，班级"
              "人数、选课人数等统计值在数据变化后保持同步。权限层面，教师账号只能看到"
              "本人课程的数据，学生账号只能访问本人记录，直接输入未授权地址会被拦截，"
              "通过 POST 直接调用未授权接口也会被拒绝，说明权限校验在服务端确实生效，"
              "而不是仅依靠前端隐藏按钮。")
    body(doc, "测试过程中也发现并修复了一些问题：例如多标签页框架中首次加载控制台时"
              "内容区高度计算有误，已通过调整布局样式解决；成绩批量录入最初以选课记录"
              "ID 作为匹配键，实际使用中学号更便于操作，已将匹配方式改为按学号查找；"
              "班级人数在学生删除后未及时更新，已在删除逻辑中补充同步。修复后回归测试"
              "全部通过。")

    section_title(doc, "5.4 系统部署")
    body(doc, "系统采用 Docker Compose 部署，部署步骤分为三步：第一步，将项目代码"
              "复制到服务器并安装 Docker 与 Docker Compose；第二步，根据需要修改 "
              "docker-compose.yml 中的端口映射和数据库密码，并确认 .env 文件中的"
              "数据库连接参数与容器配置一致；第三步，在项目根目录执行 "
              "docker compose up -d 启动服务。首次启动时数据库容器会自动执行初始化"
              "脚本，创建数据库、数据表并写入示例数据，PHP 容器启动时自动修正运行"
              "目录权限。启动完成后通过服务器的 8080 端口即可访问系统。")
    body(doc, "更新系统时，拉取新代码后执行 docker compose restart php 使 PHP 重新"
              "加载代码即可，数据库数据保存在命名卷 mariadb-data 中，不会因容器重建"
              "而丢失。如需备份数据库，可在宿主机执行 mysqldump 命令导出 SQL 文件。")

    section_title(doc, "5.5 本章小结")
    body(doc, "本章介绍了系统的测试与部署情况。设计了覆盖登录权限、业务功能和角色"
              "数据范围的 25 个测试用例并逐一执行，测试结果表明系统功能完整、权限控制"
              "有效。同时说明了基于 Docker Compose 的部署步骤和日常维护方法。")

    # ================= 总结与展望 =================
    page_break(doc)
    chapter_title(doc, "总结与展望")
    section_title(doc, "一、总结")
    body(doc, "本文围绕学院学生管理工作的实际需要，设计并实现了一套基于 ThinkPHP6 与 "
              "Layui 的学生管理系统。系统采用 B/S 架构，以 MariaDB 存储数据，按控制器、"
              "服务、模型分层组织后端代码，使用中间件统一处理会话、权限和日志；前端"
              "基于 Layui 组件库实现了列表、表单、弹层和分页等交互。系统覆盖账号权限、"
              "学生、班级院系、教师课程、选课成绩、请假资助和日志七类业务，实现了 15 张"
              "数据表的设计与维护，并通过 RBAC 模型实现了菜单级和接口级的双重权限控制，"
              "同时为管理员、辅导员、教师和学生提供了各自的工作台与数据范围。系统使用 "
              "Docker Compose 完成了容器化部署，一条命令即可搭建完整运行环境。")
    body(doc, "通过本次毕业设计，本人对 Web 应用从需求分析、数据库设计到编码实现的完整"
              "流程有了系统的认识，对 ThinkPHP 的中间件机制、ORM 的关联查询与软删除、"
              "RBAC 权限模型以及 Docker 容器编排有了更深入的理解，动手能力和问题排查"
              "能力得到了锻炼。")
    section_title(doc, "二、今后研究方向")
    body(doc, "系统目前仍有进一步完善的空间。一是可以引入学生端和教师端的自助功能，"
              "让学生在线查询成绩、教师在线录入成绩，减少教务人员的中间操作；二是可以"
              "补充数据可视化看板，将班级人数、成绩分布等数据以图表形式呈现；三是可以"
              "增加批量导入学生名单、成绩单打印导出等实用功能；四是可以引入缓存和接口"
              "限流机制，提升系统在高并发场景下的稳定性。")

    # ================= 致谢 =================
    page_break(doc)
    chapter_title(doc, "致　谢")
    body(doc, "本论文是在指导教师毕柱兰老师的悉心指导下完成的。从选题、需求分析、系统"
              "设计到论文撰写，毕老师都给予了耐心的指导和细致的修改意见，在此表示衷心的"
              "感谢。毕老师严谨的治学态度和认真负责的工作作风，使我受益匪浅。")
    body(doc, "感谢计算机信息学院的各位老师在大学期间传授的专业知识，感谢信息安全 2408 "
              "班的同学们在学习和生活上的帮助。感谢在系统开发和论文写作过程中给予建议"
              "和帮助的所有人。")
    body(doc, "最后，感谢家人一直以来的支持与鼓励，使我能够安心完成学业。")

    # ================= 参考文献 =================
    page_break(doc)
    chapter_title(doc, "参考文献")
    refs = [
        "[1] 王珊, 萨师煊. 数据库系统概论[M]. 5版. 北京: 高等教育出版社, 2014.",
        "[2] 李强. PHP Web应用开发实战[M]. 北京: 人民邮电出版社, 2020.",
        "[3] 张海藩, 牟永敏. 软件工程导论[M]. 6版. 北京: 清华大学出版社, 2013.",
        "[4] 陈雷. 基于ThinkPHP的高校教务管理系统设计与实现[J]. 电脑知识与技术, 2021, 17(12): 78-80.",
        "[5] 刘洋, 王建华. 基于RBAC模型的权限管理研究与应用[J]. 计算机应用与软件, 2019, 36(4): 52-56.",
        "[6] 赵明. Layui框架在后台管理系统界面开发中的应用[J]. 信息技术与信息化, 2020, (8): 45-47.",
        "[7] 周志明. 深入理解Java虚拟机[M]. 3版. 北京: 机械工业出版社, 2019.",
        "[8] 杨帆. Docker容器技术在Web应用部署中的应用研究[J]. 计算机时代, 2022, (3): 33-36.",
        "[9] 陈国青, 李东. 管理信息系统[M]. 北京: 清华大学出版社, 2018.",
        "[10] 孙卫琴. Tomcat与Java Web开发技术详解[M]. 北京: 电子工业出版社, 2017.",
        "[11] 郑阿奇. PHP实用教程[M]. 4版. 北京: 电子工业出版社, 2019.",
        "[12] 崔庆才. Python 3网络爬虫开发实战[M]. 2版. 北京: 人民邮电出版社, 2021.",
    ]
    for r in refs:
        p = para(doc, r, cn="宋体", size=12, line=1.5, after=0)
        p.paragraph_format.left_indent = Pt(24)
        p.paragraph_format.first_line_indent = Pt(-24)

    # ================= 附录 =================
    page_break(doc)
    chapter_title(doc, "附录A 系统部署说明")
    body(doc, "本系统提供 Docker Compose 编排文件，在安装 Docker 和 Docker Compose "
              "的服务器上，进入项目根目录执行以下命令即可启动：")
    p = para(doc, "docker compose up -d", cn="宋体", en="Consolas", size=11, line=1.5)
    body(doc, "启动完成后，浏览器访问 http://服务器地址:8080 进入登录页。系统内置"
              "两个演示账号：管理员 admin，密码 admin123；教师 teacher，密码 teacher123。")
    body(doc, "如需停止服务，执行 docker compose stop；如需清除全部数据重新初始化，"
              "执行 docker compose down -v 后重新启动即可。")

    doc.save(OUT)
    print("saved:", OUT)


if __name__ == "__main__":
    build()
