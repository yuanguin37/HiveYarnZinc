<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Hex编码 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff00ff; }
        .header h1 { font-size: 2.5em; color: #ff00ff; text-shadow: 0 0 20px #ff00ff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,0,255,0.05); border: 1px solid #ff00ff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .info-box h3 { color: #ff00ff; margin-bottom: 15px; }
        .cipher-box { background: #000; border: 2px solid #ff00ff; border-radius: 15px; padding: 40px; text-align: center; margin-bottom: 30px; }
        .cipher-text { font-size: 1.8em; color: #ff00ff; word-break: break-all; letter-spacing: 2px; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px solid #ff00ff; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #ff00ff; color: #ff00ff; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #ff00ff, #ff0088); border: none; border-radius: 5px; color: #fff; font-size: 1.1em; font-weight: bold; cursor: pointer; font-family: 'Courier New', monospace; }
        .result { margin-top: 30px; padding: 20px; border-radius: 10px; text-align: center; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .hint { background: rgba(0,0,0,0.5); border: 1px dashed #ff00ff; border-radius: 10px; padding: 20px; margin-top: 30px; }
        .hint h4 { color: #ff00ff; margin-bottom: 10px; }
        .hint p { color: #888; line-height: 1.8; }
        .hint code { color: #ff0; background: #000; padding: 2px 8px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Hex编码</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>Hex（十六进制）编码将每个字符转换为其对应的十六进制表示。<br>
            <strong>目标:</strong> 解码下面的Hex字符串获取Flag</p>
        </div>
        
        <div class="cipher-box">
            <h3 style="color:#ff00ff;margin-bottom:20px;">📝 编码内容:</h3>
            <div class="cipher-text">486976655961726e5a696e637b6865785f6465636f64655f666c61677d</div>
        </div>
        
        <?php
require_once('../flag_helper.php');
$challengeName = 'hex_decode';
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
                <label style="color:#ff00ff;">> 解码后的明文:</label>
                <input type="text" name="answer" placeholder="输入你的答案">
                <button type="submit">[ 提交 ]</button>
            </form>
            
            <?php if ($submitted): ?>
            <div class="result <?= $is_correct ? 'success' : 'error' ?>">
                <?php if ($is_correct): ?>
                    🎉 恭喜！Flag正确！<br><br>
                    <strong style="font-size:1.3em;">🎯 <?= $flag ?></strong>
                <?php else: ?>
                    ❌ Flag不正确，请重试！<br>
                    提示: Hex编码使用0-9和A-F表示
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="hint">
            <h4>📚 Hex编码原理</h4>
            <p>
            <strong>编码规则:</strong><br>
            每个字符用两个十六进制数字表示<br><br>
            
            <strong>示例:</strong><br>
            'A' → 41 (十六进制)<br>
            'B' → 42<br>
            '0' → 30<br><br>
            
            <strong>解码方法:</strong><br>
            <code>echo "48697665" | xxd -r -p</code><br>
            <code>printf "\x48\x69\x76\x65"</code><br>
            Python: <code>bytes.fromhex("48697665").decode()</code>
            </p>
        </div>
    </div>
</body>
</html>