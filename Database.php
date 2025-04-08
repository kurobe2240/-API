<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    private $is_production;

    public function __construct() {
        // 環境変数があれば使用、なければデフォルト値を使用
        $this->is_production = getenv('APP_ENV') === 'production';
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'task_api';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: 'root';
    }

    // データベース接続を取得するメソッド
    public function getConnection() {
        $this->conn = null;

        try {
            // 本番環境ではデバッグ情報を出力しない
            if (!$this->is_production) {
                echo "接続情報: host={$this->host}, dbname={$this->db_name}, user={$this->username}<br>";
            }
            
            // 開発環境でのみソケット接続確認を行う
            if (!$this->is_production) {
                $socket = @fsockopen($this->host, 3306, $errno, $errstr, 5);
                if (!$socket) {
                    throw new PDOException("MySQLサーバーに接続できません。XAMPPでMySQLが起動しているか確認してください。エラー: $errno - $errstr");
                }
                fclose($socket);
                echo "MySQLサーバー接続確認: OK<br>";
                
                // 開発環境でのみmysqli接続テスト
                if (function_exists('mysqli_connect')) {
                    echo "mysqli_connect関数を試します...<br>";
                    $mysqli = @mysqli_connect($this->host, $this->username, $this->password);
                    if ($mysqli) {
                        echo "mysqli接続成功！<br>";
                        mysqli_close($mysqli);
                    } else {
                        echo "mysqli接続失敗: " . mysqli_connect_error() . "<br>";
                    }
                }
            }
            
            // PDOで接続
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
            
            if (!$this->is_production) {
                echo "PDO接続成功<br>";
            }
        } catch(PDOException $e) {
            if (!$this->is_production) {
                echo "Connection error: " . $e->getMessage() . "<br>";
                echo "エラーコード: " . $e->getCode() . "<br>";
                echo "エラー発生場所: " . $e->getFile() . " (" . $e->getLine() . "行目)<br>";
            } else {
                // 本番環境ではエラーログに記録
                error_log("データベース接続エラー: " . $e->getMessage());
            }
            throw $e; // エラーを再スロー
        }

        return $this->conn;
    }

    // 初期設定：データベースとテーブルが存在しない場合に作成する
    public function initialize() {
        try {
            if (!$this->is_production) {
                echo "データベース初期化処理を開始します...<br>";
                echo "MySQLに接続中...<br>";
                echo "MySQLへの接続を試みます（user: {$this->username}, password: " . (empty($this->password) ? "なし" : "あり") . "）<br>";
            }
            
            // MySQLに接続（データベース指定なし）
            try {
                $tempConn = new PDO(
                    "mysql:host=" . $this->host,
                    $this->username,
                    $this->password
                );
                $tempConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                // データベースが存在しないエラーの場合は、新しく作成
                if ($e->getCode() == 1049) {
                    if (!$this->is_production) {
                        echo "データベースが存在しません。新しく作成します...<br>";
                    }
                    
                    $tempConn = new PDO(
                        "mysql:host=" . $this->host,
                        $this->username,
                        $this->password
                    );
                    $tempConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } else {
                    throw $e;
                }
            }
            
            // データベースが存在しない場合は作成
            if (!$this->is_production) {
                echo "データベース作成クエリを実行中...<br>";
            }
            $tempConn->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name . " CHARACTER SET utf8 COLLATE utf8_general_ci");
            
            // 作成したデータベースに接続
            if (!$this->is_production) {
                echo "作成したデータベースに接続中...<br>";
            }
            $conn = $this->getConnection();
            
            // tasksテーブルの作成
            if (!$this->is_production) {
                echo "タスクテーブル作成クエリを実行中...<br>";
            }
            $query = "CREATE TABLE IF NOT EXISTS tasks (
                id INT(11) NOT NULL AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                status VARCHAR(50) DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
            $conn->exec($query);
            
            // サンプルデータの作成
            if (!$this->is_production) {
                echo "サンプルデータの追加確認中...<br>";
            }
            $checkQuery = "SELECT COUNT(*) FROM tasks";
            $stmt = $conn->query($checkQuery);
            $count = $stmt->fetchColumn();
            
            if ($count == 0) {
                if (!$this->is_production) {
                    echo "サンプルデータを追加します...<br>";
                }
                $sampleData = [
                    ['タスク1', 'これは最初のタスクです', 'pending'],
                    ['重要な会議', '10時からのプロジェクト会議の準備をする', 'in-progress'],
                    ['レポート提出', '週次レポートを作成して提出する', 'completed']
                ];
                
                $insertQuery = "INSERT INTO tasks (title, description, status) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insertQuery);
                
                foreach ($sampleData as $task) {
                    $stmt->execute($task);
                }
                
                if (!$this->is_production) {
                    echo count($sampleData) . "件のサンプルデータを追加しました<br>";
                }
            } else if (!$this->is_production) {
                echo "既に" . $count . "件のデータが存在します<br>";
            }
            
            if (!$this->is_production) {
                echo "データベース初期化が完了しました。<br>";
            }
            return true;
        } catch(PDOException $e) {
            if (!$this->is_production) {
                echo "Initialization error: " . $e->getMessage() . "<br>";
                echo "エラーコード: " . $e->getCode() . "<br>";
                echo "エラー発生場所: " . $e->getFile() . " (" . $e->getLine() . "行目)<br>";
                
                // ユーザー向けのヘルプメッセージを追加
                if ($e->getCode() == 1045) {
                    echo "<p style='color:red'>MySQL接続エラー: ユーザー名またはパスワードが間違っています。</p>";
                    echo "<p>XAMPPの初期設定では、ユーザー名は 'root'、パスワードは空です。</p>";
                    echo "<p>もしパスワードを設定している場合は、環境変数または設定ファイルを更新してください。</p>";
                }
            } else {
                // 本番環境ではエラーログに記録
                error_log("データベース初期化エラー: " . $e->getMessage());
            }
            
            return false;
        }
    }
} 