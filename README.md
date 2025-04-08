# タスク管理API

シンプルなタスク管理APIとクライアントアプリケーション。

## 機能

- タスクの一覧表示
- タスクの詳細表示
- タスクの作成
- タスクの更新
- タスクの削除

## 技術スタック

### バックエンド
- PHP 7.4+
- MySQL 5.7+

### フロントエンド
- React 18
- TypeScript
- Vite
- Axios

## セットアップ方法

### 前提条件
- PHP 7.4以上
- MySQL 5.7以上
- Node.js 14以上
- npm 6以上

### バックエンドのセットアップ

1. リポジトリをクローンする
```
git clone https://github.com/kurobe2240/-API.git
cd タスク管理API
```

2. apiフォルダをWebサーバーのドキュメントルート（またはバーチャルホスト）に配置する
   - XAMPPを使用している場合：`htdocs`ディレクトリにコピー

3. データベースの初期化
   - ブラウザで`http://localhost/タスク管理API/api/init.php`にアクセスする
   - または、コマンドラインで`curl http://localhost/タスク管理API/api/init.php`を実行

### フロントエンドのセットアップ

1. 依存パッケージのインストール
```
npm install
```

2. 開発サーバーの起動
```
npm run dev
```

3. ビルド（本番環境用）
```
npm run build
```

## API エンドポイント

### タスク一覧の取得
- `GET /api/tasks`

### 特定のタスクの取得
- `GET /api/tasks/{id}`

### タスクの作成
- `POST /api/tasks`
- リクエストボディ:
```json
{
  "title": "タスク名",
  "description": "タスクの説明",
  "status": "pending | in-progress | completed"
}
```

### タスクの更新
- `PUT /api/tasks/{id}`
- リクエストボディ:
```json
{
  "title": "更新後のタスク名",
  "description": "更新後のタスクの説明",
  "status": "pending | in-progress | completed"
}
```

### タスクの削除
- `DELETE /api/tasks/{id}`

## デプロイ方法

### Renderを使用したデプロイ（推奨）

このプロジェクトはRenderへの自動デプロイに対応しています。

#### 準備

1. GitHubアカウントとRenderアカウントを用意
2. RenderダッシュボードからGitHubリポジトリと連携

#### バックエンド（PHP API）のデプロイ

1. Renderダッシュボードで「Web Service」を選択
2. 連携したGitHubリポジトリを選択
3. 以下の設定を行う:
   - Name: `task-api-backend`（任意）
   - Environment: `PHP`
   - Build Command: `composer install`
   - Start Command: `heroku-php-apache2 .`
4. 環境変数の設定:
   - `APP_ENV`: `production`
   - `DB_HOST`: データベースホスト
   - `DB_NAME`: データベース名
   - `DB_USER`: データベースユーザー
   - `DB_PASSWORD`: データベースパスワード
5. 「Create Web Service」をクリック

#### フロントエンド（React）のデプロイ

1. Renderダッシュボードで「Static Site」を選択
2. 連携したGitHubリポジトリを選択
3. 以下の設定を行う:
   - Name: `task-api-frontend`（任意）
   - Build Command: `npm install && npm run build`
   - Publish Directory: `dist`
4. 環境変数の設定:
   - `VITE_API_URL`: バックエンドAPIのURL（例: `https://task-api-backend.onrender.com`）
5. 「Create Static Site」をクリック

### 従来のホスティングサービスへのデプロイ

#### バックエンド（PHP）
1. PHPがサポートされているレンタルサーバーを用意
2. apiフォルダのファイルをFTPでアップロード
3. データベースの作成と初期化（init.phpを実行）

#### フロントエンド（React）
1. 本番用ビルドを作成
```
npm run build
```

2. 静的ファイルホスティングサービス（Vercel、Netlify、GitHub Pagesなど）にデプロイ
3. APIのベースURLを適切に設定（必要に応じて`.env.production`ファイルを更新）

## 環境変数設定

### 開発環境
`.env.development`ファイルに以下の設定を行います：
```
VITE_API_URL=http://localhost/タスク管理API/api
```

### 本番環境
`.env.production`ファイルに以下の設定を行います：
```
VITE_API_URL=https://your-api-backend.onrender.com
```

## ライセンス

MIT 