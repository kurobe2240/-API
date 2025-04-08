<?php
// クライアントディレクトリからPHPのビルトインサーバーを起動するためのスクリプト
// コマンド: php -S localhost:8000 public/api/server.php

// すべてのAPIリクエストを処理
$uri = $_SERVER['REQUEST_URI'];

// /api/以降のパスを取得
$prefix = '/api';
$uri_path = parse_url($uri, PHP_URL_PATH);

// /api/で始まるパスなら、index.phpにリダイレクト
if (strpos($uri_path, $prefix) === 0) {
    // 現在のディレクトリ内のindex.phpを実行
    require_once __DIR__ . '/index.php';
} else {
    // 静的ファイルの場合はそのまま返す
    return false;
} 