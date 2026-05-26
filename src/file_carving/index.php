<?php
require_once('../flag_helper.php');
$challengeName = 'file_carving';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// 生成需要"雕刻"恢复的文件
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    
    // 创建一个包含多个文件片段的"损坏"文件
    $carvingFile = '';
    
    // 1. 一些垃圾数据
    $carvingFile .= str_repeat("JUNKDATA" . rand(100, 999) . "\n", 20);
    
    // 2. 嵌入一个JPEG图片的头部+部分数据（实际上不是有效图片）
    $carvingFile .= "\xFF\xD8\xFF\xE0"; // JPEG SOI + APP0
    $carvingFile .= "\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00";
    $carvingFile .= "\xFF\xDB\x00\x43\x00"; // JPEG DQT
    $carvingFile .= "HIDDEN_JPEG_MARKER";
    $carvingFile .= "\xFF\xD9"; // JPEG EOI
    
    // 3. 更多垃圾数据
    $carvingFile .= str_repeat("MOREDATA" . rand(100, 999) . "\n", 30);
    
    // 4. 嵌入一个PNG文件头（模拟）
    $carvingFile .= "\x89PNG\r\n\x1a\n"; // PNG签名
    $carvingFile .= "FLAG_SECTION:" . $flag . ":END_FLAG";
    $carvingFile .= "IEND"; // PNG尾部
    
    // 5. 更多垃圾数据
    $carvingFile .= str_repeat("FILLERDATA" . rand(100, 999) . "\n", 25);
    
    // 6. 嵌入一个PDF文件头
    $carvingFile .= "%PDF-1.4\n";
    $carvingFile .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $carvingFile .= "%%EOF\n";
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="corrupted_data.bin"');
    header('Content-Length: ' . strlen($carvingFile));
    echo $carvingFile;
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
    <title>文件雕刻 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ccff; }
        .header h1 { font-size: 2.5em; color: #00ccff; text-shadow: 0 0 20px #00ccff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(0,204,255,0.05); border: 1px solid #00ccff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .download-section { background: #000; border: 2px solid #00ccff; border-radius: 15px; padding: 40px; text-align: center; margin-bottom: 30px; }
        .download-btn { display: inline-block; padding: 20px 50px; background: linear-gradient(135deg, #00ccff, #0088ff); color: #000; text-decoration: none; border-radius: 5px; font-size: 1.3em; font-weight: bold; }
        .tools-box { background: rgba(0,0,0,0.8); border: 2px solid #00ccff; border-radius: 15px; padding: 25px; margin-bottom: 30px; }
        .tool-item { background: rgba(0,204,255,0.05); border: 1px solid #00ccff; border-radius: 10px; padding: 15px; margin-bottom: 10px; }
        .tool-item strong { color: #ff0; }
        .tool-item code { color: #0ff; display: block; margin-top: 5px; }
        .sig-table { background: #1a1a2e; padding: 15px; border-radius: 5px; margin: 15px 0; font-size: 0.9em; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #00ccff; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #00ccff; color: #00ccff; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #00ccff, #0088ff); border: none; border-radius: 5px; color: #000; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🔪 文件雕刻 <span style="font-size:0.6em;color:#888;">(File Carving)</span></h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>一个损坏的文件中包含多种文件格式的碎片，Flag被隐藏在碎片中。<br>
            <strong>目标:</strong> 使用文件雕刻技术从损坏文件中恢复出Flag<br>
            <span style="color:#ff0;">提示:</span> 搜索 <code>FLAG_SECTION</code> 或使用 <code>binwalk</code> 提取嵌入文件</p>
        </div>
        
        <div class="download-section">
            <a href="?action=download" class="download-btn">[ 📦 下载 corrupted_data.bin ]</a>
        </div>
        
        <div class="tools-box">
            <h3>🛠️ 文件雕刻工具</h3>
            <div class="sig-table">
<strong style="color:#0ff;">常见文件签名:</strong><br>
JPEG: <code>\xFF\xD8\xFF</code> | PNG: <code>\x89PNG</code><br>
PDF: <code>%PDF</code> | ZIP: <code>PK\x03\x04</code><br>
GIF: <code>GIF8</code> | ELF: <code>\x7fELF</code>
            </div>
            <div class="tool-item"><strong>binwalk</strong><code>binwalk -e corrupted_data.bin</code></div>
            <div class="tool-item"><strong>foremost</strong><code>foremost -i corrupted_data.bin -o output/</code></div>
            <div class="tool-item"><strong>strings + grep</strong><code>strings corrupted_data.bin | grep FLAG</code></div>
            <div class="tool-item"><strong>hexdump</strong><code>hexdump -C corrupted_data.bin | grep -A2 "FLAG"</code></div>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确！</p></div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#00ccff;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
