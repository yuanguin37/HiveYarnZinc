<?php
require_once('../flag_helper.php');
$challengeName = 'hash_collision';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// 常见的弱哈希值
$knownHashes = [
    'MD5' => [
        ['input' => 'admin', 'hash' => md5('admin')],
        ['input' => 'password', 'hash' => md5('password')],
        ['input' => '123456', 'hash' => md5('123456')],
        // 隐藏的flag hash
        ['input' => 'flag', 'hash' => md5($flag)],
    ],
    'SHA1' => [
        ['input' => 'hello', 'hash' => sha1('hello')],
        ['input' => 'world', 'hash' => sha1('world')],
        ['input' => 'ctf', 'hash' => sha1('ctf')],
    ],
    'MD5(flag)' => md5($flag),
];

$showHash = "";
$showHint = false;

if (isset($_GET['type'])) {
    $type = $_GET['type'];
    if ($type === 'md5_flag') {
        $showHash = "MD5(flag) = " . $knownHashes['MD5(flag)'];
    } elseif (isset($_GET['crack'])) {
        $crackValue = $_GET['crack'];
        // 模拟彩虹表查询
        foreach ($knownHashes as $category => $items) {
            if (is_array($items)) {
                foreach ($items as $item) {
                    if ($item['hash'] === $crackValue) {
                        $showHash = "哈希 $crackValue 对应明文: " . $item['input'];
                        break 2;
                    }
                }
            }
        }
        if (empty($showHash)) {
            $showHash = "未找到 '$crackValue' 对应的明文";
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'hint') {
    $showHint = true;
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
    <title>哈希碰撞 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ccff; }
        .header h1 { font-size: 2.5em; color: #00ccff; text-shadow: 0 0 20px #00ccff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(0,204,255,0.05); border: 1px solid #00ccff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .hash-box { background: #000; border: 2px solid #00ccff; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .hash-box input { width: 100%; padding: 15px; background: #111; border: 1px solid #00ccff; color: #00ccff; font-size: 0.9em; border-radius: 5px; font-family: 'Courier New', monospace; margin-bottom: 10px; }
        .hash-box button { padding: 12px 40px; background: linear-gradient(135deg, #00ccff, #0088ff); border: none; border-radius: 5px; color: #000; cursor: pointer; }
        .hash-links { margin: 15px 0; }
        .hash-links a { color: #0ff; margin: 0 10px; }
        .output { background: #1a1a2e; padding: 15px; border-radius: 5px; margin-top: 15px; color: #0f0; word-break: break-all; }
        .btn { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #00ccff, #0088ff); border: none; border-radius: 5px; color: #000; cursor: pointer; text-decoration: none; margin-top: 10px; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #00ccff; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #00ccff; color: #00ccff; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #00ccff, #0088ff); border: none; border-radius: 5px; color: #000; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🔐 哈希破解</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>服务器存储了哈希值，你需要找出Flag对应的明文。<br>
            <strong>目标:</strong> 使用彩虹表或在线服务破解MD5哈希<br>
            <span style="color:#ff0;">提示:</span> 查看已知哈希对照表，尝试在线MD5破解</p>
        </div>
        
        <div class="hash-box">
            <h3 style="color:#00ccff;margin-bottom:15px;">📊 已知哈希表</h3>
            <?php foreach ($knownHashes as $category => $items): ?>
                <?php if (is_array($items)): ?>
                <p style="color:#888;"><?= $category ?>:</p>
                <?php foreach ($items as $item): ?>
                    <p><code style="color:#0f0;"><?= $item['input'] ?>: <?= $item['hash'] ?></code></p>
                <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <hr style="border-color:#333;margin:15px 0;">
            <p><strong style="color:#ff0;">🎯 目标哈希 (MD5):</strong></p>
            <p><code style="color:#0f0;font-size:1.2em;"><?= $knownHashes['MD5(flag)'] ?></code></p>
            
            <div class="hash-links">
                <a href="?type=md5_flag">查看MD5格式</a>
                <a href="?action=hint">💡 提示</a>
            </div>
            
            <form method="GET" style="margin-top:15px;">
                <input type="text" name="crack" placeholder="输入MD5哈希值查询明文">
                <button type="submit">[ 查询 ]</button>
            </form>
            <?php if ($showHash): ?>
            <div class="output"><?= htmlspecialchars($showHash) ?></div>
            <?php endif; ?>
        </div>
        
        <?php if ($showHint): ?>
        <div class="info-box">
            <h3>💡 提示</h3>
            <p>• 使用 <a href="https://crackstation.net" style="color:#0ff;" target="_blank">crackstation.net</a> 在线破解</p>
            <p>• 或使用 <code>hashcat -m 0 hash.txt rockyou.txt</code></p>
            <p>• Flag格式: <code>HiveYarnZinc{...}</code></p>
        </div>
        <?php endif; ?>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确！</p></div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#00ccff;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
