# Sử dụng image PHP 8.1 chính thức với FPM và Alpine Linux cho dung lượng nhỏ
FROM php:8.2-fpm-alpine

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Cài đặt các gói hệ thống cần thiết
# git, curl, libpng, libjpeg, etc. để build các extension
# oniguruma-dev cho mbstring, libzip-dev cho zip
RUN apk update && apk add --no-cache \
    build-base \
    git \
    curl \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Cài đặt Composer (trình quản lý gói cho PHP)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mkdir -p /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/framework/cache \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache

# 2. Thay đổi quyền sở hữu cho thư mục app
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose cổng 9000 cho PHP-FPM
EXPOSE 9000

# Chép file entrypoint mới vào container và trao quyền thực thi
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Gắn ENTRYPOINT tự động
ENTRYPOINT ["entrypoint.sh"]

# Lệnh mặc định khi container khởi chạy
CMD ["php-fpm"]