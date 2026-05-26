<?php
require_once('../flag_helper.php');

$challengeName = 'metadata';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// 动态生成带有隐藏元数据的图片
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    // 安全清除所有输出缓冲区
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // 创建图片
    $img = imagecreatetruecolor(400, 300);
    
    // 绘制渐变背景
    for ($y = 0; $y < 300; $y++) {
        $r = intval(100 + ($y * 50 / 300));
        $g = intval(100 + ($y * 30 / 300));
        $b = intval(200 - ($y * 40 / 300));
        $color = imagecolorallocate($img, $r, $g, $b);
        imageline($img, 0, $y, 400, $y, $color);
    }
    
    // 添加一些文字
    $textColor = imagecolorallocate($img, 255, 255, 255);
    imagestring($img, 5, 150, 140, "CTF Challenge", $textColor);
    
    // 保存为PNG到内存
    ob_start();
    imagepng($img);
    $pngData = ob_get_clean();
    
    // 在PNG数据末尾添加flag
    $hiddenData = "\n<!-- " . $flag . " -->\n";
    $pngData .= $hiddenData;
    
    // 设置HTTP头并输出
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="ctf_image.png"');
    header('Content-Length: ' . strlen($pngData));
    echo $pngData;
    exit();
}

// 处理提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answer = trim($_POST['answer'] ?? '');
    if ($flagManager->verifyFlag($challengeName, $answer)) {
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
    <title>图片元数据 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: #0a0a0f;
            color: #00ff00;
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ff00; }
        .header h1 { font-size: 2.5em; color: #00ff00; text-shadow: 0 0 20px #00ff00; }
        .back-link { color: #00ffff; text-decoration: none; font-size: 1.1em; }
        
        .info-box {
            background: rgba(0, 255, 0, 0.05);
            border: 1px solid #00ff00;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .info-box h3 { color: #00ff00; margin-bottom: 15px; }
        .info-box p { color: #aaa; line-height: 1.8; }
        .info-box code { background: #000; padding: 2px 8px; border-radius: 3px; color: #ff0; }
        
        .download-section {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #00ff00;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
        }
        .download-btn {
            display: inline-block;
            padding: 20px 50px;
            background: linear-gradient(135deg, #00ff00, #00aa00);
            color: #000;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.3em;
            font-weight: bold;
            transition: all 0.3s;
            font-family: 'Courier New', monospace;
        }
        .download-btn:hover {
            background: linear-gradient(135deg, #00ffff, #00ff00);
            box-shadow: 0 0 30px rgba(0, 255, 255, 0.5);
        }
        
        .tools-box {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #00ff00;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .tools-box h3 { color: #00ff00; margin-bottom: 20px; }
        .tool-item {
            background: rgba(0, 255, 0, 0.05);
            border: 1px solid #00ff00;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .tool-item strong { color: #ff0; }
        .tool-item code { color: #0ff; background: #000; padding: 3px 6px; border-radius: 3px; display: block; margin-top: 5px; }
        
        .result-box {
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        .result-box.success {
            background: rgba(0, 255, 0, 0.1);
            border: 2px solid #00ff00;
            color: #00ff00;
        }
        .result-box.error {
            background: rgba(255, 0, 0, 0.1);
            border: 2px solid #ff0000;
            color: #ff0000;
        }
        
        .submit-box {
            background: rgba(0, 0, 0, 0.8);
            border: 2px dashed #00ff00;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
        }
        .submit-box input {
            width: 100%;
            max-width: 400px;
            padding: 15px;
            background: #000;
            border: 1px solid #00ff00;
            color: #00ff00;
            font-size: 1.2em;
            border-radius: 5px;
            text-align: center;
            font-family: 'Courier New', monospace;
        }
        .btn {
            padding: 15px 40px;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #ff0, #ff8800);
            color: #000;
            margin-top: 20px;
        }
        
        .hint-box {
            background: rgba(0, 255, 0, 0.1);
            border: 2px solid #00ff00;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .hint-box h3 { color: #00ff00; margin-bottom: 15px; }
        .hint-box pre {
            background: #000;
            padding: 15px;
            border-radius: 5px;
            color: #ff0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📸 图片元数据</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>这张CTF挑战图片看起来普普通通，但图片中可能藏着一些元数据...<br>
            摄影师在上传图片前通常会保留一些信息，比如作者、拍摄地点等。<br><br>
            <strong>目标:</strong> 分析图片的元数据，找出Flag<br>
            <span style="color:#ff0;">提示:</span> 使用元数据查看工具，Flag可能隐藏在评论或作者字段中</p>
        </div>
        
        <div class="download-section">
            <h3 style="color:#00ff00; margin-bottom:20px;">📥 下载图片</h3>
            <p style="color:#aaa; margin-bottom:25px;">下载这张挑战图片并分析其元数据</p>
            <a href="?action=download" class="download-btn">[ 🖼️ 下载 ctf_image.png ]</a>
        </div>
        
        <div class="tools-box">
            <h3>🛠️ 元数据分析工具</h3>
            <div class="tool-item">
                <strong>exiftool</strong>
                <code>exiftool ctf_image.png</code>
                <span>查看完整的EXIF和元数据信息</span>
            </div>
            <div class="tool-item">
                <strong>exif</strong>
                <code>exif ctf_image.png</code>
                <span>快速查看EXIF信息</span>
            </div>
            <div class="tool-item">
                <strong>pngcheck</strong>
                <code>pngcheck -v ctf_image.png</code>
                <span>检查PNG完整性并显示tEXt chunks</span>
            </div>
            <div class="tool-item">
                <strong>strings</strong>
                <code>strings ctf_image.png | grep -i hive</code>
                <span>查找文件中的字符串</span>
            </div>
            <div class="tool-item">
                <strong>hexdump</strong>
                <code>hexdump -C ctf_image.png | tail -20</code>
                <span>查看文件末尾的隐藏数据</span>
            </div>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result-box success">
            <h2>🎉 恭喜！</h2>
            <p style="font-size:1.3em; margin:15px 0;">Flag正确！你成功从元数据中提取了信息。</p>
        </div>
        <?php elseif ($message === 'error'): ?>
        <div class="result-box error">
            <h2>❌ 错误</h2>
            <p>Flag不正确，请继续分析元数据！</p>
        </div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#00ff00; margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit" class="btn">提交</button>
            </form>
        </div>
    </div>
</body>
</html>
