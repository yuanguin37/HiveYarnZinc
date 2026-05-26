<?php
require_once('../flag_helper.php');
$challengeName = 'js_reverse';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// 生成一个JavaScript混淆函数
$obfuscatedCode = "
// 破解这段JavaScript，获取密码
var _0x4b3c = ['HiveYarnZinc', 'js_deobfuscation', '\\x7b', '\\x7d'];
function _0x5a2f(a, b) {
    return a + '_' + b;
}
function checkFlag(input) {
    var p1 = _0x4b3c[0]; // 'HiveYarnZinc'
    var p2 = _0x4b3c[1]; // 'js_deobfuscation'
    var flag = p1 + _0x4b3c[2] + p2 + _0x4b3c[3];
    return input === flag;
}
// 提示: 在浏览器控制台执行 checkFlag('你的答案')
// 或直接阅读代码找到Flag
";

$showHint = false;
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
    <title>JavaScript逆向 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ffff00; }
        .header h1 { font-size: 2.5em; color: #ffff00; text-shadow: 0 0 20px #ffff00; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,255,0,0.05); border: 1px solid #ffff00; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .code-box { background: #1a1a2e; border: 2px solid #ffff00; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .code-box pre { background: #000; padding: 20px; border-radius: 5px; color: #0f0; overflow-x: auto; font-size: 0.85em; line-height: 1.5; }
        .btn { display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #ffff00, #ffaa00); border: none; border-radius: 5px; color: #000; font-weight: bold; cursor: pointer; text-decoration: none; margin-right: 10px; font-family: 'Courier New', monospace; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #ffff00; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #ffff00; color: #ffff00; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #ffff00, #ffaa00); border: none; border-radius: 5px; color: #000; font-weight: bold; cursor: pointer; }
        .hint-box { background: rgba(255,255,0,0.1); border: 1px solid #ffff00; border-radius: 10px; padding: 20px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🔧 JavaScript逆向</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>分析以下JavaScript代码，找出正确的Flag。<br>
            <strong>目标:</strong> 阅读JS代码，理解其逻辑，找到隐藏的Flag<br>
            <span style="color:#ff0;">提示:</span> <code>\\x7b</code> 和 <code>\\x7d</code> 是花括号的十六进制表示</p>
        </div>
        
        <div class="code-box">
            <h3 style="color:#ffff00;margin-bottom:15px;">📜 JavaScript代码</h3>
            <pre><?= htmlspecialchars($obfuscatedCode) ?></pre>
            <a href="?action=hint" class="btn">[ 💡 提示 ]</a>
            <p style="color:#888;margin-top:15px;">在浏览器控制台粘贴并分析这段代码</p>
        </div>
        
        <?php if ($showHint): ?>
        <div class="hint-box">
            <h4 style="color:#ffff00;">💡 提示</h4>
            <p>• <code>_0x4b3c</code> 是一个数组，里面的字符串被混淆了</p>
            <p>• <code>\\x7b</code> = <code>{</code> , <code>\\x7d</code> = <code>}</code></p>
            <p>• 直接在控制台执行: <code>checkFlag("HiveYarnZinc{js_deobfuscation}")</code></p>
        </div>
        <?php endif; ?>
        
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
