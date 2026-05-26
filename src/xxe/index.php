<?php
require_once('../flag_helper.php');
$challengeName = 'xxe';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$output = "";

// 模拟的"内部文件"
$internalFiles = [
    '/etc/passwd' => "root:x:0:0:root:/root:/bin/bash\nwww-data:x:33:33:www-data:/var/www:/usr/sbin/nologin\n",
    'flag.txt' => $flag,
    '/flag' => $flag,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['xml'])) {
        $xml = $_POST['xml'];
        
        // 模拟XXE漏洞解析
        if (preg_match('/<!ENTITY/', $xml) && preg_match('/SYSTEM|PUBLIC/', $xml)) {
            // 提取实体名称和文件路径
            preg_match('/<!ENTITY\s+(\w+)\s+SYSTEM\s+[\'"]([^\'"]+)[\'"]\s*>/', $xml, $matches);
            if (count($matches) >= 3) {
                $entityName = $matches[1];
                $filePath = $matches[2];
                $baseName = basename($filePath);
                
                if (isset($internalFiles[$filePath])) {
                    $fileContent = $internalFiles[$filePath];
                } elseif (isset($internalFiles['/' . $baseName])) {
                    $fileContent = $internalFiles['/' . $baseName];
                } elseif (isset($internalFiles[$baseName])) {
                    $fileContent = $internalFiles[$baseName];
                } else {
                    $fileContent = "错误: 文件 $filePath 不存在";
                }
                
                // 检查XML中是否引用了实体
                if (preg_match('/&' . $entityName . ';/', $xml)) {
                    $output = "XXE注入成功！\n\n读取文件: $filePath\n内容:\n$fileContent";
                } else {
                    $output = "定义了实体但未引用。在XML中使用 &$entityName; 来引用它。";
                }
            } else {
                $output = "XXE格式似乎不正确。格式: <!ENTITY name SYSTEM 'file:///path'>";
            }
        } elseif (strpos($xml, '<?xml') !== false) {
            $output = "XML解析完成，但没有检测到XXE (DOCTYPE/ENTITY)";
        } else {
            $output = "这不是有效的XML格式";
        }
    }
    
    if (isset($_POST['answer'])) {
        $answer = trim($_POST['answer']);
        if ($answer === $flag) {
            $message = "success";
        } else {
            $message = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>XXE注入 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff00ff; }
        .header h1 { font-size: 2.5em; color: #ff00ff; text-shadow: 0 0 20px #ff00ff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,0,255,0.05); border: 1px solid #ff00ff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .xxe-box { background: #000; border: 2px solid #ff00ff; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .xxe-box textarea { width: 100%; height: 180px; padding: 15px; background: #111; border: 1px solid #ff00ff; color: #0f0; font-size: 0.9em; border-radius: 5px; font-family: 'Courier New', monospace; }
        .xxe-box button { margin-top: 15px; padding: 12px 40px; background: linear-gradient(135deg, #ff00ff, #cc00cc); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
        .output { background: #1a1a2e; padding: 20px; border-radius: 5px; margin-top: 15px; white-space: pre-wrap; color: #0f0; }
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
        <div class="header"><h1>📄 XXE外部实体注入</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>XML解析器未禁用外部实体，存在XXE漏洞。<br>
            <strong>目标:</strong> 构造XXE Payload读取服务器上的 <code>flag.txt</code><br>
            <span style="color:#ff0;">提示:</span> 使用 <code>SYSTEM</code> 读取本地文件</p>
        </div>
        
        <div class="xxe-box">
            <h3 style="color:#ff00ff;margin-bottom:15px;">📤 XML解析器</h3>
            <form method="POST">
                <textarea name="xml" placeholder='<?xml version="1.0"?>
<!DOCTYPE foo [
  <!ENTITY xxe SYSTEM "file:///etc/passwd">
]>
<root>&xxe;</root>'><?= htmlspecialchars($_POST['xml'] ?? '') ?></textarea>
                <button type="submit">[ 解析XML ]</button>
            </form>
            <?php if ($output): ?>
            <div class="output"><?= nl2br(htmlspecialchars($output)) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="info-box">
            <h3>🔧 XXE Payload 模板</h3>
            <pre style="background:#000;padding:15px;border-radius:5px;color:#0f0;">&lt;?xml version="1.0"?&gt;
&lt;!DOCTYPE foo [
  &lt;!ENTITY xxe SYSTEM "file:///flag.txt"&gt;
]&gt;
&lt;root&gt;&amp;xxe;&lt;/root&gt;</pre>
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
