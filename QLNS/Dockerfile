FROM php:8.2-fpm

# Cài đặt các thư viện hệ thống cần thiết
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    libonig-dev \
    libxml2-dev \
    nginx \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd opcache zip

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy code
COPY . .

# Tải đầy đủ các AI models (để tránh lỗi push file binary lên HF)
RUN mkdir -p public/models && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/age_gender_model-shard1 -o public/models/age_gender_model-shard1 && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/age_gender_model-weights_manifest.json -o public/models/age_gender_model-weights_manifest.json && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/face_expression_model-shard1 -o public/models/face_expression_model-shard1 && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/face_expression_model-weights_manifest.json -o public/models/face_expression_model-weights_manifest.json && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/face_landmark_68_model-shard1 -o public/models/face_landmark_68_model-shard1 && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/face_landmark_68_model-weights_manifest.json -o public/models/face_landmark_68_model-weights_manifest.json && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/face_landmark_68_tiny_model-shard1 -o public/models/face_landmark_68_tiny_model-shard1 && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/face_landmark_68_tiny_model-weights_manifest.json -o public/models/face_landmark_68_tiny_model-weights_manifest.json && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/face_recognition_model-shard1 -o public/models/face_recognition_model-shard1 && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/face_recognition_model-shard2 -o public/models/face_recognition_model-shard2 && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/face_recognition_model-weights_manifest.json -o public/models/face_recognition_model-weights_manifest.json && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/mtcnn_model-shard1 -o public/models/mtcnn_model-shard1 && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/mtcnn_model-weights_manifest.json -o public/models/mtcnn_model-weights_manifest.json && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/ssd_mobilenetv1_model-shard1 -o public/models/ssd_mobilenetv1_model-shard1 && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/ssd_mobilenetv1_model-shard2 -o public/models/ssd_mobilenetv1_model-shard2 && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/ssd_mobilenetv1_model-weights_manifest.json -o public/models/ssd_mobilenetv1_model-weights_manifest.json && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/tiny_face_detector_model-shard1 -o public/models/tiny_face_detector_model-shard1 && \
    curl -L https://github.com/justadudewhohacks/face-api.js/raw/master/weights/tiny_face_detector_model-weights_manifest.json -o public/models/tiny_face_detector_model-weights_manifest.json

# Cài đặt Node.js (để build assets với Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

# Cài đặt dependencies của Laravel
RUN composer install --no-dev --optimize-autoloader

# Build frontend assets
RUN npm install && npm run build

# Cấu hình Nginx cho HF (Port 7860)
COPY ./docker/nginx.conf /etc/nginx/sites-available/default
EXPOSE 7860

# Cấp quyền và tạo các thư mục cần thiết (Cực kỳ quan trọng cho HF)
RUN mkdir -p /var/www/storage/app/public/avatars \
             /var/www/storage/framework/cache \
             /var/www/storage/framework/sessions \
             /var/www/storage/framework/views \
             /var/www/storage/logs && \
    chmod -R 777 /var/www/storage /var/www/bootstrap/cache && \
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Chạy lệnh khởi động (Auto migrate, force storage link và start services)
CMD php artisan migrate --force && \
    rm -rf public/storage && \
    php artisan storage:link && \
    nginx && php-fpm
