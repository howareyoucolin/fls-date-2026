FROM php:8.2-apache

# Install system dependencies and PHP extensions commonly used on DreamHost
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        pdo_mysql \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure Apache to match DreamHost's .htaccess support
RUN a2enmod rewrite headers expires

# Configure Apache to allow .htaccess overrides (like DreamHost)
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY public/ /var/www/html/

# Set ServerName to suppress Apache warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Expose port 80
EXPOSE 80
