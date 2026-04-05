FROM php:8.4-fpm

# php.ini のコピー
COPY php.ini /usr/local/etc/php/

# 必要なパッケージと PHP 拡張をインストール
RUN apt update \
  && apt install -y default-mysql-client zlib1g-dev libzip-dev unzip curl \
  && docker-php-ext-install pdo_mysql zip

# Composer をインストール
RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && composer self-update

# dockerize をインストール
RUN curl -sSL https://github.com/jwilder/dockerize/releases/download/v0.6.1/dockerize-linux-amd64-v0.6.1.tar.gz | tar -xz -C /usr/local/bin

# 作業ディレクトリの設定
WORKDIR /var/www