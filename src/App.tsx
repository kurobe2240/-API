import { useState, useEffect } from 'react'
import axios from 'axios'
import './App.css'

// 環境変数からAPIのURLを取得
const API_URL = import.meta.env.VITE_API_URL || '/api';

interface Task {
  id: number;
  title: string;
  description: string;
  status: string;
  created_at: string;
  updated_at: string;
}

function App() {
  const [tasks, setTasks] = useState<Task[]>([]);
  const [newTask, setNewTask] = useState({ title: '', description: '', status: 'pending' });
  const [editingTask, setEditingTask] = useState<Task | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // タスク一覧を取得
  const fetchTasks = async () => {
    try {
      setLoading(true);
      setError(null);
      const response = await axios.get(`${API_URL}/tasks`);
      setTasks(response.data);
    } catch (err) {
      console.error('タスク取得エラー:', err);
      setError('タスクの取得に失敗しました。');
    } finally {
      setLoading(false);
    }
  };

  // コンポーネントがマウントされたときにタスクを取得
  useEffect(() => {
    fetchTasks();
  }, []);

  // 新しいタスクを作成
  const createTask = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newTask.title.trim()) return;

    try {
      await axios.post(`${API_URL}/tasks`, newTask);
      setNewTask({ title: '', description: '', status: 'pending' });
      fetchTasks();
    } catch (err) {
      console.error('タスク作成エラー:', err);
      setError('タスクの作成に失敗しました。');
    }
  };

  // タスクを更新
  const updateTask = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingTask || !editingTask.title.trim()) return;

    try {
      await axios.put(`${API_URL}/tasks/${editingTask.id}`, {
        title: editingTask.title,
        description: editingTask.description,
        status: editingTask.status
      });
      setEditingTask(null);
      fetchTasks();
    } catch (err) {
      console.error('タスク更新エラー:', err);
      setError('タスクの更新に失敗しました。');
    }
  };

  // タスクを削除
  const deleteTask = async (id: number) => {
    if (!window.confirm('このタスクを削除してもよろしいですか？')) return;

    try {
      await axios.delete(`${API_URL}/tasks/${id}`);
      fetchTasks();
    } catch (err) {
      console.error('タスク削除エラー:', err);
      setError('タスクの削除に失敗しました。');
    }
  };

  return (
    <div className="app-container">
      <h1>タスク管理アプリ</h1>
      
      {error && <div className="error-message">{error}</div>}
      
      {/* 新しいタスク作成フォーム */}
      <div className="task-form-container">
        <h2>新しいタスクを追加</h2>
        <form onSubmit={createTask} className="task-form">
          <div className="form-group">
            <label htmlFor="title">タイトル:</label>
            <input
              type="text"
              id="title"
              value={newTask.title}
              onChange={(e) => setNewTask({...newTask, title: e.target.value})}
              required
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="description">説明:</label>
            <textarea
              id="description"
              value={newTask.description}
              onChange={(e) => setNewTask({...newTask, description: e.target.value})}
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="status">ステータス:</label>
            <select
              id="status"
              value={newTask.status}
              onChange={(e) => setNewTask({...newTask, status: e.target.value})}
            >
              <option value="pending">未着手</option>
              <option value="in-progress">進行中</option>
              <option value="completed">完了</option>
            </select>
          </div>
          
          <button type="submit">追加</button>
        </form>
      </div>
      
      {/* タスク編集フォーム */}
      {editingTask && (
        <div className="task-form-container">
          <h2>タスクを編集</h2>
          <form onSubmit={updateTask} className="task-form">
            <div className="form-group">
              <label htmlFor="edit-title">タイトル:</label>
              <input
                type="text"
                id="edit-title"
                value={editingTask.title}
                onChange={(e) => setEditingTask({...editingTask, title: e.target.value})}
                required
              />
            </div>
            
            <div className="form-group">
              <label htmlFor="edit-description">説明:</label>
              <textarea
                id="edit-description"
                value={editingTask.description}
                onChange={(e) => setEditingTask({...editingTask, description: e.target.value})}
              />
            </div>
            
            <div className="form-group">
              <label htmlFor="edit-status">ステータス:</label>
              <select
                id="edit-status"
                value={editingTask.status}
                onChange={(e) => setEditingTask({...editingTask, status: e.target.value})}
              >
                <option value="pending">未着手</option>
                <option value="in-progress">進行中</option>
                <option value="completed">完了</option>
              </select>
            </div>
            
            <div className="button-group">
              <button type="submit">更新</button>
              <button type="button" onClick={() => setEditingTask(null)}>キャンセル</button>
            </div>
          </form>
        </div>
      )}
      
      {/* タスク一覧 */}
      <div className="task-list-container">
        <h2>タスク一覧</h2>
        {loading ? (
          <p>読み込み中...</p>
        ) : tasks.length === 0 ? (
          <p>タスクがありません。新しいタスクを追加してください。</p>
        ) : (
          <ul className="task-list">
            {tasks.map((task) => (
              <li key={task.id} className={`task-item status-${task.status}`}>
                <div className="task-header">
                  <h3>{task.title}</h3>
                  <span className="task-status">
                    {task.status === 'pending' && '未着手'}
                    {task.status === 'in-progress' && '進行中'}
                    {task.status === 'completed' && '完了'}
                  </span>
                </div>
                <p className="task-description">{task.description}</p>
                <div className="task-actions">
                  <button onClick={() => setEditingTask(task)}>編集</button>
                  <button onClick={() => deleteTask(task.id)}>削除</button>
                </div>
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  )
}

export default App 