<?php
require_once('../flag_helper.php');
$challengeName = 'buffer_overflow';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$output = "";
$winAddress = "0x08048444";

// 模拟Buffer Overflow
$secretFunction = "win()";
$bufferLen = 20;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['input'])) {
    $input = $_POST['input'];
    
    if (strlen($input) > $bufferLen && strlen($input) < 100) {
        // 溢出的字节
        $overflowBytes = substr($input, $bufferLen);
        $overflowLen = strlen($overflowBytes);
        
        if (strpos($overflowBytes, 'win') !== false || strpos($overflowBytes, 'WIN') !== false) {
            $output = "🎉 返回地址被成功覆盖为 win() 函数入口!\n\n" . $flag;
        } elseif ($overflowLen >= 4) {
            $output = "💥 缓冲区溢出发生!\n覆盖了 $overflowLen 字节的返回地址\n需要将返回地址指向 win() 函数 ($winAddress)\n提示: 在溢出数据中包含 'win' 或 'WIN'";
        } else {
            $output = "⚠️ 溢出 $overflowLen 字节，需要至少4字节覆盖返回地址";
        }
    } else {
        if (strlen($input) <= $bufferLen) {
            $output = "✅ 输入长度 " . strlen($input) . "，缓冲区未溢出 (容量 $bufferLen)";
        } else {
            $output = "❌ 输入过长 (>100)";
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
    <title>Buffer Overflow - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff4444; }
        .header h1 { font-size: 2.5em; color: #ff4444; text-shadow: 0 0 20px #ff4444; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,68,68,0.05); border: 1px solid #ff4444; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .box { background: #000; border: 2px solid #ff4444; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .box input { width: 100%; padding: 15px; background: #111; border: 1px solid #ff4444; color: #ff4444; font-size: 1.1em; border-radius: 5px; font-family: 'Courier New', monospace; }
        .box button { margin-top: 15px; padding: 12px 40px; background: linear-gradient(135deg, #ff4444, #cc0000); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
        .output { background: #1a1a2e; padding: 20px; border-radius: 5px; margin-top: 15px; white-space: pre-wrap; color: #0f0; }
        .asm-view { background: #000; padding: 15px; border-radius: 5px; color: #888; font-size: 0.9em; line-height: 1.6; margin: 15px 0; }
        .asm-view .target { color: #0f0; }
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
        <div class="header"><h1>🔧 Ret2Win 缓冲区溢出</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>程序中有一个 <code>win()</code> 函数，需要通过缓冲区溢出跳转到它。<br>
            <strong>目标:</strong> 溢出20字节的缓冲区，覆盖返回地址指向 win() 函数<br>
            <span style="color:#ff0;">提示:</span> 输入 20 个填充字节后附加 <code>win</code></p>
        </div>
        
        <div class="box">
            <h3 style="color:#ff4444;margin-bottom:15px;">🔍 反汇编</h3>
            <div class="asm-view">
<span style="color:#888;">0x08048444</span> &lt;win&gt;:<br>
&nbsp;&nbsp;push   %ebp<br>
&nbsp;&nbsp;mov    %esp,%ebp<br>
&nbsp;&nbsp;<span class="target">sub    $0x8,%esp</span><br>
&nbsp;&nbsp;sub    $0xc,%esp<br>
&nbsp;&nbsp;push   $0x8048560<br>
&nbsp;&nbsp;call   puts<br>
&nbsp;&nbsp;nop<br>
&nbsp;&nbsp;leave<br>
&nbsp;&nbsp;ret
            </div>
            <form method="POST">
                <input type="text" name="input" placeholder="AAAAAAAAAAAAAAAAAAAAwin" value="<?= htmlspecialchars($_POST['input'] ?? '') ?>">
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
            <h3 style="color:#ff4444;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
