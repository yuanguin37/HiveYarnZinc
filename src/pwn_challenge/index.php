<?php
require_once('../flag_helper.php');
$challengeName = 'pwn_challenge';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$output = "";

// 模拟PWN: 栈溢出 - 输入特定长度的数据触发溢出
$secret = "sup3r_s3cr3t_p@ssw0rd";
$bufferSize = 16;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['input'])) {
    $input = $_POST['input'];
    
    // 模拟缓冲区溢出
    if (strlen($input) > $bufferSize) {
        // 溢出触发：覆盖了返回地址
        $overflowData = substr($input, $bufferSize);
        if (strpos($overflowData, 'WIN') !== false) {
            $output = "🎉 缓冲区溢出成功！返回地址已被覆盖！\n$flag";
        } else {
            $output = "⚠️ 检测到溢出，但没有控制返回地址\n目标: 输入长度 > $bufferSize 且包含 'WIN' 关键字";
        }
    } else {
        $output = "✅ 输入正常 ($input)，长度 " . strlen($input) . "，缓冲区大小 $bufferSize";
        if (strlen($input) >= $bufferSize) {
            $output .= "\n⚠️ 长度刚好等于缓冲区，未溢出";
        }
    }
}

if (isset($_POST['answer'])) {
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
    <title>PWN缓冲区溢出 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff3333; }
        .header h1 { font-size: 2.5em; color: #ff3333; text-shadow: 0 0 20px #ff3333; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,50,50,0.05); border: 1px solid #ff3333; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .pwn-box { background: #000; border: 2px solid #ff3333; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .pwn-box input { width: 100%; padding: 15px; background: #111; border: 1px solid #ff3333; color: #ff3333; font-size: 1.1em; border-radius: 5px; font-family: 'Courier New', monospace; }
        .pwn-box button { margin-top: 15px; padding: 12px 40px; background: linear-gradient(135deg, #ff3333, #cc0000); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
        .output { background: #1a1a2e; padding: 20px; border-radius: 5px; margin-top: 15px; white-space: pre-wrap; color: #0f0; }
        .memory-viz { background: #000; padding: 15px; border-radius: 5px; color: #888; font-family: monospace; margin-top: 15px; line-height: 1.5; }
        .memory-viz .buf { color: #0f0; }
        .memory-viz .overflow { color: #f00; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #ff3333; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #ff3333; color: #ff3333; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #ff3333, #cc0000); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>💥 PWN 缓冲区溢出</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>一段C语言程序存在缓冲区溢出漏洞。<br>
            <strong>目标:</strong> 构造Payload，溢出缓冲区并覆盖返回地址<br>
            <span style="color:#ff0;">提示:</span> 输入长度超过16字节，并在溢出数据中包含 <code>WIN</code></p>
        </div>
        
        <div class="pwn-box">
            <h3 style="color:#ff3333;margin-bottom:15px;">🔧 漏洞程序模拟</h3>
            <div class="memory-viz">
<span style="color:#888;">内存布局 (16字节缓冲区):</span><br>
<span class="buf">[ buf (16 bytes) ]</span><span class="overflow">[ 返回地址 (可能被溢出覆盖) ]</span>
            </div>
            <form method="POST">
                <input type="text" name="input" placeholder="AAAAAAAAAAAAAAAA" value="<?= htmlspecialchars($_POST['input'] ?? '') ?>">
                <button type="submit">[ 执行 ]</button>
            </form>
            <?php if ($output): ?>
            <div class="output"><?= nl2br(htmlspecialchars($output)) ?></div>
            <?php endif; ?>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确！</p></div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#ff3333;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
