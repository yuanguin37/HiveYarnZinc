<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>弱口令 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff8800; }
        .header h1 { font-size: 2.5em; color: #ff8800; text-shadow: 0 0 20px #ff8800; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,136,0,0.05); border: 1px solid #ff8800; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .info-box h3 { color: #ff8800; margin-bottom: 15px; }
        .info-box p { color: #aaa; line-height: 1.8; }
        .login-box { background: rgba(0,0,0,0.8); border: 2px solid #ff8800; border-radius: 15px; padding: 40px; }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; color: #ff8800; margin-bottom: 10px; font-size: 1.1em; }
        .form-group input { width: 100%; padding: 15px; background: #000; border: 1px solid #ff8800; color: #ff8800; font-size: 1.1em; border-radius: 5px; font-family: 'Courier New', monospace; }
        .form-group input:focus { outline: none; border-color: #ff0; box-shadow: 0 0 15px rgba(255,136,0,0.3); }
        .form-group button { width: 100%; padding: 15px; background: linear-gradient(135deg, #ff8800, #ff4400); border: none; border-radius: 5px; color: #fff; font-size: 1.2em; font-weight: bold; cursor: pointer; font-family: 'Courier New', monospace; }
        .form-group button:hover { background: linear-gradient(135deg, #ff0, #ff8800); color: #000; }
        .result { margin-top: 20px; padding: 20px; border-radius: 10px; text-align: center; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .hint-box { background: rgba(0,0,0,0.5); border: 1px dashed #ff8800; border-radius: 10px; padding: 20px; margin-top: 30px; }
        .hint-box h4 { color: #ff8800; margin-bottom: 10px; }
        .hint-box p { color: #888; line-height: 1.8; }
        .hint-box code { color: #ff0; background: #000; padding: 2px 8px; border-radius: 3px; }
        .common-pass { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
        .pass-tag { background: rgba(255,136,0,0.2); color: #ff8800; padding: 5px 15px; border-radius: 20px; font-size: 0.9em; cursor: pointer; }
        .pass-tag:hover { background: #ff8800; color: #000; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 弱口令</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>很多管理员为了方便会使用简单的密码。<br>
            <strong>目标:</strong> 尝试常见的弱口令登录系统<br>
            <span style="color:#ff0;">提示:</span> 试试 admin 或 root</p>
        </div>
        
        <?php
        $flag = "HiveYarnZinc{weak_password}";
        $error = "";
        $success = "";
        
        $valid_users = [
            'admin' => 'admin123',
            'root' => 'toor',
            'user' => 'user',
            'guest' => 'guest',
            'administrator' => 'password'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (isset($valid_users[$username]) && $valid_users[$username] === $password) {
                $success = "欢迎回来, $username!";
                if ($username === 'admin') {
                    $success .= "<br><br><strong style='font-size:1.3em;'>🎯 Flag: $flag</strong>";
                }
            } else {
                $error = "用户名或密码错误!";
            }
        }
        ?>
        
        <?php if ($error): ?>
        <div class="result error">⚠️ <?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="result success">✅ <?= $success ?></div>
        <?php endif; ?>
        
        <div class="login-box">
            <form method="POST">
                <div class="form-group">
                    <label>> Username:</label>
                    <input type="text" name="username" placeholder="输入用户名" required>
                </div>
                <div class="form-group">
                    <label>> Password:</label>
                    <input type="password" name="password" placeholder="输入密码" required>
                </div>
                <div class="form-group">
                    <button type="submit">[ 登录 ]</button>
                </div>
            </form>
            
            <p style="color:#666;text-align:center;margin-top:20px;">试试这些常见组合:</p>
            <div class="common-pass">
                <span class="pass-tag" onclick="document.querySelector('[name=username]').value='admin';document.querySelector('[name=password]').value='admin123'">admin/admin123</span>
                <span class="pass-tag" onclick="document.querySelector('[name=username]').value='root';document.querySelector('[name=password]').value='toor'">root/toor</span>
                <span class="pass-tag" onclick="document.querySelector('[name=username]').value='admin';document.querySelector('[name=password]').value='password'">admin/password</span>
            </div>
        </div>
        
        <div class="hint-box">
            <h4>📚 常见弱口令 TOP 10</h4>
            <p>
            1. admin / admin123<br>
            2. root / toor<br>
            3. 123456 / 12345678<br>
            4. password / password123<br>
            5. qwerty / qwerty123<br><br>
            <strong>提示:</strong> 只有 admin 账户有Flag！
            </p>
        </div>
    </div>
</body>
</html>