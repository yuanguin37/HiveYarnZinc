<?php
require_once('../flag_helper.php');
$challengeName = 'log_analysis';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// 模拟系统日志
$logData = [
    "Jan 15 08:22:31 server sshd[1234]: Failed password for root from 192.168.1.100 port 22 ssh2",
    "Jan 15 08:22:35 server sshd[1235]: Failed password for root from 192.168.1.100 port 22 ssh2",
    "Jan 15 08:22:39 server sshd[1236]: Failed password for root from 192.168.1.100 port 22 ssh2",
    "Jan 15 08:23:01 server sshd[1240]: Failed password for admin from 10.0.0.50 port 22 ssh2",
    "Jan 15 08:23:05 server sshd[1241]: Failed password for admin from 10.0.0.50 port 22 ssh2",
    "Jan 15 08:23:10 server sshd[1242]: Accepted password for admin from 10.0.0.50 port 22 ssh2",
    "Jan 15 08:23:11 server sudo: admin : TTY=pts/0 ; PWD=/home/admin ; USER=root ; COMMAND=/bin/cat /root/flag.txt",
    "Jan 15 08:23:11 server kernel: [FLAG_ACCESS] Flag: " . $flag,
    "Jan 15 08:24:00 server sshd[1300]: Failed password for ctf_user from 203.0.113.5 port 22 ssh2",
];

$visibleLogs = [];
$showFlag = false;
$searchResult = "";

if (isset($_GET['search'])) {
    $search = $_GET['search'];
    foreach ($logData as $line) {
        if (stripos($line, $search) !== false) {
            $visibleLogs[] = $line;
        }
    }
    if (empty($visibleLogs)) {
        $searchResult = "没有找到包含 '$search' 的日志";
    }
    // 如果搜索flag直接显示
    if (stripos($search, 'flag') !== false) {
        $showFlag = true;
    }
} else {
    $visibleLogs = array_slice($logData, 0, 5);
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
    <title>日志分析 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ffff00; }
        .header h1 { font-size: 2.5em; color: #ffff00; text-shadow: 0 0 20px #ffff00; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,255,0,0.05); border: 1px solid #ffff00; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .log-box { background: #000; border: 2px solid #ffff00; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .log-box input { width: 100%; padding: 15px; background: #111; border: 1px solid #ffff00; color: #ffff00; font-size: 1.1em; border-radius: 5px; font-family: 'Courier New', monospace; }
        .log-box button { margin-top: 15px; padding: 12px 40px; background: linear-gradient(135deg, #ffff00, #ffaa00); border: none; border-radius: 5px; color: #000; cursor: pointer; }
        .log-entries { margin-top: 15px; background: #0a0a0f; border-radius: 5px; padding: 10px; max-height: 400px; overflow-y: auto; }
        .log-line { padding: 8px 12px; border-bottom: 1px solid #1a1a2e; font-size: 0.85em; color: #aaa; font-family: monospace; }
        .log-line:nth-child(odd) { background: rgba(255,255,255,0.02); }
        .log-line .highlight { color: #ff0; }
        .log-line .success-log { color: #0f0; }
        .log-line .fail-log { color: #f44; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #ffff00; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #ffff00; color: #ffff00; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #ffff00, #ffaa00); border: none; border-radius: 5px; color: #000; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>📋 日志分析</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>获取到服务器的SSH访问日志，需要分析出攻击者的行为。<br>
            <strong>目标:</strong> 从日志中追踪攻击者获取Flag的路径<br>
            <span style="color:#ff0;">提示:</span> 搜索 <code>Accepted</code> 或 <code>FLAG</code> 关键字</p>
        </div>
        
        <div class="log-box">
            <h3 style="color:#ffff00;margin-bottom:15px;">🔍 日志搜索</h3>
            <form method="GET">
                <input type="text" name="search" placeholder="搜索: admin, FLAG, Accepted..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit">[ 搜索 ]</button>
            </form>
            
            <div class="log-entries">
                <?php if ($searchResult): ?>
                <div class="log-line" style="color:#ff0;"><?= htmlspecialchars($searchResult) ?></div>
                <?php endif; ?>
                <?php foreach ($visibleLogs as $line): 
                    $cssClass = '';
                    if (strpos($line, 'Accepted') !== false) $cssClass = 'success-log';
                    if (strpos($line, 'Failed') !== false) $cssClass = 'fail-log';
                    if (strpos($line, 'FLAG') !== false) $cssClass = 'highlight';
                ?>
                <div class="log-line <?= $cssClass ?>"><?= htmlspecialchars($line) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确！</p></div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#ffff00;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
