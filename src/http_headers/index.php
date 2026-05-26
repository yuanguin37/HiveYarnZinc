<?php
require_once('../flag_helper.php');
$challengeName = 'http_headers';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// Web题目：通过修改HTTP请求头获取隐藏信息
$hiddenHeaders = [
    'X-Debug-Info' => 'Flag隐藏在 X-Admin-Token 中',
    'X-Admin-Token' => $flag,
    'X-Internal-IP' => '10.0.0.1',
];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['admin'])) {
    // 检查特殊请求头
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $xForwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    $xAdmin = $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? '';
    
    if ($xAdmin === 'secret_admin_token_2024') {
        $message = "success";
    } elseif (strpos($userAgent, 'AdminBot') !== false && $xForwarded === '127.0.0.1') {
        $message = "hint_admin";
    } elseif ($xForwarded === '127.0.0.1') {
        $message = "hint_token";
    }
}

if (isset($_GET['admin'])) {
    // 模拟管理员后台
    $authHeader = $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? '';
    if ($authHeader === 'secret_admin_token_2024') {
        $message = "success";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $answer = trim($_POST['answer']);
    if ($answer === $flag) {
        $message = "success";
    } else {
        $message = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>HTTP请求头攻击 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ffff; }
        .header h1 { font-size: 2.5em; color: #00ffff; text-shadow: 0 0 20px #00ffff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(0,255,255,0.05); border: 1px solid #00ffff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .headers-box { background: #000; border: 2px solid #00ffff; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .headers-box a { color: #0ff; margin: 5px 10px; display: inline-block; padding: 8px 16px; border: 1px solid #0ff; border-radius: 5px; text-decoration: none; }
        .headers-box a:hover { background: rgba(0,255,255,0.2); }
        .response-dump { background: #1a1a2e; padding: 20px; border-radius: 5px; margin-top: 15px; white-space: pre-wrap; color: #0f0; font-size: 0.9em; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #00ffff; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #00ffff; color: #00ffff; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #00ffff, #0088ff); border: none; border-radius: 5px; color: #000; cursor: pointer; }
        .tips { background: rgba(0,0,0,0.5); border: 1px dashed #00ffff; border-radius: 10px; padding: 20px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>📡 HTTP请求头攻击</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>服务器通过HTTP请求头进行身份验证。你需要伪造特定的请求头访问管理后台。<br>
            <strong>目标:</strong> 绕过请求头验证获取Flag<br>
            <span style="color:#ff0;">提示:</span> 使用 <code>X-Forwarded-For: 127.0.0.1</code> 和 <code>X-Admin-Token</code></p>
        </div>
        
        <div class="headers-box">
            <h3 style="color:#00ffff;margin-bottom:15px;">🔍 资源</h3>
            <a href=".">首页（无特殊头）</a>
            <a href="?admin=1">管理后台</a>
            <p style="color:#888;margin-top:15px;">服务器检测的请求头: User-Agent, X-Forwarded-For, X-Admin-Token</p>
            
            <div class="response-dump">
服务器响应头:<br>
X-Debug-Info: 需要使用 X-Forwarded-For: 127.0.0.1<br>
X-Internal-IP: 10.0.0.1<br>
X-Hint: 先使用 X-Forwarded-For 绕过IP限制，再使用 X-Admin-Token
            </div>
        </div>
        
        <div class="info-box"><h3>🔧 curl 命令示例</h3>
            <pre style="background:#000;padding:15px;border-radius:5px;color:#0f0;">curl -H "X-Forwarded-For: 127.0.0.1" -H "X-Admin-Token: secret_admin_token_2024" http://localhost:8080/http_headers/?admin=1</pre>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 成功！</h2><p><?= $flag ?></p></div>
        <?php elseif ($message === 'hint_admin'): ?>
        <div class="result success"><h2>✅ IP限制已绕过</h2><p>检测到模拟管理员访问，但还需要管理员令牌。<br>尝试添加 <code>X-Admin-Token: secret_admin_token_2024</code></p></div>
        <?php elseif ($message === 'hint_token'): ?>
        <div class="result success"><h2>✅ IP限制绕过</h2><p>内部访问已确认，但需要管理员令牌才能获取Flag</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确！</p></div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#00ffff;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
