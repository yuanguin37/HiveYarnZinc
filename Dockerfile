FROM php:apache

# 配置Apache
RUN a2enmod rewrite headers

# 配置 Debian 镜像源（中国大陆用户可换用国内镜像加速）
# 方法1: 构建时传入参数（推荐）
#   docker-compose build --build-arg DEBIAN_MIRROR=mirrors.tuna.tsinghua.edu.cn ctf-platform
# 方法2: 取消下面两行的注释
# RUN sed -i 's/deb.debian.org/mirrors.tuna.tsinghua.edu.cn/g' /etc/apt/sources.list.d/debian.sources \
#  && sed -i 's/security.debian.org/mirrors.tuna.tsinghua.edu.cn/g' /etc/apt/sources.list.d/debian.sources
ARG DEBIAN_MIRROR
RUN if [ -n "$DEBIAN_MIRROR" ]; then \
        sed -i "s/deb.debian.org/$DEBIAN_MIRROR/g" /etc/apt/sources.list.d/debian.sources \
        && sed -i "s/security.debian.org/$DEBIAN_MIRROR/g" /etc/apt/sources.list.d/debian.sources; \
    fi

# 安装系统依赖 + PHP扩展 + 隐写工具（合并为单层，减少构建时间）
# --fix-missing 可在部分源不可用时继续安装
RUN set -ex \
    && apt-get update -qq \
    && apt-get install -y -qq --no-install-recommends --fix-missing \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libgmp-dev \
        libonig-dev \
        steghide \
        libimage-exiftool-perl \
        sqlite3 \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd gmp pdo_mysql mysqli mbstring \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 复制应用文件
COPY --chown=www-data:www-data src/ /var/www/html/

# 确保flag生成目录可写
RUN mkdir -p /var/www/html/flags && chown www-data:www-data /var/www/html/flags

EXPOSE 80

CMD ["apache2-foreground"]
