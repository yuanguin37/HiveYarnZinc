<?php
require_once('../flag_helper.php');
$challengeName = 'php_type';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

$result = "";

// 模拟PHP弱类型比较
if (isset($_GET['a']) && isset($_GET['b'])) {
    $a = $_GET['a'];
    $b = $_GET['b'];
    
    if ($a == "admin" && $b == "admin") {
        $result = "普通登录 - 无特殊信息";
    }
    
    // 漏洞：弱类型比较
    if ($a == 0 && $b == 0) {
        $result = "两个0的比较 - 普通结果";
    }
    
    // 关键漏洞：字符串与数字的弱比较
    if ($a == 0 && $b != 0) {
        $result = "注意！\$a($a) 与 \$b($b) 不同但 \$a==0 为真";
    }
    
    // MD5碰撞漏洞
    if (md5($a) == md5($b) && $a !== $b) {
        $result = $flag;
    }
    
    // 如果没匹配到任何条件
    if (empty($result)) {
        $result = "比较结果: \$a=$a, \$b=$b, md5(\$a)=" . md5($a) . ", md5(\$b)=" . md5($b);
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
    <title>PHP类型混淆 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff8800; }
        .header h1 { font-size: 2.5em; color: #ff8800; text-shadow: 0 0 20px #ff8800; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,136,0,0.05); border: 1px solid #ff8800; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .type-box { background: #000; border: 2px solid #ff8800; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .type-box input { width: 48%; padding: 15px; background: #111; border: 1px solid #ff8800; color: #ff8800; font-size: 1em; border-radius: 5px; font-family: 'Courier New', monospace; margin-bottom: 10px; }
        .type-box button { padding: 12px 40px; background: linear-gradient(135deg, #ff8800, #ff4400); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
        .output { background: #1a1a2e; padding: 20px; border-radius: 5px; margin-top: 15px; color: #0f0; word-break: break-all; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #ff8800; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #ff8800; color: #ff8800; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #ff8800, #ff4400); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
        .php-code { background: #1a1a2e; padding: 15px; border-radius: 5px; color: #ff0; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🐘 PHP类型混淆</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>PHP的 <code>==</code>（弱类型比较）与 <code>===</code>（强类型比较）行为不同。<br>
            <strong>目标:</strong> 利用MD5碰撞漏洞（PHP弱类型比较）获取Flag<br>
            <span style="color:#ff0;">提示:</span> 寻找两个值，使 <code>md5(\$a) == md5(\$b)</code> 但 <code>\$a !== \$b</code></p>
        </div>
        
        <div class="type-box">
            <h3 style="color:#ff8800;margin-bottom:15px;">🔬 PHP比较测试器</h3>
            <div class="php-code">
// 关键代码:
// if (md5(\$a) == md5(\$b) && \$a !== \$b) {
//     echo \$flag;
// }
            </div>
            <form method="GET">
                <input type="text" name="a" placeholder="参数 a" value="<?= htmlspecialchars($_GET['a'] ?? '') ?>">
                <input type="text" name="b" placeholder="参数 b" value="<?= htmlspecialchars($_GET['b'] ?? '') ?>">
                <button type="submit">[ 比较 ]</button>
            </form>
            <?php if ($result): ?>
            <div class="output"><?= nl2br(htmlspecialchars($result)) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="info-box">
            <h3>🔑 经典MD5碰撞</h3>
            <p>PHP的 <code>==</code> 在比较以 <code>0e</code> 开头的字符串时会视为科学计数法：</p>
            <p><code>0e12345</code> == <code>0e67890</code> → <code>true</code></p>
            <p>因为两者都被解析为 <code>0 * 10^n = 0</code></p>
            <p>已知碰撞: <code>240610708</code> 和 <code>QNKCDZO</code></p>
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
