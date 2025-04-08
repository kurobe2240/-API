<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// オプションリクエストへの対応（CORSプリフライトリクエスト）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// リクエストURIからパスを取得
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// APIエンドポイントに対するルーティング
$resource = isset($uri[2]) ? $uri[2] : null;
$id = isset($uri[3]) ? $uri[3] : null;
$action = isset($uri[4]) ? $uri[4] : null;

// リクエストメソッド取得
$method = $_SERVER['REQUEST_METHOD'];

// JSONリクエストボディの取得
$data = json_decode(file_get_contents('php://input'), true);

require_once 'Database.php';
require_once 'TaskController.php';

$db = new Database();
$taskController = new TaskController($db);

// APIルーティング
switch ($resource) {
    case 'tasks':
        switch ($method) {
            case 'GET':
                if ($id) {
                    // 特定のタスクを取得
                    $taskController->getTask($id);
                } else {
                    // すべてのタスクを取得
                    $taskController->getAllTasks();
                }
                break;
            case 'POST':
                // 新しいタスクを作成
                $taskController->createTask($data);
                break;
            case 'PUT':
                // タスクを更新
                if ($id) {
                    $taskController->updateTask($id, $data);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'タスクIDが必要です']);
                }
                break;
            case 'DELETE':
                // タスクを削除
                if ($id) {
                    $taskController->deleteTask($id);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'タスクIDが必要です']);
                }
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'メソッドが許可されていません']);
                break;
        }
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'リソースが見つかりません']);
        break;
} 