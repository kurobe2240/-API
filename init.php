<?php
// エラー表示を有効にする
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// バッファリングを開始（ヘッダー送信前の出力をキャプチャ）
ob_start();

echo "初期化を開始します...<br>";

try {
    // 設定情報の表示
    echo "PHP バージョン: " . phpversion() . "<br>";
    echo "現在のパス: " . __FILE__ . "<br>";

    // PDO拡張が利用可能か確認
    if (!extension_loaded('pdo')) {
        throw new Exception("PDO拡張がインストールされていません。");
    }
    
    if (!extension_loaded('pdo_mysql')) {
        throw new Exception("PDO MySQL拡張がインストールされていません。");
    }
    
    echo "PDO拡張を確認: OK<br>";
    
    require_once 'Database.php';

    // データベース接続を初期化
    $database = new Database();
    $result = $database->initialize();
    
    if ($result) {
        echo "<h3 style='color:green'>データベースとテーブルが正常に初期化されました。</h3>";
    } else {
        echo "<h3 style='color:orange'>データベースの初期化中に問題が発生しました。</h3>";
    }
} catch (Exception $e) {
    echo "<h3 style='color:red'>エラー: " . $e->getMessage() . "</h3>";
    echo "エラー詳細（スタックトレース）:<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// 出力を表示
$output = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>データベース初期化</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        .output {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            white-space: pre-wrap;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .back-link:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>タスク管理APIデータベース初期化</h1>
    
    <div class="output">
        <?php echo $output; ?>
    </div>
    
    <a href="/タスク管理API/index.php" class="back-link">メインページに戻る</a>
</body>
</html> 