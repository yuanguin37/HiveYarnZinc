<?php
require_once('../flag_helper.php');
$challengeName = 'forensics';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$hidden_data = $flag;
$dummy_data = str_repeat("This is dummy data to hide the flag. ", 100);
$combined = substr($dummy_data, 0, 500) . $hidden_data . substr($dummy_data, 500);

file_put_contents('/tmp/forensics_challenge.bin', $combined);
file_put_contents('/tmp/forensics_hex.txt', bin2hex($combined));

$message = "";

// 处理文件下载
if (isset($_GET['action'])) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    
    if ($_GET['action'] === 'download_bin') {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="forensics_challenge.bin"');
        header('Content-Length: ' . filesize('/tmp/forensics_challenge.bin'));
        readfile('/tmp/forensics_challenge.bin');
        exit();
    } elseif ($_GET['action'] === 'download_hex') {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="forensics_hex.txt"');
        header('Content-Length: ' . filesize('/tmp/forensics_hex.txt'));
        readfile('/tmp/forensics_hex.txt');
        exit();
    }
}

// 处理Flag提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $answer = trim($_POST['answer']);
    if ($answer === $hidden_data) {
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
    <title>数字取证 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1a1a2e, #16213e); min-height: 100vh; color: #fff; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #e94560; font-size: 2.5em; }
        .header a { color: #e94560; text-decoration: none; }
        .info { background: rgba(233,69,96,0.15); border: 1px solid #e94560; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .box { background: rgba(255,255,255,0.05); border-radius: 15px; padding: 25px; margin-bottom: 20px; }
        .box h3 { color: #e94560; margin-bottom: 15px; }
        .download-btn { display: inline-block; padding: 15px 40px; background: #e94560; border-radius: 8px; color: #fff; text-decoration: none; margin: 10px; }
        code { background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 4px; font-family: monospace; }
        .hex-view { background: #000; padding: 20px; border-radius: 8px; font-family: monospace; white-space: pre; overflow-x: auto; color: #0f0; font-size: 0.9em; max-height: 300px; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { margin-top: 20px; padding: 25px; background: rgba(255,255,255,0.03); border-radius: 10px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 12px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: rgba(0,0,0,0.3); color: #fff; font-size: 1em; text-align: center; }
        .submit-box button { margin-top: 10px; padding: 12px 40px; background: #e94560; border: none; border-radius: 8px; color: #fff; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🔍 数字取证</h1><p><a href="../index.php">← 返回首页</a></p></div>
        <div class="info"><h3>💡 挑战</h3><p>这是一个二进制取证文件，Flag被隐藏在大量垃圾数据中。<br><strong>使用strings命令或十六进制编辑器找出Flag!</strong></p></div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！你成功从文件中提取了隐藏信息。</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确，请继续分析文件！</p></div>
        <?php endif; ?>
        
        <div class="box">
            <h3>🔍 文件十六进制预览</h3>
            <div class="hex-view"><?= chunk_split(bin2hex(substr($combined, 0, 1000)), 48, "\n") ?></div>
            <br>
            <a href="?action=download_bin" class="download-btn">📥 下载二进制文件</a>
            <a href="?action=download_hex" class="download-btn">📥 下载Hex文本</a>
        </div>
        
        <div class="box">
            <h3>📚 取证工具</h3>
            <p>• <code>strings filename</code> - 提取可打印字符串<br>
            • <code>hexdump -C filename</code> - 查看十六进制<br>
            • <code>binwalk filename</code> - 分析文件结构<br>
            • <code>foremost -i filename</code> - 文件恢复</p>
        </div>
        
        <div class="submit-box">
            <h3 style="color:#e94560;margin-bottom:15px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="输入你找到的Flag">
                <button type="submit">提交</button>
            </form>
        </div>
    </div>
</body>
</html>
