FROM php:8.1-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set upload directory permissions (intentionally permissive for vuln demo)
RUN mkdir -p /var/www/html/uploads && chmod 777 /var/www/html/uploads

# Apache config to allow .php execution in uploads (intentionally vulnerable)
RUN echo '<Directory /var/www/html/uploads>\n\
    Options +ExecCGI\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/uploads.conf \
    && a2enconf uploads

# Allow .php files to run everywhere (vulnerable config)
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Expose ENCRYPTION_KEY as environment variable (visible in phpinfo)
ENV ENCRYPTION_KEY=secret123

EXPOSE 80
