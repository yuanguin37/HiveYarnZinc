<?php
require_once '../auth_check.php';
$flag = "HiveYarnZinc{rce_cat_flag}";
$output = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'] ?? '';
    
    if (!empty($host)) {
        $cmd = "ping -c 1 " . $host;
        if (function_exists('shell_exec')) {
            $output = @shell_exec($cmd . " 2>&1");
        } else {
            $output = "⚠️ shell_exec 被禁用，使用模拟模式\n";
            // 模拟命令执行（用于演示）
            // 检测命令注入
            $injected = false;
            foreach ([';', '|', '&&', '||', '`'] as $char) {
                if (strpos($host, $char) !== false) {
                    $injected = true;
                    break;
                }
            }
            if ($injected) {
                $output .= "\n🎯 Flag: " . $flag;
            } else {
                $output .= "模拟执行: ping -c 1 $host\n正常输出";
            }
        }
        
        if ($output !== null && (strpos($output, 'flag') !== false || strpos($output, '/flag') !== false)) {
            $output .= "\n\n🎯 Flag: " . $flag;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>命令注入 - HiveYarnZinc</title>
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
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff3333; }
        .header h1 { font-size: 2.5em; color: #ff3333; text-shadow: 0 0 20px #ff3333; }
        .back-link { color: #00ffff; text-decoration: none; font-size: 1.1em; }
        .back-link:hover { text-shadow: 0 0 10px #00ffff; }
        
        .terminal-box {
            background: #000;
            border: 2px solid #ff3333;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .terminal-header {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }
        .terminal-btn {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        .terminal-btn.red { background: #ff5f56; }
        .terminal-btn.yellow { background: #ffbd2e; }
        .terminal-btn.green { background: #27c93f; }
        
        .terminal-body {
            min-height: 200px;
            padding: 20px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 5px;
        }
        .terminal-body pre {
            color: #00ff00;
            white-space: pre-wrap;
            line-height: 1.6;
        }
        
        .info-box {
            background: rgba(255, 50, 50, 0.1);
            border: 1px solid #ff3333;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .info-box h3 { color: #ff3333; margin-bottom: 15px; }
        .info-box p { color: #aaa; line-height: 1.8; }
        
        .input-form {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #ff3333;
            border-radius: 15px;
            padding: 40px;
        }
        .input-form label {
            display: block;
            color: #ff3333;
            font-size: 1.2em;
            margin-bottom: 15px;
        }
        .input-form input {
            width: 100%;
            padding: 15px;
            background: #000;
            border: 1px solid #ff3333;
            color: #ff3333;
            font-size: 1.1em;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
        }
        .input-form input:focus {
            outline: none;
            border-color: #ff0;
            box-shadow: 0 0 20px rgba(255, 255, 0, 0.3);
        }
        .input-form button {
            margin-top: 20px;
            padding: 15px 50px;
            background: linear-gradient(135deg, #ff3333, #ff0000);
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Courier New', monospace;
        }
        .input-form button:hover {
            background: linear-gradient(135deg, #ff0, #ff8800);
            color: #000;
        }
        
        .hint { margin-top: 30px; padding: 20px; background: rgba(0,0,0,0.5); border: 1px dashed #ff3333; border-radius: 10px; }
        .hint h4 { color: #ff3333; margin-bottom: 10px; }
        .hint p { color: #888; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💀 命令注入</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>这是一个网络诊断工具，存在命令注入漏洞。<br>
            <strong>目标:</strong> 执行系统命令获取Flag</p>
        </div>
        
        <div class="terminal-box">
            <div class="terminal-header">
                <div class="terminal-btn red"></div>
                <div class="terminal-btn yellow"></div>
                <div class="terminal-btn green"></div>
            </div>
            <div class="terminal-body">
                <pre><?php if($output): ?><?= htmlspecialchars($output) ?><?php else: ?>
root@ctf:~# ./network_tool
网络诊断工具 v1.0
使用此工具测试主机连通性

示例: 192.168.1.1 或 example.com
<?php endif; ?></pre>
            </div>
        </div>
        
        <div class="input-form">
            <form method="POST">
                <label>> 目标主机:</label>
                <input type="text" name="host" placeholder="输入IP或域名，如: 127.0.0.1">
                <button type="submit">[ 执行 Ping ]</button>
            </form>
        </div>
        
        <div class="hint">
            <h4>📚 命令注入知识:</h4>
            <p>常见命令注入技巧:<br>
            <code>; cat /etc/passwd</code><br>
            <code>| ls -la</code><br>
            <code>&& whoami</code><br>
            <code>|| cat flag.txt</code><br>
            <code>`cat flag`</code></p>
        </div>
    </div>
</body>
</html>