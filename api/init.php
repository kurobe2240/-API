<?php
require_once 'Database.php';

// データベース接続を初期化
$database = new Database();
$result = $database->initialize();

if ($result) {
    echo "データベースとテーブルが正常に初期化されました。";
} else {
    echo "データベースの初期化中にエラーが発生しました。";
} 