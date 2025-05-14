FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libaio1 \
    wget \
    alien \
    gcc \
    make \
    build-essential \
    nodejs \
    npm \
    iputils-ping \
    net-tools \
    procps

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Get Oracle Instantclient RPMs
RUN mkdir -p /opt/oracle && \
    cd /opt/oracle && \
    wget https://download.oracle.com/otn_software/linux/instantclient/213000/oracle-instantclient-basic-21.3.0.0.0-1.x86_64.rpm && \
    wget https://download.oracle.com/otn_software/linux/instantclient/213000/oracle-instantclient-devel-21.3.0.0.0-1.x86_64.rpm && \
    wget https://download.oracle.com/otn_software/linux/instantclient/213000/oracle-instantclient-sqlplus-21.3.0.0.0-1.x86_64.rpm && \
    alien -i oracle-instantclient-basic-21.3.0.0.0-1.x86_64.rpm && \
    alien -i oracle-instantclient-devel-21.3.0.0.0-1.x86_64.rpm && \
    alien -i oracle-instantclient-sqlplus-21.3.0.0.0-1.x86_64.rpm

# Configure Oracle environment variables
ENV LD_LIBRARY_PATH /usr/lib/oracle/21/client64/lib
ENV ORACLE_HOME /usr/lib/oracle/21/client64
ENV TNS_ADMIN $ORACLE_HOME/network/admin
ENV PATH $PATH:$ORACLE_HOME/bin

# Install Oracle PDO
RUN echo 'instantclient,/usr/lib/oracle/21/client64/lib' | pecl install oci8 && \
    echo "extension=oci8.so" > /usr/local/etc/php/conf.d/oci8.ini && \
    docker-php-ext-configure pdo_oci --with-pdo-oci=instantclient,/usr/lib/oracle/21/client64/lib && \
    docker-php-ext-install pdo_oci

# Get composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache modules
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Create info.php for testing
RUN mkdir -p /var/www/html/public && \
    echo "<?php phpinfo(); ?>" > /var/www/html/public/info.php

# Set up Apache VirtualHost
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy application files
COPY . /var/www/html/

# Set up permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"] 