<?php
// エラー表示を有効にする
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class TaskController {
    private $db;
    private $conn;

    public function __construct($database) {
        $this->db = $database;
        $this->conn = $database->getConnection();
        
        // データベースとテーブルの初期化を実行
        $this->db->initialize();
    }

    // 全てのタスクを取得
    public function getAllTasks() {
        try {
            $query = "SELECT * FROM tasks ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            http_response_code(200);
            echo json_encode($tasks);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'タスクの取得に失敗しました: ' . $e->getMessage()]);
        }
    }

    // 特定のタスクをIDで取得
    public function getTask($id) {
        try {
            $query = "SELECT * FROM tasks WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $task = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(200);
                echo json_encode($task);
            } else {
                http_response_code(404);
                echo json_encode(['error' => '指定されたIDのタスクが見つかりません']);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'タスクの取得に失敗しました: ' . $e->getMessage()]);
        }
    }

    // 新しいタスクを作成
    public function createTask($data) {
        if (!isset($data['title']) || empty($data['title'])) {
            http_response_code(400);
            echo json_encode(['error' => 'タイトルは必須です']);
            return;
        }

        try {
            $query = "INSERT INTO tasks (title, description, status) VALUES (:title, :description, :status)";
            $stmt = $this->conn->prepare($query);
            
            // データのバインド
            $title = $data['title'];
            $description = isset($data['description']) ? $data['description'] : '';
            $status = isset($data['status']) ? $data['status'] : 'pending';
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                $taskId = $this->conn->lastInsertId();
                
                // 作成されたタスクを取得して返す
                $this->getTask($taskId);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'タスクの作成に失敗しました']);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'タスクの作成に失敗しました: ' . $e->getMessage()]);
        }
    }

    // タスクを更新
    public function updateTask($id, $data) {
        try {
            // 更新するフィールドを確認
            $fieldsToUpdate = [];
            $params = [];
            
            if (isset($data['title'])) {
                $fieldsToUpdate[] = "title = :title";
                $params[':title'] = $data['title'];
            }
            
            if (isset($data['description'])) {
                $fieldsToUpdate[] = "description = :description";
                $params[':description'] = $data['description'];
            }
            
            if (isset($data['status'])) {
                $fieldsToUpdate[] = "status = :status";
                $params[':status'] = $data['status'];
            }
            
            // 更新するフィールドがない場合
            if (empty($fieldsToUpdate)) {
                http_response_code(400);
                echo json_encode(['error' => '更新するデータがありません']);
                return;
            }
            
            // IDをパラメータに追加
            $params[':id'] = $id;
            
            // UPDATE文の構築
            $query = "UPDATE tasks SET " . implode(", ", $fieldsToUpdate) . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            
            // パラメータをバインド
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if ($stmt->execute()) {
                // 更新されたタスクを取得して返す
                $this->getTask($id);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'タスクの更新に失敗しました']);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'タスクの更新に失敗しました: ' . $e->getMessage()]);
        }
    }

    // タスクを削除
    public function deleteTask($id) {
        try {
            // 削除前にタスクの存在確認
            $checkQuery = "SELECT id FROM tasks WHERE id = :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => '指定されたIDのタスクが見つかりません']);
                return;
            }
            
            // タスクの削除
            $query = "DELETE FROM tasks WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(['message' => 'タスクが正常に削除されました', 'id' => $id]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'タスクの削除に失敗しました']);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'タスクの削除に失敗しました: ' . $e->getMessage()]);
        }
    }
} 