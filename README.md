# タスク管理アプリ

シンプルなタスク管理フロントエンドアプリケーション。

## 機能

- タスクの一覧表示
- タスクの詳細表示
- タスクの作成
- タスクの更新
- タスクの削除

## 技術スタック

- React 18
- TypeScript
- Vite
- Axios

## セットアップ方法

### 前提条件
- Node.js 14以上
- npm 6以上

### ローカル開発環境のセットアップ

1. リポジトリをクローンする
```
git clone https://github.com/kurobe2240/-API.git
cd タスク管理API
```

2. 依存パッケージのインストール
```
npm install
```

3. 開発サーバーの起動
```
npm run dev
```

4. ビルド（本番環境用）
```
npm run build
```

## デプロイ方法

### Vercelを使用したデプロイ（推奨）

このプロジェクトはVercelへの自動デプロイに対応しています。

#### 準備

1. GitHubアカウントとVercelアカウントを用意
2. VercelダッシュボードからGitHubリポジトリと連携

#### デプロイ手順

1. Vercelダッシュボードで「New Project」を選択
2. 連携したGitHubリポジトリを選択
3. 以下の設定を行う:
   - Framework Preset: `Vite`
   - Build Command: `npm run build`
   - Output Directory: `dist`
4. 環境変数の設定（必要に応じて）:
   - `VITE_API_URL`: 外部APIのURL
5. 「Deploy」をクリック

### 環境変数設定

#### 開発環境
`.env.development`ファイルに以下の設定を行います：
```
VITE_API_URL=http://localhost:3000/api
```

#### 本番環境
`.env.production`ファイルに以下の設定を行います：
```
VITE_API_URL=https://api.example.com
```

## ライセンス

MIT 