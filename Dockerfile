FROM php:8.1-apache
# ติดตั้ง Driver MySQL
RUN docker-php-ext-install pdo pdo_mysql
# เปิดใช้งาน mod_rewrite สำหรับ .htaccess
RUN a2enmod rewrite
# เปลี่ยนสิทธิ์ให้ Apache อ่านไฟล์ได้
RUN chown -R www-data:www-data /var/www/html