<?php
require_once('../flag_helper.php');
$challengeName = 'ssrf';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$internalFile = "";

// 模拟的内部文件
$internalData = [
    'flag.txt' => $flag,
    'config.php' => "<?php\n// DB Config\n\$db_host = 'localhost';\n\$db_user = 'root';\n\$db_pass = 'sup3r_s3cr3t';\n",
    '/etc/passwd' => "root:x:0:0:root:/root:/bin/bash\nwww-data:x:33:33:www-data:/var/www:/usr/sbin/nologin\n"
];

if (isset($_GET['url'])) {
    $url = $_GET['url'];
    // 模拟SSRF: 如果URL以file://开头，读取内部文件
    if (strpos($url, 'file://') === 0) {
        $filename = substr($url, 7);
        $filename = basename($filename);
        if (isset($internalData[$filename])) {
            $internalFile = htmlspecialchars($internalData[$filename]);
        } else {
            $internalFile = "错误: 无法访问文件 '$filename'";
        }
    } elseif (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
        $internalFile = "错误: 无法访问外部URL，服务器仅允许访问本地资源";
    } else {
        $internalFile = "错误: 不支持的协议";
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
    <title>SSRF服务端请求伪造 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff8800; }
        .header h1 { font-size: 2.5em; color: #ff8800; text-shadow: 0 0 20px #ff8800; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,136,0,0.05); border: 1px solid #ff8800; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .info-box h3 { color: #ff8800; margin-bottom: 15px; }
        .ssrf-box { background: #000; border: 2px solid #ff8800; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .ssrf-box input { width: 100%; padding: 15px; background: #111; border: 1px solid #ff8800; color: #ff8800; font-size: 1.1em; border-radius: 5px; font-family: 'Courier New', monospace; }
        .ssrf-box button { margin-top: 15px; padding: 12px 40px; background: linear-gradient(135deg, #ff8800, #ff4400); border: none; border-radius: 5px; color: #fff; font-size: 1.1em; cursor: pointer; font-family: 'Courier New', monospace; }
        .output { background: #000; padding: 20px; border-radius: 5px; margin-top: 20px; border: 1px solid #333; white-space: pre-wrap; color: #0f0; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #ff8800; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #ff8800; color: #ff8800; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #ff8800, #ff4400); border: none; border-radius: 5px; color: #fff; font-size: 1.1em; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🔗 SSRF服务端请求伪造</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>服务器提供了一个URL获取功能，但限制了外部访问。<br>
            <strong>目标:</strong> 利用SSRF漏洞读取服务器内部文件 <code>flag.txt</code><br>
            <span style="color:#ff0;">提示:</span> 尝试使用 <code>file://</code> 协议读取本地文件</p>
        </div>
        
        <div class="ssrf-box">
            <h3 style="color:#ff8800;margin-bottom:15px;">🌐 内部资源读取器</h3>
            <form method="GET">
                <input type="text" name="url" placeholder="file://flag.txt 或 http://..." value="<?= htmlspecialchars($_GET['url'] ?? '') ?>">
                <button type="submit">[ 读取 ]</button>
            </form>
            <?php if ($internalFile): ?>
            <div class="output"><strong style="color:#ff8800;">读取结果:</strong><br><?= nl2br($internalFile) ?></div>
            <?php endif; ?>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确！</p></div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#ff8800;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
