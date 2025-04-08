<?php
// APIと同じディレクトリにフロントエンドを配置
// エラー表示を有効にする
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// バッファリングを開始（ヘッダー送信前の出力をキャプチャ）
ob_start();

// PHPのバージョンとパス情報を表示
echo "PHP Version: " . phpversion() . "<br>";
echo "Current path: " . __FILE__ . "<br>";

$debugOutput = "";

try {
    // データベース接続テスト
    require_once 'Database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "Database connection successful!<br>";
    
    // テーブル初期化
    $result = $db->initialize();
    echo "Database initialization result: " . ($result ? "Success" : "Failed") . "<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    // エラーが発生した場合は通常のHTMLレスポンスとして表示
    $debugOutput = ob_get_clean();
    echo $debugOutput;
    exit;
}

// デバッグ出力をキャプチャ
$debugOutput = ob_get_clean();

// URI解析とAPIリクエスト処理
$isApiRequest = false;

// リクエストURIからパスを取得
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// APIエンドポイントに対するルーティング
$resource = isset($uri[2]) ? $uri[2] : null;

// API呼び出しかどうかを判定
if ($resource === 'tasks') {
    $isApiRequest = true;
}

if ($isApiRequest) {
    // APIリクエストの場合はJSONレスポンス
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // オプションリクエストへの対応（CORSプリフライトリクエスト）
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    $id = isset($uri[3]) ? $uri[3] : null;
    $action = isset($uri[4]) ? $uri[4] : null;

    // リクエストメソッド取得
    $method = $_SERVER['REQUEST_METHOD'];

    // JSONリクエストボディの取得
    $data = json_decode(file_get_contents('php://input'), true);

    require_once 'TaskController.php';
    $taskController = new TaskController($db);

    // APIルーティング
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
} else {
    // 通常のWebページリクエストとして処理
    // ここからHTMLを出力
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>タスク管理アプリ</title>
  <style>
    :root {
      font-family: Inter, system-ui, Avenir, Helvetica, Arial, sans-serif;
      line-height: 1.5;
      font-weight: 400;
      color-scheme: light dark;
      color: rgba(255, 255, 255, 0.87);
      background-color: #242424;
      font-synthesis: none;
      text-rendering: optimizeLegibility;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    body {
      margin: 0;
      display: flex;
      justify-content: center;
      min-width: 320px;
      min-height: 100vh;
    }

    #app {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
      width: 100%;
    }

    h1, h2 {
      text-align: center;
    }

    .debug-info {
      background-color: #f5f5f5;
      border: 1px solid #ddd;
      padding: 10px;
      margin-bottom: 20px;
      font-family: monospace;
      white-space: pre-wrap;
    }

    .error-message {
      background-color: #ffdddd;
      color: #ff0000;
      padding: 1rem;
      margin-bottom: 1rem;
      border-radius: 4px;
      text-align: center;
    }

    .task-form-container {
      background-color: #f5f5f5;
      padding: 1.5rem;
      border-radius: 8px;
      margin-bottom: 2rem;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .task-form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    label {
      font-weight: 600;
    }

    input, textarea, select {
      padding: 0.8rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 1rem;
    }

    textarea {
      min-height: 100px;
      resize: vertical;
    }

    button {
      border-radius: 8px;
      border: 1px solid transparent;
      padding: 0.6em 1.2em;
      font-size: 1em;
      font-weight: 500;
      font-family: inherit;
      background-color: #1a1a1a;
      cursor: pointer;
      transition: border-color 0.25s;
    }

    button:hover {
      border-color: #646cff;
    }

    .task-list-container {
      background-color: #f5f5f5;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .task-list {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .task-item {
      background-color: #fff;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      border-left: 5px solid #ccc;
    }

    .status-pending {
      border-left-color: #f39c12 !important;
    }

    .status-in-progress {
      border-left-color: #3498db !important;
    }

    .status-completed {
      border-left-color: #2ecc71 !important;
    }

    .task-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.5rem;
    }

    .task-header h3 {
      margin: 0;
    }

    .task-status {
      font-size: 0.9rem;
      padding: 0.4rem 0.8rem;
      border-radius: 4px;
      font-weight: 600;
    }

    .status-pending .task-status {
      background-color: #fff3cd;
      color: #856404;
    }

    .status-in-progress .task-status {
      background-color: #cce5ff;
      color: #004085;
    }

    .status-completed .task-status {
      background-color: #d4edda;
      color: #155724;
    }

    .task-description {
      margin: 1rem 0;
      line-height: 1.5;
      color: #666;
    }

    .task-actions {
      display: flex;
      gap: 0.5rem;
      margin-top: 1rem;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.4);
    }

    .modal-content {
      background-color: #fefefe;
      margin: 15% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
      max-width: 600px;
      border-radius: 8px;
    }

    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }

    .hidden {
      display: none;
    }

    .button-group {
      display: flex;
      gap: 1rem;
    }

    @media (prefers-color-scheme: light) {
      :root {
        color: #213547;
        background-color: #ffffff;
      }
      button {
        background-color: #f9f9f9;
      }
    }
  </style>
</head>
<body>
  <div id="app">
    <h1>タスク管理アプリ</h1>
    
    <?php if (!empty($debugOutput)): ?>
    <div class="debug-info">
      <h3>デバッグ情報:</h3>
      <?php echo $debugOutput; ?>
    </div>
    <?php endif; ?>
    
    <div id="error-message" class="error-message hidden"></div>
    
    <!-- 新しいタスク作成フォーム -->
    <div class="task-form-container">
      <h2>新しいタスクを追加</h2>
      <form id="create-task-form" class="task-form">
        <div class="form-group">
          <label for="title">タイトル:</label>
          <input type="text" id="title" name="title" required>
        </div>
        
        <div class="form-group">
          <label for="description">説明:</label>
          <textarea id="description" name="description"></textarea>
        </div>
        
        <div class="form-group">
          <label for="status">ステータス:</label>
          <select id="status" name="status">
            <option value="pending">未着手</option>
            <option value="in-progress">進行中</option>
            <option value="completed">完了</option>
          </select>
        </div>
        
        <button type="submit">追加</button>
      </form>
    </div>
    
    <!-- タスク一覧 -->
    <div class="task-list-container">
      <h2>タスク一覧</h2>
      <div id="loading">読み込み中...</div>
      <div id="no-tasks" class="hidden">タスクがありません。新しいタスクを追加してください。</div>
      <ul id="task-list" class="task-list hidden"></ul>
    </div>

    <!-- 編集モーダル -->
    <div id="edit-modal" class="modal">
      <div class="modal-content">
        <span class="close">&times;</span>
        <h2>タスクを編集</h2>
        <form id="edit-task-form" class="task-form">
          <input type="hidden" id="edit-id" name="id">
          
          <div class="form-group">
            <label for="edit-title">タイトル:</label>
            <input type="text" id="edit-title" name="title" required>
          </div>
          
          <div class="form-group">
            <label for="edit-description">説明:</label>
            <textarea id="edit-description" name="description"></textarea>
          </div>
          
          <div class="form-group">
            <label for="edit-status">ステータス:</label>
            <select id="edit-status" name="status">
              <option value="pending">未着手</option>
              <option value="in-progress">進行中</option>
              <option value="completed">完了</option>
            </select>
          </div>
          
          <div class="button-group">
            <button type="submit">更新</button>
            <button type="button" id="cancel-edit">キャンセル</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // 要素の取得
      const errorMessageEl = document.getElementById('error-message');
      const createTaskForm = document.getElementById('create-task-form');
      const editTaskForm = document.getElementById('edit-task-form');
      const taskListEl = document.getElementById('task-list');
      const loadingEl = document.getElementById('loading');
      const noTasksEl = document.getElementById('no-tasks');
      const editModal = document.getElementById('edit-modal');
      const closeModalBtn = document.querySelector('.close');
      const cancelEditBtn = document.getElementById('cancel-edit');
      
      // タスクの取得
      async function fetchTasks() {
        try {
          loadingEl.classList.remove('hidden');
          taskListEl.classList.add('hidden');
          noTasksEl.classList.add('hidden');
          
          const response = await fetch('/タスク管理API/tasks');
          const tasks = await response.json();
          
          if (tasks.length === 0) {
            noTasksEl.classList.remove('hidden');
          } else {
            renderTasks(tasks);
            taskListEl.classList.remove('hidden');
          }
        } catch (error) {
          showError('タスクの取得に失敗しました: ' + error.message);
        } finally {
          loadingEl.classList.add('hidden');
        }
      }
      
      // タスクの表示
      function renderTasks(tasks) {
        taskListEl.innerHTML = '';
        
        tasks.forEach(task => {
          const li = document.createElement('li');
          li.className = `task-item status-${task.status}`;
          
          const statusText = 
            task.status === 'pending' ? '未着手' : 
            task.status === 'in-progress' ? '進行中' : 
            task.status === 'completed' ? '完了' : task.status;
          
          li.innerHTML = `
            <div class="task-header">
              <h3>${escapeHtml(task.title)}</h3>
              <span class="task-status">${statusText}</span>
            </div>
            <p class="task-description">${escapeHtml(task.description || '')}</p>
            <div class="task-actions">
              <button data-id="${task.id}" class="edit-btn">編集</button>
              <button data-id="${task.id}" class="delete-btn">削除</button>
            </div>
          `;
          
          taskListEl.appendChild(li);
        });
        
        // 編集ボタンのイベントを設定
        document.querySelectorAll('.edit-btn').forEach(btn => {
          btn.addEventListener('click', function() {
            const taskId = this.getAttribute('data-id');
            editTask(taskId);
          });
        });
        
        // 削除ボタンのイベントを設定
        document.querySelectorAll('.delete-btn').forEach(btn => {
          btn.addEventListener('click', function() {
            const taskId = this.getAttribute('data-id');
            deleteTask(taskId);
          });
        });
      }
      
      // 新しいタスクの作成
      createTaskForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const taskData = {
          title: formData.get('title'),
          description: formData.get('description'),
          status: formData.get('status')
        };
        
        try {
          const response = await fetch('/タスク管理API/tasks', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(taskData)
          });
          
          if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'タスクの作成に失敗しました');
          }
          
          this.reset();
          fetchTasks();
        } catch (error) {
          showError('タスクの作成に失敗しました: ' + error.message);
        }
      });
      
      // タスクの削除
      async function deleteTask(id) {
        if (!confirm('このタスクを削除してもよろしいですか？')) return;
        
        try {
          const response = await fetch(`/タスク管理API/tasks/${id}`, {
            method: 'DELETE'
          });
          
          if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'タスクの削除に失敗しました');
          }
          
          fetchTasks();
        } catch (error) {
          showError('タスクの削除に失敗しました: ' + error.message);
        }
      }
      
      // タスクの編集（モーダルを表示）
      async function editTask(id) {
        try {
          const response = await fetch(`/タスク管理API/tasks/${id}`);
          
          if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'タスクの取得に失敗しました');
          }
          
          const task = await response.json();
          
          document.getElementById('edit-id').value = task.id;
          document.getElementById('edit-title').value = task.title;
          document.getElementById('edit-description').value = task.description || '';
          document.getElementById('edit-status').value = task.status;
          
          editModal.style.display = 'block';
        } catch (error) {
          showError('タスクの取得に失敗しました: ' + error.message);
        }
      }
      
      // タスクの更新
      editTaskForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const id = formData.get('id');
        const taskData = {
          title: formData.get('title'),
          description: formData.get('description'),
          status: formData.get('status')
        };
        
        try {
          const response = await fetch(`/タスク管理API/tasks/${id}`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(taskData)
          });
          
          if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'タスクの更新に失敗しました');
          }
          
          editModal.style.display = 'none';
          fetchTasks();
        } catch (error) {
          showError('タスクの更新に失敗しました: ' + error.message);
        }
      });
      
      // モーダルを閉じる
      closeModalBtn.addEventListener('click', function() {
        editModal.style.display = 'none';
      });
      
      cancelEditBtn.addEventListener('click', function() {
        editModal.style.display = 'none';
      });
      
      // モーダル外をクリックしたときも閉じる
      window.addEventListener('click', function(e) {
        if (e.target === editModal) {
          editModal.style.display = 'none';
        }
      });
      
      // エラーメッセージの表示
      function showError(message) {
        errorMessageEl.textContent = message;
        errorMessageEl.classList.remove('hidden');
        
        // 5秒後に非表示にする
        setTimeout(() => {
          errorMessageEl.classList.add('hidden');
        }, 5000);
      }
      
      // HTMLエスケープ
      function escapeHtml(text) {
        if (!text) return '';
        return text
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/'/g, "&#039;");
      }
      
      // 初期表示
      fetchTasks();
    });
  </script>
</body>
</html>
<?php
}
?> 