
# 勤怠管理アプリ

## 概要
本アプリは、出勤・退勤・休憩管理を行う勤怠管理アプリです。  
ユーザーは会員登録後、メール認証を完了しないとログインおよび勤怠機能を利用できません。

---

## 使用技術

- Laravel 12
- PHP 8.4
- MySQL 8.0
- Docker
- Nginx
- Fortify（認証機能）
- Mailhog（開発用メール確認ツール）

---

## 環境構築

### 1. Docker起動

```bash
docker compose up -d
````

### 2. PHPコンテナへ入る

```bash
docker compose exec php bash
```

### 3. 依存関係インストール

```bash
composer install
```

### 4. マイグレーション実行

```bash
php artisan migrate
```

---

## アクセスURL

* アプリ: [http://localhost](http://localhost)
* Mailhog: [http://localhost:8025](http://localhost:8025)
* phpMyAdmin: [http://localhost:8085](http://localhost:8085)

---

## メール認証機能

### 実装内容

* 新規会員登録時に認証メールを送信
* メール未認証ユーザーはログイン後もアプリ機能を利用不可
* `verified` ミドルウェアにより制御
* `MustVerifyEmail` を Userモデルに実装
* `email_verified_at` カラムにより認証状態を管理

### 開発環境メール確認方法

1. [http://localhost:8025](http://localhost:8025) にアクセス
2. 受信メール一覧から認証メールを選択
3. メール内の認証リンクをクリック
4. 認証完了後、勤怠画面へ遷移

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

## 機能一覧

* 会員登録
* ログイン / ログアウト
* メール認証機能
* 出勤処理
* 退勤処理
* 休憩開始 / 終了
* 管理者判定機能

---

## 補足

本アプリでは開発環境にMailhogを使用しています。
本番環境ではSMTPサーバー（例：SendGrid等）へ切り替えることでメール送信が可能です。

