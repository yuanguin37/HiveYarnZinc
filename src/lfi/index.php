<?php
require_once('../flag_helper.php');
$challengeName = 'lfi';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$fileContent = "";

// 允许读取的白名单目录
$allowedFiles = [
    'welcome.txt' => '欢迎来到文件包含挑战！',
    'notes.txt' => '开发笔记: 记得修复文件包含漏洞！',
    'config.php' => "<?php\n\$db_host = 'internal.db.local';\n\$db_user = 'admin';\n\$db_pass = 'flag_is_hidden_elsewhere';\n",
];

if (isset($_GET['file'])) {
    $file = $_GET['file'];
    // 模拟文件包含漏洞 - 允许路径遍历
    if (strpos($file, '..') !== false || strpos($file, '/') !== false) {
        // 模拟读取 /etc/passwd 或 flag 文件
        if (strpos($file, 'flag') !== false) {
            $fileContent = $flag;
        } elseif (strpos($file, 'passwd') !== false || strpos($file, 'shadow') !== false) {
            $fileContent = "root:x:0:0:root:/root:/bin/bash\nwww-data:x:33:33:www-data:/var/www:/usr/sbin/nologin\n";
        } else {
            $fileContent = "错误: 文件 '$file' 不存在";
        }
    } elseif (isset($allowedFiles[$file])) {
        $fileContent = $allowedFiles[$file];
    } else {
        $fileContent = "错误: 文件 '$file' 不存在";
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
    <title>本地文件包含 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ffff; }
        .header h1 { font-size: 2.5em; color: #00ffff; text-shadow: 0 0 20px #00ffff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(0,255,255,0.05); border: 1px solid #00ffff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .lfi-box { background: #000; border: 2px solid #00ffff; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .lfi-box input { width: 100%; padding: 15px; background: #111; border: 1px solid #00ffff; color: #00ffff; font-size: 1.1em; border-radius: 5px; font-family: 'Courier New', monospace; }
        .lfi-box button { margin-top: 15px; padding: 12px 40px; background: linear-gradient(135deg, #00ffff, #0088ff); border: none; border-radius: 5px; color: #000; font-size: 1.1em; cursor: pointer; }
        .file-list { margin-top: 15px; }
        .file-list a { color: #00ffff; text-decoration: none; margin: 0 10px; }
        .file-list a:hover { text-shadow: 0 0 10px #00ffff; }
        .output { background: #000; padding: 20px; border-radius: 5px; margin-top: 20px; border: 1px solid #333; white-space: pre-wrap; color: #0f0; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #00ffff; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #00ffff; color: #00ffff; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #00ffff, #0088ff); border: none; border-radius: 5px; color: #000; font-size: 1.1em; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>📁 本地文件包含</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>文件包含功能允许读取服务器上的文件。<br>
            <strong>目标:</strong> 利用路径遍历读取Flag文件<br>
            <span style="color:#ff0;">提示:</span> 尝试使用 <code>../../flag.txt</code></p>
        </div>
        
        <div class="lfi-box">
            <h3 style="color:#00ffff;margin-bottom:15px;">📂 文件读取器</h3>
            <div class="file-list">可用文件: 
                <a href="?file=welcome.txt">welcome.txt</a>
                <a href="?file=notes.txt">notes.txt</a>
                <a href="?file=config.php">config.php</a>
            </div>
            <form method="GET" style="margin-top:15px;">
                <input type="text" name="file" placeholder="输入文件名..." value="<?= htmlspecialchars($_GET['file'] ?? '') ?>">
                <button type="submit">[ 读取 ]</button>
            </form>
            <?php if ($fileContent): ?>
            <div class="output"><strong style="color:#00ffff;"><?= htmlspecialchars($_GET['file']) ?>:</strong><br><?= nl2br(htmlspecialchars($fileContent)) ?></div>
            <?php endif; ?>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！</p></div>
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
