<?php
// 全局中间件定义文件
return [
    // Session 初始化（登录状态依赖 Session）
    \think\middleware\SessionInit::class,
    // 输入清洗（去除 HTML 标签，防存储型 XSS）
    \app\middleware\SanitizeInput::class,
    // 登录校验
    \app\middleware\CheckLogin::class,
    // 权限校验
    \app\middleware\CheckPermission::class,
    // 操作日志
    \app\middleware\OperationLog::class,
];
