Laravel 12 + Docker 開発環境構築レポート：weather-app

1. はじめに

本レポートは、次世代のPHPフレームワークである Laravel 12 を核としたプロジェクト「weather-app」の開発環境構築手順をまとめたものである。

本構成では、コンテナ仮想化技術を用いて開発、テスト、本番環境の差異を極小化し、スケーラビリティと再現性の高いインフラ環境を迅速にデプロイすることを目的としている。最新のモダンな開発手法に準拠し、チーム全体でのスムーズな開発スタートを支援する。

2. 環境構成（システムアーキテクチャ）

本プロジェクト「weather-app」は、以下の技術スタックで構成される。2025年時点での安定性とパフォーマンスを重視した選定である。

* フレームワーク: Laravel 12
* 言語ランタイム: PHP 8.2 (FPM) - Bullseyeベースの安定版を採用
* Webサーバー: Nginx (1.20-alpine) - 軽量・高速な静的ファイル処理
* データベース: MySQL 8.0 - 信頼性の高いRDBMS
* パッケージ管理: Composer 2.2
* インフラ基盤: Docker / Docker Compose
* CI基盤: GitHub Actions

3. ディレクトリ構造

プロジェクトルート weather-app/ を起点としたディレクトリ構成を以下に示す。インフラ設定ファイルを infra/ に集約し、Laravelアプリケーション本体（src/）と分離することで、メンテナンス性を高めている。

weather-app/
├── docker-compose.yml
├── infra/
│   ├── php/
│   │   ├── Dockerfile
│   │   └── php.ini
│   ├── nginx/
│   │   └── default.conf
│   └── mysql/
│       ├── Dockerfile
│       └── my.cnf
└── src/                 # Laravel本体 (構築前は空ディレクトリ)


4. コンテナ設定ファイルの定義

4.1. docker-compose.yml

アプリケーション、ウェブ、データベースの3層構造を定義する。

Senior Engineer's Insight: データベースのポートマッピングを 3306:3306 としているのは、ホストマシンから Sequel Ace や TablePlus などの GUI ツールを使用して直接接続し、開発効率を向上させるためである。

services:
  app:
    build: ./infra/php
    volumes:
      - ./src:/data

  web:
    image: nginx:1.20-alpine
    ports:
      - 8080:80
    volumes:
      - ./src:/data
      - ./infra/nginx/default.conf:/etc/nginx/conf.d/default.conf
    working_dir: /data

  db:
    build: ./infra/mysql
    volumes:
      - db-store:/var/lib/mysql
    ports:
      - 3306:3306

volumes:
  db-store:


4.2. PHP-FPM Application Container (PHP 8.2)

Dockerfile

Debian Bullseye ベースを採用することで、パッケージの安定性と互換性を確保している。

Implementation Note: COMPOSER_ALLOW_SUPERUSER=1 を設定することで、コンテナ内でのルート権限によるComposer実行時の警告を抑制し、CI/CDパイプラインを円滑化している。

FROM php:8.2-fpm-bullseye
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/composer
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer
RUN apt-get update && \
    apt-get -y install --no-install-recommends git unzip libzip-dev libicu-dev libonig-dev && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-install intl pdo_mysql zip bcmath
COPY ./php.ini /usr/local/etc/php/php.ini
WORKDIR /data


php.ini

開発環境においてデバッグを容易にし、かつLaravelの要件を満たす正確な設定を適用する。

zend.exception_ignore_args = off
expose_php = on
max_execution_time = 30
max_input_vars = 1000
upload_max_filesize = 64M
post_max_size = 128M
memory_limit = 256M
error_reporting = E_ALL
display_errors = on
display_startup_errors = on
log_errors = on
error_log = /dev/stderr
default_charset = UTF-8

[Date]
date.timezone = Asia/Tokyo

[mysqlnd]
mysqlnd.collect_memory_statistics = on

[Assertion]
zend.assertions = 1

[mbstring]
mbstring.language = Japanese


4.3. Nginx Web Server (Nginx 1.20)

default.conf

Laravel の公開ディレクトリ public を適切にルーティングし、セキュリティヘッダーを付与する。

Performance Note: /favicon.ico と /robots.txt へのアクセスログを抑制し、ディスクI/Oおよびログのノイズを低減している。

server {
    listen 80;
    listen [::]:80;
    server_name example.com;
    root /data/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ /\.(?!well-known).* {
        deny all;
    }
}


4.4. MySQL Database Container (MySQL 8.0)

Dockerfile

MySQL公式イメージを使用。設定ファイルのパーミッション管理はMySQLの仕様に基づき厳格に行う。

FROM mysql/mysql-server:8.0
ENV MYSQL_DATABASE=laravel \
    MYSQL_USER=phper \
    MYSQL_PASSWORD=secret \
    MYSQL_ROOT_PASSWORD=secret \
    TZ=Asia/Tokyo
COPY ./my.cnf /etc/my.cnf
RUN chmod 644 /etc/my.cnf


my.cnf

開発中のデバッグを容易にするため、スロークエリログおよび全クエリログ（General Log）を有効化している。

[mysqld]
# default
skip-host-cache
skip-name-resolve
datadir = /var/lib/mysql
socket = /var/lib/mysql/mysql.sock
secure-file-priv = /var/lib/mysql-files
user = mysql
pid-file = /var/run/mysqld/mysqld.pid

# character set / collation
character_set_server = utf8mb4
collation_server = utf8mb4_ja_0900_as_cs_ks

# timezone
default-time-zone = SYSTEM
log_timestamps = SYSTEM

# Error Log
log-error = mysql-error.log

# Slow Query Log
slow_query_log = 1
slow_query_log_file = mysql-slow.log
long_query_time = 1.0
log_queries_not_using_indexes = 0

# General Log
general_log = 1
general_log_file = mysql-general.log

[mysql]
default-character-set = utf8mb4

[client]
default-character-set = utf8mb4


5. セットアップ手順（コマンドライン）

手順1：プロジェクトディレクトリの準備

Laravelソースコードをマウントするための空ディレクトリを作成する。

mkdir -p src


手順2：Laravel 12のインストール

コンテナを一時的に起動し、Composerを使用して最新のLaravel 12をカレントディレクトリに展開する。

docker compose run --rm app composer create-project laravel/laravel . "^12.0"


手順3：環境変数（.env）の設定

src/.env および src/.env.example のDB接続設定を、Docker内ネットワーク名に合わせて変更する。

Network Note: コンテナ間通信のため DB_HOST はサービス名の db を指定する。ホスト側からGUIツール等で接続する場合は 127.0.0.1 を使用すること。

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=phper
DB_PASSWORD=secret


手順4：コンテナの起動

インフラ構成を反映させ、バックグラウンドで起動する。

docker compose up -d


6. 動作確認

起動後、任意のブラウザで以下のURLにアクセスする。

http://localhost:8080

Laravel 12のウェルカム画面が表示されれば、Docker環境およびPHP-FPM、Nginxの連携は正常に完了している。

7. CI設定（GitHub Actions）

継続的インテグレーションのため、GitHub Actionsの設定ファイル .github/workflows/test.yml を定義する。

Senior Engineer's Insight: CI環境ではデータベースの起動完了にラグが生じるため、PDOを用いたリトライループ（Wait for DB）を実装している。また、php artisan config:clear を実行することで、環境変数のキャッシュに起因する接続拒否エラーを確実に回避している。

name: Laravel Testing
on:
  pull_request:
jobs:
  laravel-testing:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Docker Version
        run: docker version
      - name: Build Docker Image
        run: docker compose build
      - name: Start Docker Containers
        run: docker compose up -d
      - name: Wait for DB
        run: |
          for i in {1..30}; do
            docker compose exec -T app php -r 'try { new PDO("mysql:host=db;dbname=laravel", "phper", "secret"); exit(0); } catch (Exception $e) { echo "Waiting for DB...\n"; sleep(2); }'
          done
      - name: Setup Laravel
        run: |
          docker compose exec -T app cp .env.example .env
          docker compose exec -T app php artisan config:clear
          docker compose exec -T app php artisan key:generate
          docker compose exec -T app composer install
      - name: Migrate & Test
        run: |
          docker compose exec -T app php artisan migrate
          docker compose exec -T app php artisan test


8. おわりに

本レポートの構成により、Laravel 12を用いた「weather-app」の開発基盤が整った。Dockerによるコンテナ化により、ローカル環境を汚染することなく、チーム全員が同一のミドルウェア構成で開発を進めることが可能である。また、GitHub ActionsによるCI設定により、コード品質の担保も自動化されている。これをベースに、迅速な機能実装へと移行されたい。

