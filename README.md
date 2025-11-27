# COACHTECH ATTENDANCE APP

## 環境構築

### Dockerビルド

1. `git@github.com:K-RKY/attendance-app.git`
2. `docker-compose up -d --build`

* MySQLは、OSによって起動方法があるので、それぞれのPCに合わせて `docker-compose.yml` ファイルを編集してください。

### Laravel環境構築

1. `docker-compose exec php bash`
2. `composer install`
3. `.env.exampleファイルから.envを作成し、環境変数を変更
(メール認証用にMAIL_FROM_ADDRESSにno-reply@example.comを設定してください)`
4. `php artisan key:generate`
5. `php artisan migrate`
6. `php artisan db:seed`

### テスト環境構築

1. `docker exec -it attendance-app-mysql-1 bash`
2. `mysql -u root -p`
3. `CREATE DATABASE attendance_test;`
4. `.envから.env.testingを作成し、環境変数をテスト用に変更`
5. `docker-compose exec php bash`
6. `php artisan key:generate --env=testing`
7. `php artisan config:clear`
8. `php artisan migrate --env=testing`

*管理者ユーザー

- email : admin@example.com
- pass : password

## 使用技術

- PHP 8.0
- Laravel 10.0
- MySQL 8.0

## ER図

<img width="941" height="761" alt="attendance" src="https://github.com/user-attachments/assets/9b50506d-4737-47f7-ba7d-3d3bfd12d018" />


## URL

- 開発環境 : [http://localhost](http://localhost)
- phpMyAdmin : [http://localhost:8080](http://localhost:8080)
- mailhog : [http://localhost:8025](http://localhost:8025)


