# -*- coding: utf-8 -*-
"""生成论文中的架构图、功能结构图与 E-R 图（黑白线条风格，适合打印）。"""
import os
import matplotlib
matplotlib.use("Agg")
import matplotlib.pyplot as plt
from matplotlib.patches import FancyBboxPatch, Rectangle
import matplotlib.font_manager as fm

# 中文字体
for cand in ["Noto Sans CJK SC", "WenQuanYi Zen Hei", "Source Han Sans SC",
             "SimHei", "Microsoft YaHei"]:
    if any(cand.lower() in f.name.lower() for f in fm.fontManager.ttflist):
        plt.rcParams["font.family"] = cand
        break
plt.rcParams["axes.unicode_minus"] = False

OUT = os.path.join(os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "docs")), "figures")
os.makedirs(OUT, exist_ok=True)


def box(ax, x, y, w, h, text, fs=10, lw=1.0):
    rect = FancyBboxPatch((x, y), w, h, boxstyle="round,pad=0.005,rounding_size=0.01",
                          linewidth=lw, edgecolor="black", facecolor="white")
    ax.add_patch(rect)
    ax.text(x + w / 2, y + h / 2, text, ha="center", va="center", fontsize=fs)


def line(ax, x1, y1, x2, y2, lw=0.8, style="-"):
    ax.plot([x1, x2], [y1, y2], color="black", lw=lw, linestyle=style)


# ------------------------- 图3.1 系统总体架构 -------------------------
def arch():
    fig, ax = plt.subplots(figsize=(7.2, 5.2), dpi=200)
    ax.set_xlim(0, 10); ax.set_ylim(0, 8); ax.axis("off")

    layers = [
        (6.6, "表示层", "Layui 页面：登录页、后台框架、各业务列表与表单"),
        (5.0, "业务逻辑层", "控制器 / 服务类：登录认证、权限判定、业务规则、日志记录"),
        (3.4, "数据访问层", "ThinkPHP ORM：模型、查询构造器、软删除、事务"),
        (1.8, "数据存储层", "MariaDB：student_ms 数据库（13 张数据表）"),
    ]
    for y, name, desc in layers:
        ax.add_patch(Rectangle((0.6, y), 8.8, 1.1, linewidth=1.0,
                               edgecolor="black", facecolor="white"))
        ax.text(1.9, y + 0.55, name, ha="center", va="center", fontsize=10)
        ax.text(5.6, y + 0.55, desc, ha="center", va="center", fontsize=8.5)
        line(ax, 2.9, y + 0.1, 2.9, y + 1.0, lw=0.6)

    for y1, y2 in [(6.6, 6.1), (5.0, 4.5), (3.4, 2.9)]:
        ax.annotate("", xy=(5, y2), xytext=(5, y1),
                    arrowprops=dict(arrowstyle="<->", lw=0.8, color="black"))

    ax.text(5, 0.7, "运行环境：Nginx → PHP-FPM → MariaDB（Docker Compose 编排）",
            ha="center", va="center", fontsize=9)
    fig.savefig(os.path.join(OUT, "arch.png"), bbox_inches="tight")
    plt.close(fig)


# ------------------------- 图3.2 功能结构 -------------------------
def modules():
    fig, ax = plt.subplots(figsize=(7.4, 4.6), dpi=200)
    ax.set_xlim(0, 24); ax.set_ylim(0, 10); ax.axis("off")

    box(ax, 9.6, 8.8, 4.8, 0.9, "学生管理系统", fs=10.5, lw=1.2)
    groups = [
        ("系统管理", ["账号管理", "角色管理", "权限管理"], 1.2),
        ("学生管理", ["学生列表", "班级管理", "院系管理", "教师管理"], 6.8),
        ("教务管理", ["课程管理", "选课管理", "成绩管理"], 13.0),
        ("日志管理", ["登录日志", "操作日志"], 19.2),
    ]
    centers = [x + 1.8 for _, _, x in groups]
    # 根节点到横向总线，再由总线分到各组
    line(ax, 12.0, 8.8, 12.0, 6.6, lw=0.7)
    line(ax, min(centers), 6.6, max(centers), 6.6, lw=0.7)
    for name, children, x in groups:
        box(ax, x, 5.4, 3.6, 0.9, name, fs=9.5)
        line(ax, x + 1.8, 6.6, x + 1.8, 6.3, lw=0.7)
        for i, c in enumerate(children):
            y = 4.0 - i * 1.05
            box(ax, x + 0.35, y, 2.9, 0.72, c, fs=8.5)
            line(ax, x + 1.8, 5.4, x + 1.8, y + 0.72, lw=0.6, style=":")
    fig.savefig(os.path.join(OUT, "modules.png"), bbox_inches="tight")
    plt.close(fig)


# ------------------------- 图3.3 E-R 图 -------------------------
def er():
    fig, ax = plt.subplots(figsize=(8.6, 5.6), dpi=200)
    ax.set_xlim(0, 29.5); ax.set_ylim(0, 17); ax.axis("off")

    def entity(x, y, w, h, name, attrs, fs=8):
        ax.add_patch(Rectangle((x, y), w, h, linewidth=1.0,
                               edgecolor="black", facecolor="white"))
        line(ax, x, y + h - 0.6, x + w, y + h - 0.6, lw=0.8)
        ax.text(x + w / 2, y + h - 0.3, name, ha="center", va="center",
                fontsize=fs, fontweight="bold")
        for i, a in enumerate(attrs):
            ax.text(x + 0.2, y + h - 1.0 - i * 0.55, a, ha="left", va="center", fontsize=fs)

    def diamond(x, y, w, h, text, fs=7.5):
        ax.add_patch(plt.Polygon([[x + w/2, y + h], [x + w, y + h/2],
                                  [x + w/2, y], [x, y + h/2]],
                                 closed=True, edgecolor="black",
                                 facecolor="white", lw=0.9))
        ax.text(x + w / 2, y + h / 2, text, ha="center", va="center", fontsize=fs)

    def label(x, y, text):
        ax.text(x, y, text, fontsize=7.5, ha="center", va="center")

    W, H = 5.4, 4.6
    # 上排
    entity(0.5, 11.6, W, H, "账号 ms_admin",
           ["id  主键", "username  账号", "password  密码", "nickname  姓名", "status  状态"])
    entity(8.1, 11.6, W, H, "角色 ms_role",
           ["id  主键", "name  名称", "code  标识", "status  状态", "sort  排序"])
    entity(15.7, 11.6, W, H, "权限 ms_auth",
           ["id  主键", "pid  上级", "name  名称", "type  类型", "path  地址"])

    diamond(6.35, 13.1, 1.5, 1.0, "拥有")
    line(ax, 5.9, 13.6, 6.35, 13.6)
    line(ax, 7.85, 13.6, 8.1, 13.6)
    label(6.0, 13.95, "m"); label(7.95, 13.95, "n")

    diamond(13.95, 13.1, 1.5, 1.0, "授权")
    line(ax, 13.5, 13.6, 13.95, 13.6)
    line(ax, 15.45, 13.6, 15.7, 13.6)
    label(13.6, 13.95, "m"); label(15.55, 13.95, "n")

    # 中间：课程
    entity(8.1, 7.15, W, 2.95, "课程 ms_course",
           ["id  主键", "course_no  编号", "name  名称", "credit  学分", "teacher_id  教师"])

    # 下排
    entity(0.5, 1.4, W, H, "院系 ms_department",
           ["id  主键", "name  名称", "code  代码", "leader  负责人", "sort  排序"])
    entity(8.1, 1.4, W, H, "班级 ms_class",
           ["id  主键", "name  名称", "grade  年级", "head_teacher  班主任", "department_id  院系"])
    entity(15.7, 1.4, W, H, "学生 ms_student",
           ["id  主键", "student_no  学号", "name  姓名", "class_id  班级", "status  学籍"])
    entity(23.2, 1.4, W, H, "教师 ms_teacher",
           ["id  主键", "teacher_no  工号", "name  姓名", "title  职称", "department_id  院系"])

    line(ax, 5.9, 3.7, 8.1, 3.7); label(7.0, 3.95, "1 : n")
    line(ax, 13.5, 3.7, 15.7, 3.7); label(14.6, 3.95, "1 : n")
    line(ax, 25.9, 6.0, 25.9, 9.5)
    line(ax, 13.5, 9.5, 25.9, 9.5)
    label(25.2, 7.9, "1 : n")

    diamond(12.8, 5.85, 1.6, 1.0, "选课", fs=7.5)
    line(ax, 13.6, 6.85, 13.6, 7.15)
    label(14.3, 7.05, "m : n")
    ax.text(14.6, 6.3, "成绩 / 学期", fontsize=7, ha="left", va="center")

    fig.savefig(os.path.join(OUT, "er.png"), bbox_inches="tight")
    plt.close(fig)


if __name__ == "__main__":
    arch()
    modules()
    er()
    print("figures saved to", OUT)
