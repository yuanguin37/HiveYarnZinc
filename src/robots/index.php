<?php
require_once('../flag_helper.php');
$challengeName = 'robots';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// 模拟robots.txt内容
$robotsContent = "User-agent: *\nDisallow: /admin/\nDisallow: /backup/\nDisallow: /secret_flag.txt\n\n# 管理员备注: flag.txt 隐藏在 backup 目录下";

// 模拟secret文件
$secretFiles = [
    'flag.txt' => $flag,
    'backup/flag.txt' => $flag,
    'admin/config.php' => "<?php\n\$admin_password = 'sup3r_53cur3_p@ss';\n"
];

$fileContent = "";

if (isset($_GET['view'])) {
    $view = $_GET['view'];
    if ($view === 'robots.txt') {
        header('Content-Type: text/plain');
        echo $robotsContent;
        exit();
    } elseif (isset($secretFiles[$view])) {
        $fileContent = $secretFiles[$view];
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
    <title>信息泄露 - Robots.txt - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff00ff; }
        .header h1 { font-size: 2.5em; color: #ff00ff; text-shadow: 0 0 20px #ff00ff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,0,255,0.05); border: 1px solid #ff00ff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .robots-box { background: #000; border: 2px solid #ff00ff; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .robots-box a { color: #0ff; display: inline-block; margin: 10px; padding: 10px 20px; background: rgba(0,255,255,0.1); border: 1px solid #0ff; border-radius: 5px; text-decoration: none; }
        .robots-box a:hover { background: rgba(0,255,255,0.3); }
        .output { background: #000; padding: 20px; border-radius: 5px; margin-top: 20px; white-space: pre-wrap; color: #0f0; }
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
        <div class="header"><h1>🤖 Robots.txt信息泄露</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>网站的 robots.txt 文件可能包含敏感目录信息。<br>
            <strong>目标:</strong> 查看 <code>robots.txt</code> 找到隐藏目录并获取Flag<br>
            <span style="color:#ff0;">提示:</span> 确认robots.txt中有哪些禁止访问的路径</p>
        </div>
        
        <div class="robots-box">
            <h3 style="color:#ff00ff;margin-bottom:15px;">🔍 资源探索</h3>
            <p style="margin-bottom:15px;color:#888;">查看 robots.txt 了解哪些目录被禁止访问：</p>
            <a href="?view=robots.txt">🤖 robots.txt</a>
            <a href="?view=flag.txt">🏴 flag.txt</a>
            <a href="?view=backup/flag.txt">📁 backup/flag.txt</a>
            
            <?php if ($fileContent): ?>
            <div class="output"><?= htmlspecialchars($fileContent) ?></div>
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
