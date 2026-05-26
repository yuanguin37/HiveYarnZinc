<?php
require_once('../flag_helper.php');
$challengeName = 'disk_forensics';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// 生成模拟磁盘镜像
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    
    // 创建模拟磁盘镜像: MBR + 分区 + 隐藏数据
    $diskImage = '';
    
    // MBR (512字节)
    $diskImage .= str_repeat("\x00", 446); // 引导代码
    // 分区表: 1个FAT32分区
    $diskImage .= pack('C', 0x80); // 可引导
    $diskImage .= pack('C*', 0, 1, 0); // CHS起始
    $diskImage .= pack('C', 0x0B); // FAT32
    $diskImage .= pack('C*', 0xFE, 0xFF, 0xFF); // CHS结束
    $diskImage .= pack('V', 2048); // LBA起始扇区
    $diskImage .= pack('V', 409600); // 扇区数 (200MB)
    $diskImage .= str_repeat("\x00", 48); // 剩余分区表
    $diskImage .= "\x55\xAA"; // 引导标记
    
    // 文件数据区域
    $diskImage .= str_repeat("This is a normal file.\n", 50);
    $diskImage .= "FLAG: " . $flag . "\n";
    $diskImage .= str_repeat("More normal data...\n", 50);
    
    // 在特定偏移写入隐藏数据
    $diskImage = substr_replace($diskImage, "\x00\x00\x00\x00HIDDEN:" . $flag . ":END", 4096, 0);
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="disk_image.dd"');
    header('Content-Length: ' . strlen($diskImage));
    echo $diskImage;
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
    <title>磁盘取证 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff00ff; }
        .header h1 { font-size: 2.5em; color: #ff00ff; text-shadow: 0 0 20px #ff00ff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,0,255,0.05); border: 1px solid #ff00ff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .download-section { background: #000; border: 2px solid #ff00ff; border-radius: 15px; padding: 40px; text-align: center; margin-bottom: 30px; }
        .download-btn { display: inline-block; padding: 20px 50px; background: linear-gradient(135deg, #ff00ff, #cc00cc); color: #fff; text-decoration: none; border-radius: 5px; font-size: 1.3em; font-weight: bold; }
        .tools-box { background: rgba(0,0,0,0.8); border: 2px solid #ff00ff; border-radius: 15px; padding: 25px; margin-bottom: 30px; }
        .tool-item { background: rgba(255,0,255,0.05); border: 1px solid #ff00ff; border-radius: 10px; padding: 15px; margin-bottom: 10px; }
        .tool-item strong { color: #ff0; }
        .tool-item code { color: #0ff; display: block; margin-top: 5px; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #ff00ff; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #ff00ff; color: #ff00ff; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #ff00ff, #cc00cc); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>💾 磁盘取证</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>获取到一个磁盘镜像文件，其中包含被隐藏的数据。<br>
            <strong>目标:</strong> 分析磁盘镜像，恢复被隐藏的Flag<br>
            <span style="color:#ff0;">提示:</span> 使用 <code>strings</code>、<code>hexdump</code> 或 <code>binwalk</code> 分析</p>
        </div>
        
        <div class="download-section">
            <a href="?action=download" class="download-btn">[ 💾 下载 disk_image.dd ]</a>
        </div>
        
        <div class="tools-box">
            <h3>🛠️ 取证工具</h3>
            <div class="tool-item"><strong>strings</strong><code>strings disk_image.dd | grep Hive</code></div>
            <div class="tool-item"><strong>hexdump</strong><code>hexdump -C disk_image.dd | grep -A5 HIDDEN</code></div>
            <div class="tool-item"><strong>binwalk</strong><code>binwalk disk_image.dd</code></div>
            <div class="tool-item"><strong>foremost</strong><code>foremost -i disk_image.dd -o output/</code></div>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确！</p></div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#ff00ff;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
