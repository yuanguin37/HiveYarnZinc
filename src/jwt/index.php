<?php
require_once('../flag_helper.php');
$challengeName = 'jwt';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$decoded = "";

// JWT模拟 - 简单Base64编码的JWT
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

// 模拟JWT验证
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $parts = explode('.', $token);
    
    if (count($parts) === 3) {
        $header = json_decode(base64url_decode($parts[0]), true);
        $payload = json_decode(base64url_decode($parts[1]), true);
        $signature = $parts[2];
        
        if ($header && $payload) {
            $alg = $header['alg'] ?? 'none';
            
            // 漏洞：接受 none 算法，不验证签名
            if ($alg === 'none' || $signature === '') {
                // none算法：不验证签名
                $decoded = "JWT验证通过！\n用户: " . ($payload['user'] ?? 'unknown') . "\n角色: " . ($payload['role'] ?? 'unknown');
                
                if (($payload['role'] ?? '') === 'admin' && ($payload['user'] ?? '') === 'admin') {
                    $decoded .= "\n\n🎯 " . $flag;
                }
            } elseif ($alg === 'HS256') {
                // 模拟HS256验证（总是失败，因为没有密钥）
                $decoded = "签名验证失败！无效的令牌。";
            } else {
                $decoded = "不支持的算法: $alg";
            }
        } else {
            $decoded = "无效的JWT格式！";
        }
    } else {
        $decoded = "无效的令牌格式！需要3部分。";
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
    <title>JWT令牌伪造 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff00ff; }
        .header h1 { font-size: 2.5em; color: #ff00ff; text-shadow: 0 0 20px #ff00ff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,0,255,0.05); border: 1px solid #ff00ff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .jwt-box { background: #000; border: 2px solid #ff00ff; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .jwt-box input { width: 100%; padding: 15px; background: #111; border: 1px solid #ff00ff; color: #ff00ff; font-size: 0.9em; border-radius: 5px; font-family: 'Courier New', monospace; }
        .jwt-box button { margin-top: 15px; padding: 12px 40px; background: linear-gradient(135deg, #ff00ff, #cc00cc); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
        .output { background: #000; padding: 20px; border-radius: 5px; margin-top: 20px; white-space: pre-wrap; color: #0f0; word-break: break-all; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #ff00ff; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #ff00ff; color: #ff00ff; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #ff00ff, #cc00cc); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🔑 JWT令牌伪造</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>JWT（JSON Web Token）用于身份验证。服务器未正确验证签名算法。<br>
            <strong>目标:</strong> 构造一个JWT，使 <code>role=admin</code> 且 <code>user=admin</code><br>
            <span style="color:#ff0;">提示:</span> 尝试 <code>alg: none</code> 攻击</p>
        </div>
        
        <div class="jwt-box">
            <h3 style="color:#ff00ff;margin-bottom:15px;">🔐 JWT验证器</h3>
            <form method="GET">
                <input type="text" name="token" placeholder="eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJ1c2VyIjoiYWRtaW4iLCJyb2xlIjoiYWRtaW4ifQ.">
                <button type="submit">[ 验证 ]</button>
            </form>
            <?php if ($decoded): ?>
            <div class="output"><?= nl2br(htmlspecialchars($decoded)) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="info-box">
            <h3>🔧 构造JWT</h3>
            <p><strong>Header:</strong> <code>{"alg":"none","typ":"JWT"}</code></p>
            <p><strong>Payload:</strong> <code>{"user":"admin","role":"admin"}</code></p>
            <p><strong>在线工具:</strong> <a href="https://jwt.io" style="color:#0ff;" target="_blank">jwt.io</a></p>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确！</p></div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#ff00ff;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
