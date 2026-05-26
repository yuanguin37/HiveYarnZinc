<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>URL编码 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ffff; }
        .header h1 { font-size: 2.5em; color: #00ffff; text-shadow: 0 0 20px #00ffff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(0,255,255,0.05); border: 1px solid #00ffff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .info-box h3 { color: #00ffff; margin-bottom: 15px; }
        .info-box p { color: #aaa; line-height: 1.8; }
        .cipher-box { background: #000; border: 2px solid #00ffff; border-radius: 15px; padding: 40px; text-align: center; margin-bottom: 30px; }
        .cipher-text { font-size: 1.5em; color: #00ffff; word-break: break-all; letter-spacing: 2px; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px solid #00ffff; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #00ffff; color: #00ffff; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #00ffff, #0088ff); border: none; border-radius: 5px; color: #000; font-size: 1.1em; font-weight: bold; cursor: pointer; font-family: 'Courier New', monospace; }
        .result { margin-top: 30px; padding: 20px; border-radius: 10px; text-align: center; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .hint { background: rgba(0,0,0,0.5); border: 1px dashed #00ffff; border-radius: 10px; padding: 20px; margin-top: 30px; }
        .hint h4 { color: #00ffff; margin-bottom: 15px; }
        .hint p { color: #888; line-height: 1.8; }
        .hint code { color: #ff0; background: #000; padding: 2px 8px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔗 URL编码</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>URL编码（Percent-Encoding）用于转义特殊字符，使其可以在URL中传输。<br>
            <strong>目标:</strong> 解码下面的URL编码字符串获取Flag</p>
        </div>
        
        <div class="cipher-box">
            <h3 style="color:#00ffff;margin-bottom:20px;">🔒 URL编码:</h3>
            <div class="cipher-text">HiveYarnZinc%7Burl_decode_easy%7D</div>
        </div>
        
        <?php
require_once('../flag_helper.php');
$challengeName = 'url_decode';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$submitted = false;
$is_correct = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = true;
    $answer = trim($_POST['answer'] ?? '');
    $is_correct = (strtoupper($answer) === strtoupper($flag));
}
?>
        
        <div class="submit-box">
            <form method="POST">
                <label style="color:#00ffff;">> 解码后的明文:</label>
                <input type="text" name="answer" placeholder="输入你的答案">
                <button type="submit">[ 提交 ]</button>
            </form>
            
            <?php if ($submitted): ?>
            <div class="result <?= $is_correct ? 'success' : 'error' ?>">
                <?php if ($is_correct): ?>
                    🎉 恭喜！URL解码成功！<br><br>
                    <strong style="font-size:1.3em;">🎯 <?= $flag ?></strong>
                <?php else: ?>
                    ❌ 答案不正确！<br>
                    提示: %XX 格式表示十六进制数
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="hint">
            <h4>📚 URL编码对照</h4>
            <p>
            <strong>常见字符编码:</strong><br>
            <code>%20</code> = 空格 (space)<br>
            <code>%7B</code> = { (左花括号)<br>
            <code>%7D</code> = } (右花括号)<br>
            <code>%3D</code> = = (等号)<br>
            <code>%2F</code> = / (斜杠)<br>
            <code>%25</code> = % (百分号本身)<br><br>
            
            <strong>解码方法:</strong><br>
            Python: <code>from urllib.parse import unquote; unquote("...")</code><br>
            JavaScript: <code>decodeURIComponent("...")</code><br>
            PHP: <code>urldecode("...")</code><br><br>
            
            <strong>原理:</strong><br>
            % 后跟两个十六进制数字 = 该字符的ASCII码<br>
            例如: %41 = 0x41 = 65 = 'A'
            </p>
        </div>
    </div>
</body>
</html>