<?php
require_once('../flag_helper.php');
$challengeName = 'pcap';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);

// 创建一个简单的PCAP文件（模拟）
$pcap_header = pack('N', 0xa1b2c3d4) . // magic number
               pack('v', 2) . pack('v', 4) . // version
               pack('N', 0) . pack('N', 0) . // timezone, sigfigs
               pack('N', 65535) . pack('N', 65535); // snaplen, link type

$packet_data = "FLAG: " . $flag . "\x00";

file_put_contents('/tmp/capture.pcap', $pcap_header . $packet_data);

$message = "";

// 处理文件下载
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="capture.pcap"');
    header('Content-Length: ' . filesize('/tmp/capture.pcap'));
    readfile('/tmp/capture.pcap');
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
    <title>流量分析 - HiveYarnZinc</title>
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
        .download-btn { display: inline-block; padding: 15px 40px; background: #e94560; border-radius: 8px; color: #fff; text-decoration: none; }
        code { background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 4px; font-family: monospace; }
        .hex-view { background: #000; padding: 20px; border-radius: 8px; font-family: monospace; white-space: pre; overflow-x: auto; color: #0f0; }
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
        <div class="header"><h1>📦 流量分析</h1><p><a href="../index.php">← 返回首页</a></p></div>
        <div class="info"><h3>💡 挑战</h3><p>这是一个网络流量包文件，使用Wireshark分析找出隐藏的Flag。<br><strong>提示:</strong> 搜索"FLAG"关键字</p></div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！你成功从流量包中提取了隐藏信息。</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确，请继续分析流量包！</p></div>
        <?php endif; ?>
        
        <div class="box">
            <h3>🔍 流量包预览</h3>
            <div class="hex-view">
00000000: 464c 4147 3a20 4869 7665 5961 726e 5a69  FLAG: HiveYarnZi
00000010: 6e63 7b70 3463 6b33 745f 346e 346c 7931  nc{p4ck3t_4n4lys1
00000020: 737d 00                                  s}.    
            </div>
            <br>
            <a href="?action=download" class="download-btn">📥 下载流量包</a>
        </div>
        <div class="box">
            <h3>📚 Wireshark使用技巧</h3>
            <p>• <code>Ctrl+F</code> 搜索关键字或十六进制<br>
            • 使用过滤器: <code>tcp contains "FLAG"</code><br>
            • 右键 → Follow → TCP Stream 查看完整会话<br>
            • Statistics → Endpoints 查看通信端点</p>
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
