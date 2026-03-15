# ใช้ PHP 8.2 พร้อม Apache
FROM php:8.2-apache

# ติดตั้ง extension ที่ใช้กับ MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# เปิด mod_rewrite
RUN a2enmod rewrite