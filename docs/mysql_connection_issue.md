# MySQL接続問題の調査と解決記録

## 問題の概要
MySQLへの接続において、rootユーザーでの認証に失敗する問題が発生。

## エラー内容
```
phpMyAdmin へようこそ

エラー
MySQL のメッセージ: ドキュメント

接続できません。設定が無効です。
mysqli::real_connect(): (HY000/1045): Access denied for user 'root'@'localhost' (using password: NO)
MySQL サーバに接続しようとしましたが拒否されました。config.inc.php のホスト、ユーザ名、パスワードが MySQL サーバの管理者から与えられた情報と一致するか確認してください。
```

## 調査プロセス

### 1. データベース接続の確認
初期化スクリプト（init.php）実行時のエラー：
```
エラー: SQLSTATE[HY000] [2002] 対象のコンピューターによって拒否されたため、接続できませんでした。
エラーコード: 2002
```

### 2. 設定ファイルの確認

#### phpMyAdmin設定
ファイルパス: `C:\Users\yamam\Documents\tools\xampp\phpMyAdmin\config.inc.php`
```php
$cfg['Servers'][$i]['password'] = '';
$cfg['Servers'][$i]['AllowNoPassword'] = true;
```

#### Database.php設定
ファイルパス: `C:\xampp\htdocs\タスク管理API\Database.php`
```php
private $host = 'localhost';
private $db_name = 'task_api';
private $username = 'root';
private $password = '';  // パスワードを空に設定
```

## 試行した解決策

1. MySQLサービスの再起動
   - XAMPPコントロールパネルでのMySQLの停止/起動
   - Apacheサービスの停止/起動

2. rootパスワードのリセット試行
   - `mysql -u root --skip-password`コマンドでの接続試行
   - パスワードリセットSQLの実行（PowerShellの制限により失敗）

## 未解決の問題
1. rootユーザーの認証エラー
2. MySQLへの接続拒否

## 推奨される解決手順

### 方法1: データベースリセット
1. XAMPPのサービスを停止（MySQL, Apache）
2. 以下のファイルをバックアップ後、削除
   - `C:\Users\yamam\Documents\tools\xampp\mysql\data\ibdata1`
   - `C:\Users\yamam\Documents\tools\xampp\mysql\data\ib_logfile0`
   - `C:\Users\yamam\Documents\tools\xampp\mysql\data\ib_logfile1`
3. サービスを再起動

### 方法2: XAMPPの再インストール
1. すべてのサービスを停止
2. XAMPPをアンインストール
3. `C:\Users\yamam\Documents\tools\xampp`フォルダを削除
4. XAMPPを新規インストール
5. サービスの起動確認

## 技術的な注意点
- PowerShellでのMySQLコマンド実行に制限あり
- データベースファイルの直接操作にはバックアップが必須
- XAMPPの初期設定ではrootパスワードは空

## 参考資料
- エラーコード: 1045（アクセス拒否）
- エラーコード: 2002（接続拒否）
- 関連ファイル: config.inc.php, Database.php, my.ini

## 次のアクション
1. データベースリセット方法の実行
2. 失敗した場合はXAMPPの再インストール
3. 新規インストール後の接続テスト実施 