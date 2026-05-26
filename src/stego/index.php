<?php
require_once('../flag_helper.php');

$challengeName = 'stego_lsb';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$show_hint = false;

// 处理下载请求（在HTTP头输出之前，清除缓冲区）
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    // 安全清除所有输出缓冲区
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // 生成隐写图片并直接输出
    $width = 200;
    $height = 200;
    $image = imagecreatetruecolor($width, $height);
    
    // 创建渐变背景
    for ($i = 0; $i < $height; $i++) {
        $r = intval(100 + ($i * 50 / $height));
        $g = intval(150 + ($i * 30 / $height));
        $b = intval(200 - ($i * 40 / $height));
        $color = imagecolorallocate($image, $r, $g, $b);
        imageline($image, 0, $i, $width, $i, $color);
    }
    
    // 添加随机像素噪声
    for ($i = 0; $i < 1000; $i++) {
        $x = rand(0, $width - 1);
        $y = rand(0, $height - 1);
        $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        imagesetpixel($image, $x, $y, $color);
    }
    
    // 将flag转换为二进制
    $flagBinary = '';
    for ($i = 0; $i < strlen($flag); $i++) {
        $flagBinary .= str_pad(decbin(ord($flag[$i])), 8, '0', STR_PAD_LEFT);
    }
    $flagBinary .= '00000000';
    
    // LSB隐写：将flag嵌入到蓝色通道的最低位
    $binaryIndex = 0;
    $pixelsNeeded = strlen($flagBinary);
    
    for ($y = 0; $y < $height && $binaryIndex < $pixelsNeeded; $y++) {
        for ($x = 0; $x < $width && $binaryIndex < $pixelsNeeded; $x++) {
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $bit = (int)$flagBinary[$binaryIndex];
            $b = ($b & 0xFE) | $bit;
            $newColor = imagecolorallocate($image, $r, $g, $b);
            imagesetpixel($image, $x, $y, $newColor);
            $binaryIndex++;
        }
    }
    
    // 设置HTTP头
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="challenge_image.png"');
    
    // 输出图片
    imagepng($image);
    // PHP 8+ 自动释放GD资源，无需调用imagedestroy
    exit();
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'hint') {
            $show_hint = true;
        }
        elseif ($_POST['action'] === 'verify') {
            $answer = trim($_POST['answer'] ?? '');
            if ($flagManager->verifyFlag($challengeName, $answer)) {
                $message = "success";
            } else {
                $message = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>图片LSB隐写 - HiveYarnZinc</title>
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
        .container { max-width: 1000px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ff00; }
        .header h1 { font-size: 2.5em; color: #00ff00; text-shadow: 0 0 20px #00ff00; }
        .back-link { color: #00ffff; text-decoration: none; font-size: 1.1em; }
        .back-link:hover { text-shadow: 0 0 10px #00ffff; }
        
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
            transform: scale(1.05);
        }
        
        .tools-box {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #00ff00;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .tools-box h3 { color: #00ff00; margin-bottom: 20px; }
        .tools-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        .tool-item {
            background: rgba(0, 255, 0, 0.05);
            border: 1px solid #00ff00;
            border-radius: 10px;
            padding: 15px;
        }
        .tool-item strong { color: #ff0; display: block; margin-bottom: 8px; }
        .tool-item code { color: #0ff; background: #000; padding: 3px 6px; border-radius: 3px; display: block; margin-top: 5px; }
        .tool-item span { color: #888; font-size: 0.9em; }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 15px 40px;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #00ff00, #00aa00);
            color: #000;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #00ffff, #00ff00);
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.5);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #ff0, #ff8800);
            color: #000;
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #ff8800, #ff0);
        }
        
        .hint-box {
            background: rgba(0, 255, 0, 0.1);
            border: 2px solid #00ff00;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .hint-box h3 { color: #00ff00; margin-bottom: 15px; }
        .hint-box p { color: #aaa; line-height: 1.8; margin-bottom: 15px; }
        .hint-box pre {
            background: #000;
            padding: 15px;
            border-radius: 5px;
            color: #ff0;
            overflow-x: auto;
        }
        
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
        .submit-box input:focus {
            outline: none;
            border-color: #ff0;
            box-shadow: 0 0 20px rgba(255, 255, 0, 0.3);
        }
        
        .stego-demo {
            background: #000;
            border: 2px solid #00ff00;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            font-family: monospace;
            color: #0f0;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎨 图片LSB隐写</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>LSB（最低有效位）隐写术是最常见的图片隐写技术。<br>
            秘密信息被隐藏在图片像素数据的最低位中，人眼几乎无法察觉。<br><br>
            <strong>目标:</strong> 下载图片，使用工具提取其中隐藏的Flag<br>
            <span style="color:#ff0;">提示:</span> Flag隐藏在LSB最低位中，可使用steghide、zsteg或Python脚本提取</p>
        </div>
        
        <div class="download-section">
            <h3 style="color:#00ff00; margin-bottom:20px;">📥 下载隐写图片</h3>
            <p style="color:#aaa; margin-bottom:25px;">每次下载的图片都包含不同的隐藏Flag（动态生成）</p>
            <a href="?action=download" class="download-btn">[ 📷 下载挑战图片 ]</a>
            <p style="color:#666; margin-top:15px; font-size:0.9em;">图片格式: PNG | 尺寸: 200x200 | 隐写方式: LSB</p>
        </div>
        
        <div class="tools-box">
            <h3>🛠️ 常用隐写分析工具</h3>
            <div class="tools-list">
                <div class="tool-item">
                    <strong>🔧 steghide</strong>
                    <code>steghide extract -sf image.png</code>
                    <span>常用隐写提取工具</span>
                </div>
                <div class="tool-item">
                    <strong>🔧 zsteg</strong>
                    <code>zsteg image.png</code>
                    <span>自动检测各种隐写</span>
                </div>
                <div class="tool-item">
                    <strong>🔧 Python PIL</strong>
                    <code>使用Python脚本提取LSB</code>
                    <span>可自定义提取脚本</span>
                </div>
                <div class="tool-item">
                    <strong>🔧 strings</strong>
                    <code>strings image.png | grep Hive</code>
                    <span>快速查找嵌入字符串</span>
                </div>
                <div class="tool-item">
                    <strong>🔧 exiftool</strong>
                    <code>exiftool image.png</code>
                    <span>查看图片元数据</span>
                </div>
                <div class="tool-item">
                    <strong>🔧 binwalk</strong>
                    <code>binwalk -e image.png</code>
                    <span>提取嵌入文件</span>
                </div>
            </div>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result-box success">
            <h2>🎉 恭喜！</h2>
            <p style="font-size:1.3em; margin:15px 0;">Flag正确！</p>
            <p style="font-size:1.1em;">你已成功提取图片中隐藏的信息。</p>
        </div>
        <?php elseif ($message === 'error'): ?>
        <div class="result-box error">
            <h2>❌ 错误</h2>
            <p>Flag不正确，请继续分析图片！</p>
        </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="hint">
                <button type="submit" class="btn btn-primary">[ 💡 查看提示 ]</button>
            </form>
        </div>
        
        <?php if ($show_hint): ?>
        <div class="hint-box">
            <h3>📊 LSB隐写原理与提取方法</h3>
            <p><strong>原理:</strong> 将Flag的每个字符转换为8位二进制，嵌入到图片像素蓝色通道的最低位。</p>
            
            <p style="margin-top:15px;"><strong>Python提取脚本示例:</strong></p>
            <pre>from PIL import Image
import binascii

img = Image.open('challenge_image.png')
pixels = img.load()
width, height = img.size

binary = ""
for y in range(height):
    for x in range(width):
        r, g, b = pixels[x, y]
        binary += str(b & 1)  # 提取蓝色通道最低位
        if len(binary) >= 8 and binary[-8:] == '00000000':
            break

# 二进制转字符串
flag = ""
for i in range(0, len(binary)-8, 8):
    byte = binary[i:i+8]
    flag += chr(int(byte, 2))

print("Hidden Flag:", flag)</pre>
            
            <p style="margin-top:15px;"><strong>提示:</strong> Flag格式为 <code>HiveYarnZinc{...}</code></p>
        </div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#00ff00; margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="hidden" name="action" value="verify">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit" class="btn btn-secondary" style="margin-top:20px;">[ 提交 ]</button>
            </form>
        </div>
        
        <div class="info-box" style="margin-top:30px;">
            <h3>📚 LSB隐写原理</h3>
            <div class="stego-demo">
<span style="color:#0ff;">像素颜色用RGB表示，每个通道0-255</span>
<span style="color:#0f0;">例如: R=214 (11010110), G=109 (01101101), B=153 (10011001)</span>
<span style="color:#ff0;">                                          ↑ 最低位可以隐藏1bit信息</span>

<span style="color:#0ff;">隐藏 'A' (ASCII=65=01000001):</span>
<span style="color:#0f0;">B = 1001100<span style="color:#f00;">1</span> → 修改最低位为0 (第1位)</span>
<span style="color:#0f0;">B = 1001100<span style="color:#f00;">0</span> → 修改最低位为1 (第2位)</span>
<span style="color:#0f0;">... 以此类推，8个像素隐藏1个字母</span>
            </div>
        </div>
    </div>
</body>
</html>
