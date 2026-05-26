<?php
require_once('../flag_helper.php');
$challengeName = 'regex_bypass';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$result = "";

// 模拟正则表达式过滤绕过
$filterPattern = "/flag|admin|root|pass|secret/i";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['input'])) {
        $input = $_POST['input'];
        
        if (preg_match($filterPattern, $input)) {
            $result = "❌ 输入包含敏感词，已被过滤！";
        } else {
            // 如果绕过过滤，检查输入是否包含"flag"的各种变体
            if (stripos($input, 'f') !== false && stripos($input, 'l') !== false && stripos($input, 'a') !== false && stripos($input, 'g') !== false) {
                // 检查是否使用了特殊字符绕过
                if (preg_match('/[^a-zA-Z0-9]/', $input)) {
                    $result = "✅ 正则绕过成功！你使用了: $input<br><br>🎯 " . $flag;
                } else {
                    $result = "✅ 输入通过，但未使用特殊字符绕过。试试加入特殊字符！";
                }
            } else {
                $result = "✅ 输入 '$input' 通过过滤，但内容无关";
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
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>正则表达式绕过 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ffff; }
        .header h1 { font-size: 2.5em; color: #00ffff; text-shadow: 0 0 20px #00ffff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(0,255,255,0.05); border: 1px solid #00ffff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .regex-box { background: #000; border: 2px solid #00ffff; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .regex-box input { width: 100%; padding: 15px; background: #111; border: 1px solid #00ffff; color: #00ffff; font-size: 1.1em; border-radius: 5px; font-family: 'Courier New', monospace; }
        .regex-box button { margin-top: 15px; padding: 12px 40px; background: linear-gradient(135deg, #00ffff, #0088ff); border: none; border-radius: 5px; color: #000; cursor: pointer; }
        .output { background: #1a1a2e; padding: 20px; border-radius: 5px; margin-top: 15px; color: #0f0; }
        .filter-info { background: #000; padding: 15px; border-radius: 5px; color: #ff0; margin: 15px 0; font-size: 0.9em; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #00ffff; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #00ffff; color: #00ffff; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #00ffff, #0088ff); border: none; border-radius: 5px; color: #000; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🔤 正则表达式绕过</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>系统使用正则过滤了敏感词: <code>/flag|admin|root|pass|secret/i</code><br>
            <strong>目标:</strong> 绕过正则过滤，输入包含"flag"字符但绕过检测<br>
            <span style="color:#ff0;">提示:</span> 使用特殊字符分割，如 <code>f.l.a.g</code></p>
        </div>
        
        <div class="regex-box">
            <h3 style="color:#00ffff;margin-bottom:15px;">🔍 正则过滤器</h3>
            <div class="filter-info">
禁止词汇: flag, admin, root, pass, secret<br>
正则: <code>/flag|admin|root|pass|secret/i</code>
            </div>
            <form method="POST">
                <input type="text" name="input" placeholder="尝试输入 f.l.a.g">
                <button type="submit">[ 检测 ]</button>
            </form>
            <?php if ($result): ?>
            <div class="output"><?= $result ?></div>
            <?php endif; ?>
        </div>
        
        <div class="info-box">
            <h3>💡 绕过技巧</h3>
            <p>• 使用点号分割: <code>f.l.a.g</code></p>
            <p>• URL编码: <code>%66%6c%61%67</code></p>
            <p>• Base64编码: <code>ZmxhZw==</code></p>
            <p>• HTML实体: <code>&#102;&#108;&#97;&#103;</code></p>
            <p>• 大小写混合: <code>fLaG</code> (不适用此过滤器，因为使用了 /i)</p>
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
