FROM php:8.0-apache

# PHPの拡張機能をインストール
RUN docker-php-ext-install pdo pdo_mysql

# Apacheの設定
RUN a2enmod rewrite

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ファイルをコピー
COPY . /var/www/html/

# 作業ディレクトリを設定
WORKDIR /var/www/html

# Composerの依存関係をインストール
RUN composer install --no-dev

# Apacheの設定ファイルを上書き
COPY .htaccess /var/www/html/.htaccess

# 権限の設定
RUN chown -R www-data:www-data /var/www/html

# ポートを公開
EXPOSE 80

# Apacheを起動
CMD ["apache2-foreground"] 