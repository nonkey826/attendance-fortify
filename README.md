以下に、記号を使わずに丁寧に書き直した **README** を再度記載します。

---

# 勤怠管理アプリ

## 概要

本アプリは、**出勤・退勤・休憩管理** を行う勤怠管理アプリケーションです。
ユーザーは **会員登録** を行った後、**メール認証** を完了しないとログインおよび勤怠機能を利用できません。
また、アプリケーション内でユーザーが実施する操作（出勤、退勤、休憩開始など）を管理者が承認・確認できる機能も実装されています。

---

## 使用技術

本アプリは以下の技術を使用しています：

* **Laravel Framework**（バージョン: ^10.0）
* **PHP 8.4**
* **MySQL 8.0**（データベース）
* **Docker**（コンテナ化）
* **Nginx**（Webサーバー）
* **Laravel Fortify**（認証機能）
* **Mailhog**（開発用メール確認ツール）

---

## 環境構築

アプリをローカル環境でセットアップするための手順を説明します。以下の手順に従って環境を構築してください。

 1. Dockerの起動

まず、Dockerコンテナを起動します。これにより、必要なすべてのサービス（PHP、MySQL、Nginx、Mailhogなど）が立ち上がります。


docker compose up -d


2. PHPコンテナに入る

次に、**PHPコンテナ** にアクセスします。コンテナ内で必要なコマンドを実行できます。


docker compose exec php bash


 3. 依存関係のインストール

PHPコンテナ内で、Laravelプロジェクトに必要な依存関係をインストールします。


composer install


4. 環境ファイル設定

`.env.example` ファイルを `.env` にコピーし、アプリケーションの設定を行います。その後、アプリケーションキーを生成します。


cp .env.example .env
php artisan key:generate


 5. マイグレーションとダミーデータ投入

データベースのマイグレーションを実行し、初期データ（ダミーデータ）を投入します。


php artisan migrate --seed


---

## MySQL 接続

MySQL 8.0 を使用する場合、SSLを無効化するために以下のコマンドを使用します。


mysql -h mysql -u laravel_user -p"laravel_pass" --skip-ssl


このコマンドを使って、MySQLに接続できます。

---

## アクセスURL

* アプリケーション：[http://localhost](http://localhost)
* **Mailhog**（開発用メール確認ツール）：[http://localhost:8025](http://localhost:8025)
* **phpMyAdmin**（データベース管理ツール）：[http://localhost:8085](http://localhost:8085)

---

## ログイン情報

### 管理者

* **Email**: [admin@test.com](mailto:admin@test.com)
* **Password**: `admin123`

### 一般ユーザー

* **Email**: [user@test.com](mailto:user@test.com)
* **Password**: `12345678`

---

## メール認証機能

### 実装内容

* 新規会員登録時に、認証メールを送信します。
* メール未認証ユーザーはログイン後もアプリ機能を利用できません。
* `verified` ミドルウェアにより、メール認証状態を制御します。
* `MustVerifyEmail` を Userモデルに実装。
* `email_verified_at` カラムにより認証状態を管理します。

### 開発環境でのメール確認方法

1. **Mailhog** にアクセス：[http://localhost:8025](http://localhost:8025)
2. 受信メール一覧から 認証メール*を選択します。
3. メール内の 認証リンクをクリックします。
4. 認証完了後、勤怠画面に遷移します。

---

## 機能一覧

### 一般ユーザー

* 会員登録
* ログイン / ログアウト
* メール認証機能
* 出勤処理
* 退勤処理
* 休憩開始 / 終了
* 勤怠一覧表示
* 勤怠詳細表示
* 修正申請

### 管理者

* 勤怠一覧表示
* 勤怠詳細・編集
* 修正申請の承認

---

## データベース設計

### usersテーブル

| カラム名              | 型                    |
| ----------------- | -------------------- |
| id                | unsigned bigint      |
| name              | varchar(255)         |
| email             | varchar(255)         |
| password          | varchar(255)         |
| role              | varchar(50)          |
| email_verified_at | timestamp (nullable) |
| created_at        | timestamp            |
| updated_at        | timestamp            |

---

## ダミーデータ

Seederにより、以下のデータを作成することができます：

* 管理者ユーザー
* 一般ユーザー
* 勤怠データ

以下のコマンドで、ダミーデータを投入できます。


php artisan db:seed


---

## ER図

![ER図](resources/docs/ER.png)

---

## 補足

* 本アプリでは、開発環境に Mailhogを使用しています。
  本番環境では、SMTPサーバー（例：SendGrid、Mailgunなど）を使用することで、実際のメール送信が可能です。

