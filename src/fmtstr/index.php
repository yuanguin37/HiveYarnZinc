<?php
require_once('../flag_helper.php');
$challengeName = 'fmtstr';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$output = "";

// 模拟的"秘密"变量
$secretValue = "the_flag_is_hidden_here";

// 格式化字符串漏洞模拟
if (isset($_GET['input'])) {
    $input = $_GET['input'];
    
    // 模拟格式化字符串漏洞
    if (strpos($input, '%') !== false) {
        if (strpos($input, '%s') !== false) {
            $output = "读取字符串: " . $secretValue;
            if (strpos($input, '%x') !== false) {
                $output .= "\n十六进制: 0x" . bin2hex($secretValue);
            }
            if (strpos($input, '%n') !== false) {
                $output .= "\n⚠️ 写入操作已被阻止 (只读模式)";
            }
        } elseif (strpos($input, '%x') !== false) {
            $output = "栈数据: 0x" . bin2hex("stack_data") . " 0x" . dechex(12345678) . " 0x" . dechex(87654321);
        } elseif (strpos($input, '%p') !== false) {
            $output = "指针: 0x7fff" . dechex(rand(10000000, 99999999));
        } else {
            $output = "格式化输出: " . sprintf($input);
        }
    } else {
        $output = htmlspecialchars($input);
    }
    
    // 如果成功读取到secret
    if (strpos($output, $secretValue) !== false) {
        $output .= "\n\n🎯 🔓 你通过格式化字符串泄露了秘密！\nFlag: " . $flag;
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
    <title>格式化字符串漏洞 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff00ff; }
        .header h1 { font-size: 2.5em; color: #ff00ff; text-shadow: 0 0 20px #ff00ff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,0,255,0.05); border: 1px solid #ff00ff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .fmt-box { background: #000; border: 2px solid #ff00ff; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .fmt-box input { width: 100%; padding: 15px; background: #111; border: 1px solid #ff00ff; color: #ff00ff; font-size: 1.1em; border-radius: 5px; font-family: 'Courier New', monospace; }
        .fmt-box button { margin-top: 15px; padding: 12px 40px; background: linear-gradient(135deg, #ff00ff, #cc00cc); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
        .output { background: #1a1a2e; padding: 20px; border-radius: 5px; margin-top: 15px; white-space: pre-wrap; color: #0f0; }
        .code-box { background: #000; padding: 15px; border-radius: 5px; color: #0f0; margin: 15px 0; font-size: 0.9em; line-height: 1.6; }
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
        <div class="header"><h1>📝 格式化字符串漏洞</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>程序使用 <code>printf(input)</code> 直接输出用户输入，存在格式化字符串漏洞。<br>
            <strong>目标:</strong> 利用 <code>%s</code> 和 <code>%x</code> 等格式化符号泄露内存中的秘密<br>
            <span style="color:#ff0;">提示:</span> 尝试 <code>%s</code> 读取字符串，<code>%x</code> 读取十六进制</p>
        </div>
        
        <div class="fmt-box">
            <h3 style="color:#ff00ff;margin-bottom:15px;">🐞 漏洞程序</h3>
            <div class="code-box">
// 漏洞代码:<br>
printf("用户输入: ");<br>
<span style="color:#f00;">printf(user_input);  // 漏洞！</span><br>
<br>
// 栈上有秘密变量:<br>
char *secret = "the_flag_is_hidden_here";
            </div>
            <form method="GET">
                <input type="text" name="input" placeholder="%s 或 %x %x %x" value="<?= htmlspecialchars($_GET['input'] ?? '') ?>">
                <button type="submit">[ 提交 ]</button>
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
            <h3 style="color:#ff00ff;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
