<?php
require_once '../auth_check.php';
$flag = "HiveYarnZinc{gif_frame_stego}";
$message = "";

// 生成动态GIF（多帧，其中一帧包含flag）
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    
    // 创建GIF帧
    $frames = [];
    $frameCount = 8;
    $flagFrame = 4; // 第4帧包含flag
    
    for ($i = 0; $i < $frameCount; $i++) {
        $img = imagecreatetruecolor(150, 150);
        
        // 随机背景色
        $bgR = rand(50, 200);
        $bgG = rand(50, 200);
        $bgB = rand(50, 200);
        $bg = imagecolorallocate($img, $bgR, $bgG, $bgB);
        imagefill($img, 0, 0, $bg);
        
        // 绘制文字
        $textColor = imagecolorallocate($img, 255, 255, 255);
        $text = "Frame " . ($i + 1);
        
        if ($i === $flagFrame) {
            $text = "FLAG HERE";
            // 在像素中隐藏flag
            for ($y = 0; $y < 50; $y++) {
                for ($x = 0; $x < 50; $x++) {
                    $rgb = imagecolorat($img, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    // 在蓝色通道最低位隐藏数据
                    if ($x < strlen($flag)) {
                        $bit = (ord($flag[$x]) >> ($y % 8)) & 1;
                        $b = ($b & 0xFE) | $bit;
                        $newColor = imagecolorallocate($img, $r, $g, $b);
                        imagesetpixel($img, $x, $y, $newColor);
                    }
                }
            }
        } else {
            imagestring($img, 5, 40, 65, $text, $textColor);
        }
        
        ob_start();
        imagepng($img);
        $frames[] = ob_get_clean();
    }
    
    // 输出为PNG序列ZIP包
    require_once('../flag_helper.php');
    $flagManager = getFlagManager();
    
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="animation_frames.zip"');
    
    // 创建ZIP包含所有帧
    $zip = new ZipArchive();
    $zipPath = '/tmp/gif_frames_' . session_id() . '.zip';
    
    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
        foreach ($frames as $idx => $frameData) {
            $zip->addFromString("frame_" . sprintf("%02d", $idx) . ".png", $frameData);
        }
        // 添加提示文件
        $hintText = "其中一帧隐藏了Flag，使用图片比较工具分析每帧差异\n使用: compare frame_*.png result.png 或使用StegSolve\nFlag帧: frame_04.png (蓝色通道最低位隐藏flag)";
        $zip->addFromString("README.txt", $hintText);
        $zip->close();
        readfile($zipPath);
        unlink($zipPath);
    }
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
    <title>GIF帧隐写 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ff00; }
        .header h1 { font-size: 2.5em; color: #00ff00; text-shadow: 0 0 20px #00ff00; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(0,255,0,0.05); border: 1px solid #00ff00; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .gif-box { background: #000; border: 2px solid #00ff00; border-radius: 15px; padding: 40px; text-align: center; margin-bottom: 30px; }
        .download-btn { display: inline-block; padding: 20px 50px; background: linear-gradient(135deg, #00ff00, #00aa00); color: #000; text-decoration: none; border-radius: 5px; font-size: 1.3em; font-weight: bold; }
        .download-btn:hover { box-shadow: 0 0 30px rgba(0,255,0,0.5); transform: scale(1.05); }
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
        <div class="header"><h1>🎞️ GIF帧隐写</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>8帧PNG图片中，有一帧隐藏了Flag信息。<br>
            <strong>目标:</strong> 下载所有帧，找出哪一帧包含了隐藏数据<br>
            <span style="color:#ff0;">提示:</span> 使用图片比较工具分析帧间差异</p>
        </div>
        
        <div class="gif-box">
            <h3 style="color:#00ff00;margin-bottom:20px;">📥 下载动画帧</h3>
            <a href="?action=download" class="download-btn">[ 📦 下载所有帧 (ZIP) ]</a>
            <p style="color:#666;margin-top:15px;">包含8帧PNG图片 + 提示文件</p>
        </div>
        
        <div class="tools-box">
            <h3>🛠️ 分析工具</h3>
            <div class="tool-item">
                <strong>StegSolve</strong>
                <code>使用Image Combiner逐帧比较</code>
            </div>
            <div class="tool-item">
                <strong>ImageMagick</strong>
                <code>compare frame_03.png frame_04.png diff.png</code>
            </div>
            <div class="tool-item">
                <strong>Python PIL</strong>
                <code>逐像素比较帧之间的差异</code>
            </div>
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
