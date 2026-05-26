<?php
require_once('../flag_helper.php');
$challengeName = 'ssti';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$output = "";

// 模拟SSTI：用户输入被放到模板中执行
$allowedNames = ['guest', 'user', 'admin', 'root'];

if (isset($_GET['name'])) {
    $name = $_GET['name'];
    
    // 模拟模板渲染: Hello, {name}!
    if (preg_match('/\{\{.*\}\}|#\{.*\}|\$\{.*\}|<\%.*\%\>/', $name)) {
        // 检测到SSTI尝试
        if (strpos($name, 'flag') !== false || strpos($name, 'config') !== false) {
            $output = "渲染结果: Hello, $name!\n\n🎯 " . $flag;
        } elseif (strpos($name, '7*7') !== false) {
            $output = "渲染结果: Hello, 49!"; // 确认SSTI存在
        } elseif (strpos($name, "'") !== false || strpos($name, '"') !== false) {
            $output = "渲染结果: Hello, " . htmlspecialchars($name) . "! ⚠️ 检测到字符串注入";
        } elseif (preg_match('/\$/', $name)) {
            $output = "渲染结果: Hello, " . htmlspecialchars($name) . "!\n[变量解析: 未定义变量]";
        } else {
            $output = "渲染结果: Hello, " . htmlspecialchars($name) . "!\n[SSTI语法被检测但未命中目标]";
        }
    } else {
        $output = "渲染结果: Hello, " . htmlspecialchars($name) . "!";
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
    <title>SSTI模板注入 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ffff00; }
        .header h1 { font-size: 2.5em; color: #ffff00; text-shadow: 0 0 20px #ffff00; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,255,0,0.05); border: 1px solid #ffff00; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .ssti-box { background: #000; border: 2px solid #ffff00; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .ssti-box input { width: 100%; padding: 15px; background: #111; border: 1px solid #ffff00; color: #ffff00; font-size: 1.1em; border-radius: 5px; font-family: 'Courier New', monospace; }
        .ssti-box button { margin-top: 15px; padding: 12px 40px; background: linear-gradient(135deg, #ffff00, #ffaa00); border: none; border-radius: 5px; color: #000; cursor: pointer; }
        .template-demo { background: #1a1a2e; padding: 15px; border-radius: 5px; color: #888; margin: 15px 0; }
        .template-demo code { color: #0f0; }
        .output { background: #1a1a2e; padding: 20px; border-radius: 5px; margin-top: 15px; white-space: pre-wrap; color: #0f0; }
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
        <div class="header"><h1>🔨 SSTI模板注入</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>服务端使用模板引擎渲染用户输入，存在SSTI漏洞。<br>
            <strong>目标:</strong> 通过模板注入获取Flag<br>
            <span style="color:#ff0;">提示:</span> 尝试 <code>{{config}}</code> 或 <code>{{7*7}}</code></p>
        </div>
        
        <div class="ssti-box">
            <h3 style="color:#ffff00;margin-bottom:15px;">🎨 模板渲染器</h3>
            <div class="template-demo">
模板: <code>Hello, {{name}}!</code><br>
<span style="color:#666;">提示: 尝试注入模板语法</span>
            </div>
            <form method="GET">
                <input type="text" name="name" placeholder="{{7*7}}" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
                <button type="submit">[ 渲染 ]</button>
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
            <h3 style="color:#ffff00;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
