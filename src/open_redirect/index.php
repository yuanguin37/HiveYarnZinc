<?php
require_once('../flag_helper.php');
$challengeName = 'open_redirect';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// 模拟开放重定向漏洞
$safeDomains = ['example.com', 'localhost', 'hiveyarnzinc.com'];
$redirectLog = [];

if (isset($_GET['url'])) {
    $url = $_GET['url'];
    $parsed = parse_url($url);
    $host = $parsed['host'] ?? '';
    
    // 漏洞：没有验证重定向目标
    if (!empty($url)) {
        $redirectLog = [
            'requested_url' => $url,
            'host' => $host,
            'in_whitelist' => in_array($host, $safeDomains) ? '是' : '否',
            'redirecting' => '正在重定向...'
        ];
        // 如果重定向到特定路径，显示flag
        if (strpos($url, 'flag') !== false || $host === 'flag.com') {
            $redirectLog['result'] = $flag;
        } elseif (in_array($host, $safeDomains)) {
            $redirectLog['result'] = "安全重定向到 $url";
        } else {
            $redirectLog['result'] = "开放重定向漏洞已利用！目标: $url";
            $redirectLog['flag_hint'] = "访问包含 flag 的URL获取Flag";
        }
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
    <title>开放重定向 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff00ff; }
        .header h1 { font-size: 2.5em; color: #ff00ff; text-shadow: 0 0 20px #ff00ff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,0,255,0.05); border: 1px solid #ff00ff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .redirect-box { background: #000; border: 2px solid #ff00ff; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .redirect-box input { width: 100%; padding: 15px; background: #111; border: 1px solid #ff00ff; color: #ff00ff; font-size: 1.1em; border-radius: 5px; font-family: 'Courier New', monospace; }
        .redirect-box button { margin-top: 15px; padding: 12px 40px; background: linear-gradient(135deg, #ff00ff, #cc00cc); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
        .output { background: #1a1a2e; padding: 20px; border-radius: 5px; margin-top: 15px; white-space: pre-wrap; color: #0f0; }
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
        <div class="header"><h1>🔀 开放重定向</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>重定向功能未验证目标URL，可跳转到任意站点。<br>
            <strong>目标:</strong> 利用开放重定向找到隐藏的Flag<br>
            <span style="color:#ff0;">提示:</span> 尝试重定向到 <code>flag.com</code> 或包含 <code>flag</code> 的URL</p>
        </div>
        
        <div class="redirect-box">
            <h3 style="color:#ff00ff;margin-bottom:15px;">🔄 URL重定向器</h3>
            <form method="GET">
                <input type="text" name="url" placeholder="http://example.com" value="<?= htmlspecialchars($_GET['url'] ?? '') ?>">
                <button type="submit">[ 跳转 ]</button>
            </form>
            <?php if ($redirectLog): ?>
            <div class="output">
                <?php foreach ($redirectLog as $k => $v): ?>
                <strong style="color:#ff00ff;"><?= $k ?>:</strong> <?= htmlspecialchars($v) ?><br>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
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
