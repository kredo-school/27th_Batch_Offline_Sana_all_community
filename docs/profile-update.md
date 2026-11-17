# Profile update guide / プロフィール更新手順

This update adds location and occupation visibility settings, a private Written reviews list, review editing/deletion, and profile community links. It also fixes the CSS build entry used by the shared layout.

所在地・職業の公開設定、自分が書いたレビューの一覧・編集・削除、コミュニティリンクを追加しています。共通レイアウトの CSS ビルド設定も修正しています。

## Update your local branch / ローカルの更新

Run commands from the project root. Save or commit your own changes first.
プロジェクトのルートで実行してください。自分の作業中の変更は先に保存・コミットしてください。

On main / main ブランチの場合:

```bash
git pull origin main
```

On your feature branch / 自分の作業ブランチの場合:

```bash
git fetch origin main
git merge origin/main
```

## Required individual migration / 必須の個別マイグレーション

Existing installations must already have the profiles, events, event_reviews and user_reviews tables and earlier profile migrations. Check migration status before running the command below. Confirm that `.env` points to your intended local database.

既存環境の profiles・events・event_reviews・user_reviews テーブルと、これまでのプロフィール関連マイグレーションが適用済みであることが前提です。`.env` の接続先を確認し、実行状況を確認してください。

```bash
php artisan migrate:status
php artisan migrate --path=database/migrations/2026_11_11_120000_add_visibility_settings_to_profiles_table.php
```

This adds `show_location` and `show_occupation` to profiles, both defaulting to public (`true`). It preserves saved location and occupation values. Already-applied migrations are skipped. No additional migration is needed for Written reviews; it uses the existing review tables and soft deletes.

profiles に `show_location` と `show_occupation` を追加します。初期値は両方とも公開です。保存済みの所在地・職業は保持されます。適用済みの場合はスキップされます。レビュー機能は既存テーブルと論理削除を使うため、追加のマイグレーションはありません。

Use the exact `--path` command for this profile update. The current main branch also contains a separate migration, `2026_11_12_071106_recreate_jobs_table.php`, that drops and recreates `jobs`. Running all pending migrations with `php artisan migrate` also runs that migration if pending; review it separately before updating a database containing data. Do not use `migrate:fresh` or `migrate:refresh` on an existing database.

今回のプロフィール更新には上記の `--path` 付きコマンドを使ってください。main には別機能の `2026_11_12_071106_recreate_jobs_table.php` も含まれ、jobs テーブルを削除・再作成します。一括の `php artisan migrate` は未適用のその処理も実行するため、既存データのある環境では別途確認してください。既存 DB に `migrate:fresh`・`migrate:refresh` は実行しないでください。

## Build assets / CSS・JavaScript のビルド

```bash
npm ci
npm run build
php artisan view:clear
```

The build now includes `resources/css/app.css` with the Tailwind Vite plugin. Rebuild after pulling so the manifest contains the CSS requested by the shared layout; an old manifest can cause HTTP 500 errors.

共通レイアウトが参照する CSS をビルド対象に追加しています。古い manifest では 500 エラーになるため、pull 後はビルドしてください。

## Optional local demo data / 任意のローカル確認用データ

These seeders are optional and write sample data to your configured database. Use them only when you want demo content. They are not part of the required migration.

以下は任意です。接続先 DB にサンプルデータを追加するため、確認用データが必要なローカル環境でのみ実行してください。必須のマイグレーションではありません。

Choose either / 必要な方を選択:

```bash
# Reviews for an existing non-admin account; requires at least two non-admin accounts.
# 既存の一般ユーザー向けレビュー例（一般アカウントが2件以上必要）
php artisan db:seed --class=ExistingAccountReviewSeeder

# Profile examples for every active account; requires at least two active accounts.
# 全アクティブユーザー向けのプロフィール確認データ（2件以上必要）
php artisan db:seed --class=ProfileFeatureDemoSeeder
```

The second seeder adds sample events, communities, memberships, bookmarks, reviews and follows, and fills empty profile fields. Both seeders preserve existing review edits and deleted demo records on reruns.

2つ目はイベント・コミュニティ・参加情報・ブックマーク・レビュー・フォローを追加し、空のプロフィール項目を補完します。どちらも再実行時にレビューの編集内容や削除済みのサンプルを保持します。

## Verify / 確認

```bash
php artisan test
```

Tests use the SQLite in-memory database configured in `phpunit.xml`.

1. In Edit Profile, set location and occupation to Only me. The owner should still see them with Only you; another account should not see them.
2. Open Reviews → Written reviews on your own profile. Check All / Events / Hosts and pagination.
3. Edit or delete your own review and check the updated rating/list. Another account must not be able to manage it.
4. Open the Communities tab and check the community links.

テストは `phpunit.xml` の SQLite メモリ DB を使用します。画面では公開設定、自分のレビューの絞り込み・編集・削除、他アカウントからの非表示・操作不可、コミュニティリンクを確認してください。
