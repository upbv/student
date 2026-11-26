#!/bin/sh
# 学生管理系统 PHP 容器启动脚本
# 修正运行目录权限后交给 php-fpm 接管

set -e

cd /var/www/html

# runtime 目录可能来自宿主挂载，需要保证 PHP 运行用户可写
mkdir -p runtime/tpl runtime/cache runtime/log runtime/session
chown -R www-data:www-data runtime

exec "$@"
