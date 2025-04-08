<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'task_api';
    private $username = 'root';
    private $password = '';
    private $conn;

    // データベース接続を取得するメソッド
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }

        return $this->conn;
    }

    // 初期設定：データベースとテーブルが存在しない場合に作成する
    public function initialize() {
        try {
            // MySQLに接続（データベース指定なし）
            $tempConn = new PDO(
                "mysql:host=" . $this->host,
                $this->username,
                $this->password
            );
            $tempConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // データベースが存在しない場合は作成
            $tempConn->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name . " CHARACTER SET utf8 COLLATE utf8_general_ci");
            
            // 作成したデータベースに接続
            $conn = $this->getConnection();
            
            // tasksテーブルの作成
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
            
            return true;
        } catch(PDOException $e) {
            echo "Initialization error: " . $e->getMessage();
            return false;
        }
    }
} 