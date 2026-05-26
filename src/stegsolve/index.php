<?php
require_once('../flag_helper.php');
$challengeName = 'stegsolve';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// 生成包含隐藏flag的图片（不同颜色通道）
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    
    $img = imagecreatetruecolor(300, 200);
    
    // 绘制背景
    for ($y = 0; $y < 200; $y++) {
        $r = intval(200 + ($y * 20 / 200));
        $g = intval(100 + ($y * 30 / 200));
        $b = intval(50 + ($y * 10 / 200));
        $color = imagecolorallocate($img, $r, $g, $b);
        imageline($img, 0, $y, 300, $y, $color);
    }
    
    // 在红色通道中隐藏flag（红色值的最低位）
    for ($x = 0; $x < strlen($flag) * 8; $x++) {
        $y = intval($x / 300);
        $px = $x % 300;
        if ($y < 200) {
            $rgb = imagecolorat($img, $px, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $byteIndex = intval($x / 8);
            $bitIndex = $x % 8;
            if ($byteIndex < strlen($flag)) {
                $bit = (ord($flag[$byteIndex]) >> $bitIndex) & 1;
                $r = ($r & 0xFE) | $bit;
            }
            $newColor = imagecolorallocate($img, $r, $g, $b);
            imagesetpixel($img, $px, $y, $newColor);
        }
    }
    
    // 在绿色通道中写入提示
    $hint = "Check Red channel LSB";
    for ($i = 0; $i < strlen($hint) * 8 && $i < 100; $i++) {
        $rgb = imagecolorat($img, 10 + $i, 180);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $byteIndex = intval($i / 8);
        $bitIndex = $i % 8;
        if ($byteIndex < strlen($hint)) {
            $bit = (ord($hint[$byteIndex]) >> $bitIndex) & 1;
            $g = ($g & 0xFE) | $bit;
            $newColor = imagecolorallocate($img, $r, $g, $b);
            imagesetpixel($img, 10 + $i, 180, $newColor);
        }
    }
    
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="channel_stego.png"');
    imagepng($img);
    exit();
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
    <title>StegSolve通道分析 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ff00; }
        .header h1 { font-size: 2.5em; color: #00ff00; text-shadow: 0 0 20px #00ff00; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(0,255,0,0.05); border: 1px solid #00ff00; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .download-section { background: #000; border: 2px solid #00ff00; border-radius: 15px; padding: 40px; text-align: center; margin-bottom: 30px; }
        .download-btn { display: inline-block; padding: 20px 50px; background: linear-gradient(135deg, #00ff00, #00aa00); color: #000; text-decoration: none; border-radius: 5px; font-size: 1.3em; font-weight: bold; }
        .download-btn:hover { box-shadow: 0 0 30px rgba(0,255,0,0.5); }
        .tools-box { background: rgba(0,0,0,0.8); border: 2px solid #00ff00; border-radius: 15px; padding: 25px; margin-bottom: 30px; }
        .tool-item { background: rgba(0,255,0,0.05); border: 1px solid #00ff00; border-radius: 10px; padding: 15px; margin-bottom: 10px; }
        .tool-item strong { color: #ff0; }
        .tool-item code { color: #0ff; display: block; margin-top: 5px; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #00ff00; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #00ff00; color: #00ff00; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #ff0, #ff8800); border: none; border-radius: 5px; color: #000; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🎨 StegSolve通道分析</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>图片的RGB颜色通道中可能隐藏信息。Flag隐藏在某个颜色通道的最低位。<br>
            <strong>目标:</strong> 使用StegSolve工具分析各颜色通道，找出隐藏的Flag<br>
            <span style="color:#ff0;">提示:</span> 检查红色(Red)通道的最低位(LSB)</p>
        </div>
        
        <div class="download-section">
            <h3 style="color:#00ff00;margin-bottom:20px;">📥 下载挑战图片</h3>
            <a href="?action=download" class="download-btn">[ 📷 下载 channel_stego.png ]</a>
        </div>
        
        <div class="tools-box">
            <h3>🛠️ 分析工具</h3>
            <div class="tool-item"><strong>StegSolve</strong><code>使用 Analyze → Colour Channel 查看各通道</code></div>
            <div class="tool-item"><strong>Python PIL</strong><code>分离RGB通道并检查LSB</code></div>
            <div class="tool-item"><strong>zsteg</strong><code>zsteg channel_stego.png</code></div>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确！</p></div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#00ff00;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
