FROM php:8.2-fpm

# 安装常用扩展与中文处理依赖
RUN docker-php-ext-install -j$(nproc) pdo_mysql opcache \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        git \
        unzip \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && rm -rf /var/lib/apt/lists/*

# 安装 Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# 预创建运行目录并授权给 www-data，避免容器启动后目录不可写
RUN mkdir -p runtime/tpl runtime/cache runtime/log runtime/session \
    && chown -R www-data:www-data runtime

EXPOSE 9000

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
