<?php
require_once '../auth_check.php';

// MySQL 连接配置
$MYSQL_HOST = getenv('MYSQL_HOST') ?: 'ctf-mysql';
$MYSQL_USER = getenv('MYSQL_USER') ?: 'ctf_user';
$MYSQL_PASS = getenv('MYSQL_PASS') ?: 'ctf_password';
$MYSQL_DB   = getenv('MYSQL_DB')   ?: 'ctf_sqli';

// 启用 mysqli 异常模式
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $db = new mysqli($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DB);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // 连接失败时显示错误信息（依然保留题目页面 UI，但数据库不可用）
    $db = null;
    $db_error = "数据库连接失败: " . $e->getMessage();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($db === null) {
        $error = "数据库服务暂不可用，请联系管理员。";
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        // ★ 漏洞点：字符串直接拼接 SQL 查询（与实战场景一致）
        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        
        try {
            $result = $db->query($query);
            $row = $result ? $result->fetch_assoc() : false;
            
            if ($row) {
                $success = "欢迎回来, " . htmlspecialchars($row['username']) . "!";
                if ($row['role'] === 'admin') {
                    $success .= "<br><strong style='color:#00ff00;font-size:1.2em;'>🎯 Flag: HiveYarnZinc{sql_injection_123}</strong>";
                    $success .= "<br><em style='color:#00aa00;'>（Flag已自动记录到进度中）</em>";
                    $secrets_result = $db->query("SELECT * FROM secrets");
                    $success .= "<br><br><div style='background:#000;padding:15px;border-radius:5px;'>内部数据:<br>";
                    while ($s = $secrets_result->fetch_assoc()) {
                        $success .= htmlspecialchars($s['name']) . ": " . htmlspecialchars($s['value']) . "<br>";
                    }
                    $success .= "</div>";
                    // 自动标记完成（通过JavaScript）
                    $success .= "<script>window.addEventListener('DOMContentLoaded', function() { 
                        if (window.markChallengeCompleteAuto) window.markChallengeCompleteAuto('sqli');
                    });</script>";
                }
            } else {
                $error = "用户名或密码错误!";
            }
        } catch (mysqli_sql_exception $e) {
            $error = "查询错误: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>SQL注入 - HiveYarnZinc</title>
    <?php require_once '../progress_helper.php'; ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: #0a0a0f;
            color: #00ff00;
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 700px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ff00; }
        .header h1 { 
            font-size: 2.5em; 
            color: #00ff00; 
            text-shadow: 0 0 20px #00ff00;
            margin-bottom: 10px;
        }
        .back-link { 
            color: #00ffff; 
            text-decoration: none;
            font-size: 1.1em;
        }
        .back-link:hover { text-shadow: 0 0 10px #00ffff; }
        .info { 
            background: rgba(0, 255, 0, 0.05); 
            border: 1px solid #00ff00;
            border-radius: 10px; 
            padding: 20px; 
            margin-bottom: 30px;
        }
        .info h3 { color: #ff0; margin-bottom: 10px; }
        .login-form { 
            background: rgba(0, 20, 0, 0.8); 
            border: 2px solid #00ff00;
            border-radius: 15px; 
            padding: 40px;
            box-shadow: 0 0 30px rgba(0, 255, 0, 0.2);
        }
        .form-group { margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; color: #00ffff; font-size: 1.1em; }
        input { 
            width: 100%; 
            padding: 15px; 
            border: 1px solid #00ff00;
            border-radius: 5px; 
            background: #000; 
            color: #00ff00; 
            font-size: 1.1em;
            font-family: 'Courier New', monospace;
        }
        input:focus { 
            outline: none; 
            border-color: #00ffff;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.3);
        }
        input::placeholder { color: #666; }
        button { 
            width: 100%; 
            padding: 15px; 
            background: linear-gradient(135deg, #00ff00, #00aa00);
            border: none; 
            border-radius: 5px; 
            color: #000; 
            font-size: 1.2em; 
            font-weight: bold;
            cursor: pointer;
            font-family: 'Courier New', monospace;
        }
        button:hover { 
            background: linear-gradient(135deg, #00ffff, #00ff00);
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.5);
        }
        .error { 
            background: rgba(255, 0, 0, 0.1); 
            border: 1px solid #ff0000; 
            color: #ff0000; 
            padding: 15px; 
            border-radius: 5px; 
            margin-bottom: 20px;
        }
        .success { 
            background: rgba(0, 255, 0, 0.1); 
            border: 2px solid #00ff00;
            color: #00ff00; 
            padding: 20px; 
            border-radius: 10px; 
            margin-bottom: 20px;
            text-align: center;
        }
        code { 
            background: #000; 
            padding: 3px 8px; 
            border-radius: 3px;
            color: #ff0;
        }
        .hint { 
            margin-top: 30px; 
            padding: 25px; 
            background: rgba(0, 0, 0, 0.5);
            border: 1px dashed #00ffff;
            border-radius: 10px;
        }
        .hint h4 { color: #00ffff; margin-bottom: 15px; }
        .hint p { color: #aaa; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔓 SQL注入</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
            <div style="margin-top:8px;font-size:0.85em;color:#888;">后端: <span style="color:#ff9900;">MySQL 8.0</span> · 端口: <span style="color:#ff9900;">3307</span></div>
        </div>
        
        <div class="info">
            <h3>💡 题目描述</h3>
            <p>这是一个存在SQL注入漏洞的登录页面。<br>
            <strong>目标:</strong> 以管理员身份登录获取Flag</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error">⚠️ <?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="login-form">
            <form method="POST">
                <div class="form-group">
                    <label>> Username:</label>
                    <input type="text" name="username" placeholder="输入用户名" required>
                </div>
                <div class="form-group">
                    <label>> Password:</label>
                    <input type="password" name="password" placeholder="输入密码" required>
                </div>
                <button type="submit">[ 登录 ]</button>
            </form>
        </div>
        
        <div class="hint">
            <h4>📚 渗透测试笔记:</h4>
            <p>尝试使用以下Payload绕过认证:<br>
            <code>' OR '1'='1</code><br>
            <code>admin' --</code><br>
            <code>' OR 1=1 --</code><br>
            <code>' OR '1'='1' --</code></p>
        </div>
    </div>
    
    <script>
        // 页面加载时检查此题目是否已完成
        window.addEventListener('DOMContentLoaded', function() {
            if (window.checkChallengeCompleted) {
                window.checkChallengeCompleted('sqli');
            }
        });
    </script>
</body>
</html>