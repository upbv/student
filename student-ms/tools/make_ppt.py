# -*- coding: utf-8 -*-
"""生成毕业答辩 PPT（nature 风格：白底、深墨绿强调色、左侧细线分区）。"""
import os
from pptx import Presentation
from pptx.util import Inches, Pt, Emu
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE
from PIL import Image

HERE = os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs"))
OUT = os.path.join(HERE, "毕业答辩PPT.pptx")
FIG = os.path.join(HERE, "figures")

INK = RGBColor(0x1A, 0x1A, 0x1A)       # 近黑
GREY = RGBColor(0x6B, 0x6B, 0x6B)
LIGHT = RGBColor(0xE8, 0xE8, 0xE8)
ACCENT = RGBColor(0x2F, 0x5D, 0x50)    # 深墨绿
ACCENT_LIGHT = RGBColor(0xE9, 0xF0, 0xED)

FONT = "Microsoft YaHei"
FONT_EN = "Georgia"

W, H = Inches(13.333), Inches(7.5)


def add_slide(prs):
    return prs.slides.add_slide(prs.slide_layouts[6])


def text(slide, x, y, w, h, s, size=18, color=INK, bold=False, font=FONT,
         align=PP_ALIGN.LEFT, anchor=MSO_ANCHOR.TOP, spacing=1.15):
    box = slide.shapes.add_textbox(x, y, w, h)
    tf = box.text_frame
    tf.word_wrap = True
    tf.vertical_anchor = anchor
    tf.margin_left = 0
    tf.margin_right = 0
    tf.margin_top = 0
    tf.margin_bottom = 0
    lines = s.split("\n")
    for i, line in enumerate(lines):
        p = tf.paragraphs[0] if i == 0 else tf.add_paragraph()
        p.alignment = align
        p.line_spacing = spacing
        r = p.add_run()
        r.text = line
        r.font.size = Pt(size)
        r.font.bold = bold
        r.font.color.rgb = color
        r.font.name = font
    return box


def line(slide, x1, y1, x2, y2, color=LIGHT, width=0.75):
    from pptx.enum.shapes import MSO_CONNECTOR
    conn = slide.shapes.add_connector(MSO_CONNECTOR.STRAIGHT, x1, y1, x2, y2)
    conn.line.color.rgb = color
    conn.line.width = Pt(width)


def rect(slide, x, y, w, h, fill, line_color=None):
    shape = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, x, y, w, h)
    shape.fill.solid()
    shape.fill.fore_color.rgb = fill
    if line_color:
        shape.line.color.rgb = line_color
        shape.line.width = Pt(0.5)
    else:
        shape.line.fill.background()
    shape.shadow.inherit = False
    return shape


def header(slide, title, page=None, kicker=None):
    """页眉：左上角强调色短线 + 标题"""
    rect(slide, Inches(0.9), Inches(0.62), Inches(0.36), Pt(3), ACCENT)
    text(slide, Inches(1.4), Inches(0.44), Inches(9), Inches(0.55),
         title, size=24, bold=True)
    if kicker:
        text(slide, Inches(1.4), Inches(1.02), Inches(9), Inches(0.4),
             kicker, size=12, color=GREY)
    if page is not None:
        text(slide, Inches(12.1), Inches(6.95), Inches(0.7), Inches(0.3),
             "%02d" % page, size=11, color=GREY, align=PP_ALIGN.RIGHT)
        line(slide, Inches(0.9), Inches(7.15), Inches(12.45), Inches(7.15))


def picture_fit(slide, path, x, y, max_w, max_h, border=True):
    """按比例缩放图片并居中于给定区域"""
    with Image.open(path) as im:
        iw, ih = im.size
    ratio = min(max_w / iw, max_h / ih)
    w, h = int(iw * ratio), int(ih * ratio)
    px = int(x + (max_w - w) / 2)
    py = int(y + (max_h - h) / 2)
    pic = slide.shapes.add_picture(path, px, py, width=w, height=h)
    if border:
        pic.line.color.rgb = RGBColor(0xD5, 0xD5, 0xD5)
        pic.line.width = Pt(0.75)
    return pic


def bullet_block(slide, x, y, w, items, size=15, gap=0.52, color=INK, dot=ACCENT):
    for i, item in enumerate(items):
        yy = y + Inches(gap * i)
        rect(slide, x, yy + Inches(0.11), Pt(4), Pt(4), dot)
        text(slide, x + Inches(0.18), yy, w - Inches(0.18), Inches(0.45),
             item, size=size, color=color)


def build():
    prs = Presentation()
    prs.slide_width = W
    prs.slide_height = H
    page = 0

    # ---------------------------------------------------------------- 封面
    s = add_slide(prs)
    rect(s, 0, 0, W, Inches(0.16), ACCENT)
    text(s, Inches(1.1), Inches(1.5), Inches(11), Inches(0.5),
         "计算机信息学院 · 毕业设计答辩", size=15, color=GREY)
    text(s, Inches(1.1), Inches(2.15), Inches(11.2), Inches(1.9),
         "基于ThinkPHP6+Layui的\n学生管理系统的设计与实现",
         size=40, bold=True, spacing=1.2)
    line(s, Inches(1.1), Inches(4.42), Inches(5.6), Inches(4.42), ACCENT, 1.6)
    text(s, Inches(1.1), Inches(4.7), Inches(6), Inches(1.4),
         "答辩人：郭佳辰\n学　号：2400012811\n指导教师：毕柱兰", size=16, color=GREY, spacing=1.5)
    text(s, Inches(1.1), Inches(6.55), Inches(6), Inches(0.4),
         "2027 年 1 月", size=14, color=GREY)

    # ---------------------------------------------------------------- 目录
    page += 1
    s = add_slide(prs)
    header(s, "汇报提纲", page)
    items = [
        ("01", "研究背景与意义", "学生管理信息化的现实需求"),
        ("02", "需求分析", "角色划分与功能边界"),
        ("03", "系统设计", "总体架构、数据库与权限模型"),
        ("04", "系统实现", "关键模块与界面展示"),
        ("05", "系统测试与部署", "测试用例与容器化部署"),
        ("06", "总结与展望", "完成情况与后续改进方向"),
    ]
    for i, (no, title, desc) in enumerate(items):
        col = i % 2
        row = i // 2
        x = Inches(1.1 + col * 5.9)
        y = Inches(1.75 + row * 1.7)
        text(s, x, y, Inches(0.9), Inches(0.6), no, size=26, color=ACCENT, bold=True, font=FONT_EN)
        text(s, x + Inches(0.85), y - Inches(0.04), Inches(4.6), Inches(0.45),
             title, size=19, bold=True)
        text(s, x + Inches(0.85), y + Inches(0.42), Inches(4.6), Inches(0.4),
             desc, size=12, color=GREY)

    # ---------------------------------------------------------------- 01 背景
    page += 1
    s = add_slide(prs)
    header(s, "研究背景与意义", page, "01 · 研究背景")
    bullet_block(s, Inches(1.1), Inches(1.7), Inches(6.0), [
        "学院学生、班级、课程与成绩数据逐年增多，Excel 逐级汇总存在多版本、重复录入问题",
        "B/S 架构管理系统部署维护集中，教务人员、教师通过浏览器即可访问",
        "中小规模场景更适合采用成熟轻量的 PHP 技术栈快速交付",
        "权限划分与操作留痕是教务数据安全的基本要求",
    ], size=14.5, gap=1.05)
    # 右侧关键数字
    stats = [("13", "张数据表"), ("12", "个功能模块"), ("3", "类用户角色"), ("21", "个测试用例")]
    for i, (num, label) in enumerate(stats):
        y = Inches(1.85 + i * 1.22)
        rect(s, Inches(8.2), y, Inches(3.9), Inches(1.0), ACCENT_LIGHT)
        text(s, Inches(8.45), y + Inches(0.12), Inches(1.4), Inches(0.7),
             num, size=28, bold=True, color=ACCENT, font=FONT_EN)
        text(s, Inches(9.9), y + Inches(0.28), Inches(2.0), Inches(0.5),
             label, size=14, color=INK)

    # ---------------------------------------------------------------- 02 需求
    page += 1
    s = add_slide(prs)
    header(s, "需求分析", page, "02 · 角色与功能")
    text(s, Inches(1.1), Inches(1.45), Inches(5), Inches(0.4),
         "三类用户角色", size=15, bold=True, color=ACCENT)
    roles = [
        ("超级管理员", "账号、角色、权限维护；院系班级等基础数据"),
        ("教务管理员", "学生、班级、课程维护；选课办理与成绩管理"),
        ("教师", "查看学生名单；录入所授课程成绩"),
    ]
    for i, (name, desc) in enumerate(roles):
        y = Inches(1.95 + i * 1.45)
        rect(s, Inches(1.1), y, Inches(5.0), Inches(1.15), ACCENT_LIGHT)
        text(s, Inches(1.35), y + Inches(0.16), Inches(4.5), Inches(0.4),
             name, size=16, bold=True)
        text(s, Inches(1.35), y + Inches(0.62), Inches(4.5), Inches(0.4),
             desc, size=11.5, color=GREY)

    picture_fit(s, os.path.join(FIG, "modules.png"),
                Inches(6.5), Inches(1.55), Inches(6.1), Inches(5.3))
    text(s, Inches(6.5), Inches(6.75), Inches(6.1), Inches(0.3),
         "图 系统功能结构", size=10, color=GREY, align=PP_ALIGN.CENTER)

    # ---------------------------------------------------------------- 03 架构
    page += 1
    s = add_slide(prs)
    header(s, "系统总体架构", page, "03 · 系统设计")
    picture_fit(s, os.path.join(FIG, "arch.png"),
                Inches(1.0), Inches(1.5), Inches(6.3), Inches(5.35))
    text(s, Inches(1.0), Inches(6.85), Inches(6.3), Inches(0.3),
         "图 系统四层架构", size=10, color=GREY, align=PP_ALIGN.CENTER)

    text(s, Inches(7.8), Inches(1.55), Inches(4.8), Inches(0.4),
         "技术选型", size=15, bold=True, color=ACCENT)
    tech = [
        ("后端框架", "ThinkPHP 6.1.5（路由 / ORM / 中间件）"),
        ("前端组件", "Layui 2.9.21"),
        ("数据存储", "MariaDB 11，13 张数据表"),
        ("运行环境", "Nginx + PHP-FPM 8.2"),
        ("部署方式", "Docker Compose 一键启动"),
    ]
    for i, (k, v) in enumerate(tech):
        y = Inches(2.05 + i * 0.88)
        text(s, Inches(7.8), y, Inches(1.5), Inches(0.4), k, size=13, color=GREY)
        text(s, Inches(9.35), y, Inches(3.3), Inches(0.75), v, size=13.5)
        if i < len(tech) - 1:
            line(s, Inches(7.8), y + Inches(0.62), Inches(12.55), y + Inches(0.62))

    # ---------------------------------------------------------------- 04 权限模型
    page += 1
    s = add_slide(prs)
    header(s, "RBAC 权限模型", page, "03 · 系统设计")
    text(s, Inches(1.1), Inches(1.5), Inches(5.4), Inches(2.0),
         "账号 → 角色 → 权限\n\n账号不直接持有权限，权限统一授予角色，"
         "角色再分配给账号；同一账号可拥有多个角色，最终权限取并集。",
         size=14, color=INK, spacing=1.4)
    text(s, Inches(1.1), Inches(3.7), Inches(5.4), Inches(2.6),
         "两级校验\n\n· 菜单级：登录后按角色生成左侧导航，未授权菜单不下发\n"
         "· 接口级：CheckPermission 中间件对每次请求校验路径权限，"
         "直接调用接口同样被拦截",
         size=14, color=INK, spacing=1.4)
    picture_fit(s, os.path.join(FIG, "er.png"),
                Inches(6.2), Inches(1.5), Inches(6.4), Inches(5.2))
    text(s, Inches(6.2), Inches(6.75), Inches(6.4), Inches(0.3),
         "图 系统主要实体关系", size=10, color=GREY, align=PP_ALIGN.CENTER)

    # ---------------------------------------------------------------- 05 数据库
    page += 1
    s = add_slide(prs)
    header(s, "数据库设计", page, "03 · 系统设计")
    groups = [
        ("权限体系", "账号、角色、权限、账号角色、角色权限 5 张表"),
        ("基础数据", "院系、班级、教师、课程 4 张表"),
        ("业务数据", "学生、选课（含成绩）2 张表"),
        ("审计数据", "登录日志、操作日志 2 张表"),
    ]
    for i, (name, desc) in enumerate(groups):
        col = i % 2
        row = i // 2
        x = Inches(1.1 + col * 5.95)
        y = Inches(1.7 + row * 2.45)
        rect(s, x, y, Inches(5.5), Inches(2.0), ACCENT_LIGHT)
        text(s, x + Inches(0.3), y + Inches(0.25), Inches(4.9), Inches(0.45),
             name, size=17, bold=True, color=ACCENT)
        text(s, x + Inches(0.3), y + Inches(0.85), Inches(4.9), Inches(0.9),
             desc, size=13, color=INK, spacing=1.35)
    text(s, Inches(1.1), Inches(6.55), Inches(11.5), Inches(0.5),
         "统一 utf8mb4 编码；学号、工号、课程编号设唯一索引；学生、账号等表采用软删除，误删可恢复。",
         size=12.5, color=GREY)

    # ---------------------------------------------------------------- 06 登录
    page += 1
    s = add_slide(prs)
    header(s, "登录与认证", page, "04 · 系统实现")
    picture_fit(s, os.path.join(FIG, "login.png"),
                Inches(1.0), Inches(1.5), Inches(6.3), Inches(5.2))
    text(s, Inches(1.0), Inches(6.75), Inches(6.3), Inches(0.3),
         "图 系统登录页面", size=10, color=GREY, align=PP_ALIGN.CENTER)
    bullet_block(s, Inches(7.8), Inches(1.65), Inches(4.9), [
        "密码 MD5 加盐存储，盐值随机生成",
        "同一 IP 十秒内只允许一次登录尝试",
        "连续五次密码错误锁定十五分钟",
        "会话由中间件统一校验，页面与接口分别返回跳转或 JSON 提示",
        "每次登录成功或失败均写入登录日志",
    ], size=13, gap=1.0)

    # ---------------------------------------------------------------- 07 后台框架
    page += 1
    s = add_slide(prs)
    header(s, "后台主框架与权限菜单", page, "04 · 系统实现")
    picture_fit(s, os.path.join(FIG, "frame.png"),
                Inches(1.0), Inches(1.5), Inches(11.4), Inches(5.15))
    text(s, Inches(1.0), Inches(6.72), Inches(11.4), Inches(0.3),
         "图 后台主界面：左侧导航按角色动态生成，右侧多标签页工作区", size=10,
         color=GREY, align=PP_ALIGN.CENTER)

    # ---------------------------------------------------------------- 08 学生管理
    page += 1
    s = add_slide(prs)
    header(s, "学生信息管理", page, "04 · 系统实现")
    picture_fit(s, os.path.join(FIG, "student.png"),
                Inches(1.0), Inches(1.5), Inches(8.1), Inches(5.15))
    text(s, Inches(1.0), Inches(6.72), Inches(8.1), Inches(0.3),
         "图 学生列表页面", size=10, color=GREY, align=PP_ALIGN.CENTER)
    bullet_block(s, Inches(9.4), Inches(1.65), Inches(3.4), [
        "组合条件查询与分页",
        "新增、编辑、批量删除",
        "软删除，可恢复",
        "详情页联动展示选课与成绩",
        "按筛选条件导出 CSV 名单",
    ], size=12.5, gap=0.98)

    # ---------------------------------------------------------------- 09 成绩
    page += 1
    s = add_slide(prs)
    header(s, "选课与成绩管理", page, "04 · 系统实现")
    picture_fit(s, os.path.join(FIG, "score.png"),
                Inches(1.0), Inches(1.5), Inches(8.1), Inches(5.15))
    text(s, Inches(1.0), Inches(6.72), Inches(8.1), Inches(0.3),
         "图 成绩管理页面", size=10, color=GREY, align=PP_ALIGN.CENTER)
    bullet_block(s, Inches(9.4), Inches(1.65), Inches(3.4), [
        "重复选课拦截，退课后可恢复",
        "成绩单条录入与校验",
        "按学号批量录入成绩",
        "按课程统计平均分与及格率",
    ], size=12.5, gap=0.98)

    # ---------------------------------------------------------------- 10 日志
    page += 1
    s = add_slide(prs)
    header(s, "日志审计", page, "04 · 系统实现")
    picture_fit(s, os.path.join(FIG, "log.png"),
                Inches(1.0), Inches(1.5), Inches(8.1), Inches(5.15))
    text(s, Inches(1.0), Inches(6.72), Inches(8.1), Inches(0.3),
         "图 操作日志页面", size=10, color=GREY, align=PP_ALIGN.CENTER)
    bullet_block(s, Inches(9.4), Inches(1.65), Inches(3.4), [
        "登录日志记录结果与 IP",
        "操作日志由中间件自动写入",
        "记录参数与执行结果",
        "密码等敏感参数做掩码",
        "支持按模块、结果、日期检索",
    ], size=12.5, gap=0.98)

    # ---------------------------------------------------------------- 11 测试
    page += 1
    s = add_slide(prs)
    header(s, "系统测试", page, "05 · 测试与部署")
    text(s, Inches(1.1), Inches(1.45), Inches(10), Inches(0.4),
         "黑盒测试，覆盖登录权限与业务功能，共 21 个用例，全部通过", size=14, color=GREY)
    data = [
        ("登录权限", "8 个用例", "错误密码、锁定、未登录跳转、越权拦截均已验证"),
        ("业务功能", "13 个用例", "增删改查、重复校验、批量操作、统计导出均已验证"),
        ("问题修复", "3 项", "班级人数同步、批量成绩匹配、多标签页布局"),
    ]
    for i, (name, count, desc) in enumerate(data):
        y = Inches(2.1 + i * 1.55)
        text(s, Inches(1.1), y, Inches(1.9), Inches(0.5), name, size=18, bold=True, color=ACCENT)
        text(s, Inches(3.1), y + Inches(0.03), Inches(1.5), Inches(0.5), count, size=15)
        text(s, Inches(4.9), y + Inches(0.05), Inches(7.4), Inches(0.6), desc, size=13, color=GREY)
        line(s, Inches(1.1), y + Inches(1.05), Inches(12.3), y + Inches(1.05))

    # ---------------------------------------------------------------- 12 部署
    page += 1
    s = add_slide(prs)
    header(s, "系统部署", page, "05 · 测试与部署")
    steps = [
        ("①", "准备环境", "服务器安装 Docker 与 Docker Compose，复制项目代码"),
        ("②", "配置参数", "按需调整端口映射与数据库密码，核对 .env 连接参数"),
        ("③", "一键启动", "执行 docker compose up -d，数据库自动初始化"),
        ("④", "访问系统", "浏览器访问服务器 8080 端口，使用演示账号登录"),
    ]
    for i, (no, name, desc) in enumerate(steps):
        y = Inches(1.7 + i * 1.28)
        text(s, Inches(1.1), y, Inches(0.6), Inches(0.6), no, size=22, color=ACCENT)
        text(s, Inches(1.85), y - Inches(0.03), Inches(1.7), Inches(0.45), name, size=17, bold=True)
        text(s, Inches(3.7), y + Inches(0.02), Inches(8.4), Inches(0.6), desc, size=13.5, color=GREY)
        if i < 3:
            line(s, Inches(1.1), y + Inches(0.92), Inches(12.1), y + Inches(0.92))
    rect(s, Inches(1.1), Inches(6.55), Inches(11.0), Inches(0.52), ACCENT_LIGHT)
    text(s, Inches(1.3), Inches(6.62), Inches(10.6), Inches(0.4),
         "演示地址：http://服务器地址:8080　　管理员 admin / admin123　　教师 teacher / teacher123",
         size=12.5, color=ACCENT)

    # ---------------------------------------------------------------- 13 总结
    page += 1
    s = add_slide(prs)
    header(s, "总结与展望", page, "06 · 总结")
    text(s, Inches(1.1), Inches(1.5), Inches(5.2), Inches(0.4),
         "完成情况", size=16, bold=True, color=ACCENT)
    bullet_block(s, Inches(1.1), Inches(2.0), Inches(5.2), [
        "完成 12 个功能模块与 13 张数据表设计",
        "实现 RBAC 菜单与接口两级权限控制",
        "登录日志与操作日志完整留痕",
        "Docker Compose 一键部署",
        "论文、任务记录、PPT、源代码齐全",
    ], size=13, gap=0.9)
    text(s, Inches(7.0), Inches(1.5), Inches(5.2), Inches(0.4),
         "后续改进", size=16, bold=True, color=ACCENT)
    bullet_block(s, Inches(7.0), Inches(2.0), Inches(5.2), [
        "增加学生端、教师端自助查询与录入",
        "补充成绩分布等数据可视化看板",
        "支持学生名单与成绩批量导入",
        "引入缓存与接口限流提升并发能力",
    ], size=13, gap=0.9)

    # ---------------------------------------------------------------- 致谢
    page += 1
    s = add_slide(prs)
    rect(s, 0, 0, W, Inches(0.16), ACCENT)
    text(s, Inches(1.1), Inches(2.4), Inches(11), Inches(0.8),
         "感谢各位老师批评指正", size=34, bold=True)
    text(s, Inches(1.1), Inches(3.5), Inches(11), Inches(1.2),
         "感谢指导老师毕柱兰老师的悉心指导，感谢学院各位老师的培养。\n"
         "请各位老师批评指正。", size=16, color=GREY, spacing=1.6)
    text(s, Inches(1.1), Inches(6.4), Inches(6), Inches(0.4),
         "答辩人：郭佳辰　·　信息安全2408班", size=13, color=GREY)

    prs.save(OUT)
    print("saved:", OUT)


if __name__ == "__main__":
    build()
