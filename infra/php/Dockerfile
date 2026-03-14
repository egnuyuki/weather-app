FROM php:8.2-fpm-bullseye
# 引数を受け取る設定を追加 [1]
ARG UID
ARG GID
ENV UID=${UID}
ENV GID=${GID}
# ユーザーとグループを作成し、PHP-FPMの実行ユーザーを変更 [2]

# ホストと同じIDを持つユーザーを作成 (以下はDebian系の例) [3]
RUN groupadd -g ${GID} laravel && \
    useradd -u ${UID} -g laravel -m laravel

# PHP-FPMの実行ユーザーを www-data から laravel に書き換える [4]
RUN sed -i "s/user = www-data/user = laravel/g" /usr/local/etc/php-fpm.d/www.conf && \
    sed -i "s/group = www-data/group = laravel/g" /usr/local/etc/php-fpm.d/www.conf


# Laravelのディレクトリ権限を設定 [5-7]
WORKDIR /data
# 所有者を新しく作ったユーザーに変更する
RUN chown -R laravel:laravel /data

# パッケージのインストール (root権限が必要)
RUN apt-get update && \
    apt-get -y install --no-install-recommends git unzip libzip-dev libicu-dev libonig-dev && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-install intl pdo_mysql zip bcmath

COPY ./php.ini /usr/local/etc/php/php.ini

USER laravel

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/composer
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer
