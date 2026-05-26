<?php
require_once('../flag_helper.php');
$challengeName = 'zip_crack';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$zip_password = "1234";

// 创建一个真正的加密ZIP文件（使用系统zip命令）
$tmpDir = '/tmp/zip_challenge';
if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}
file_put_contents($tmpDir . '/flag.txt', $flag);

// 使用系统zip命令创建加密zip
exec("cd $tmpDir && zip -P $zip_password -q ../challenge.zip flag.txt 2>&1", $output, $returnCode);

// 如果系统zip不可用，使用PHP生成一个模拟文件
if ($returnCode !== 0) {
    // 简单的PKZIP格式模拟
    $localHeader = pack('VvvvvvvvVVVvv',
        0x04034b50,          // local file header signature
        20,                  // version needed
        0,                   // general purpose bit flag
        0x0001,              // compression method (stored)
        0, 0, 0, 0,          // last mod file time/date, crc-32
        strlen($flag),       // compressed size
        strlen($flag),       // uncompressed size
        strlen('flag.txt'),  // file name length
        0                    // extra field length
    );
    $zipFile = $localHeader . 'flag.txt' . $flag;
    file_put_contents('/tmp/challenge.zip', $zipFile);
}

$message = "";

// 处理文件下载
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    $zipFile = '/tmp/challenge.zip';
    if (!file_exists($zipFile)) {
        // 如果文件不存在，重新创建
        $localHeader = pack('VvvvvvvvVVVvv',
            0x04034b50, 20, 0, 0x0001,
            0, 0, 0, 0,
            strlen($flag), strlen($flag),
            strlen('flag.txt'), 0
        );
        file_put_contents($zipFile, $localHeader . 'flag.txt' . $flag);
    }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="secret.zip"');
    header('Content-Length: ' . filesize($zipFile));
    readfile($zipFile);
    exit();
}

// 处理Flag提交
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
    <title>ZIP密码破解 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1a1a2e, #16213e); min-height: 100vh; color: #fff; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #e94560; font-size: 2.5em; }
        .header a { color: #e94560; text-decoration: none; }
        .info { background: rgba(233,69,96,0.15); border: 1px solid #e94560; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .box { background: rgba(255,255,255,0.05); border-radius: 15px; padding: 25px; margin-bottom: 20px; }
        .box h3 { color: #e94560; margin-bottom: 15px; }
        .download-btn { display: inline-block; padding: 15px 40px; background: #e94560; border-radius: 8px; color: #fff; text-decoration: none; }
        code { background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 4px; font-family: monospace; }
        .hint { margin-top: 20px; padding: 15px; background: rgba(255,152,0,0.2); border-radius: 8px; border-left: 3px solid #ff9800; }
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
        <div class="header"><h1>📦 ZIP密码破解</h1><p><a href="../index.php">← 返回首页</a></p></div>
        <div class="info"><h3>💡 挑战</h3><p>这是一个加密的ZIP文件，密码是4位数字。<br><strong>使用暴力破解获取Flag!</strong></p></div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！你成功破解了ZIP密码。</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确，请继续破解ZIP！</p></div>
        <?php endif; ?>
        
        <div class="box">
            <h3>📁 加密ZIP文件</h3>
            <p style="margin-bottom:15px;">文件名: <code>secret.zip</code></p>
            <p>提示: 密码是4位数字 (0000-9999)</p>
            <br>
            <a href="?action=download" class="download-btn">📥 下载ZIP文件</a>
        </div>
        <div class="hint">
            <h3>💡 提示</h3>
            <p>密码提示: <strong>简单的数字组合</strong><br><br>
            <strong>破解命令:</strong><br>
            <code>zip2john secret.zip > hash.txt</code><br>
            <code>john --format=zip --mask=?d?d?d?d hash.txt</code></p>
        </div>
        <div class="box" style="margin-top:20px;">
            <h3>📚 常见工具</h3>
            <p>• <code>fcrackzip</code> - 快速ZIP密码破解<br>
            • <code>john the ripper</code> - 通用密码破解<br>
            • <code>hashcat</code> - GPU加速破解</p>
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
