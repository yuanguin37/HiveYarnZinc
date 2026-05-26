<?php
require_once '../auth_check.php';
$message = "";
$flag = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $target_dir = "/tmp/uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($_FILES['file']['name']);
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // 黑名单绕过
    $blacklist = ['php', 'php3', 'php4', 'php5', 'phtml'];
    
    if (in_array($fileType, $blacklist)) {
        $message = "❌ 文件类型不允许上传！";
    } else {
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
            $message = "✅ 文件上传成功！路径: " . $target_file;
            if ($fileType === 'jpg' || $fileType === 'png' || $fileType === 'gif') {
                $flag = "🎯 Flag: HiveYarnZinc{upload_pwn_shell}";
            }
        } else {
            $message = "❌ 上传失败！";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>文件上传 - HiveYarnZinc</title>
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
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ffff; }
        .header h1 { font-size: 2.5em; color: #00ffff; text-shadow: 0 0 20px #00ffff; }
        .back-link { color: #00ffff; text-decoration: none; font-size: 1.1em; }
        .back-link:hover { text-shadow: 0 0 10px #00ffff; }
        
        .info-box {
            background: rgba(0, 255, 255, 0.05);
            border: 1px solid #00ffff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .info-box h3 { color: #00ffff; margin-bottom: 15px; }
        .info-box p { color: #aaa; line-height: 1.8; }
        
        .upload-box {
            background: rgba(0, 0, 0, 0.8);
            border: 2px dashed #00ffff;
            border-radius: 15px;
            padding: 50px;
            text-align: center;
            margin-bottom: 30px;
        }
        .upload-box input[type="file"] {
            color: #00ffff;
            margin: 20px 0;
        }
        .upload-box button {
            margin-top: 20px;
            padding: 15px 50px;
            background: linear-gradient(135deg, #00ffff, #0088ff);
            border: none;
            border-radius: 5px;
            color: #000;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Courier New', monospace;
        }
        .upload-box button:hover {
            background: linear-gradient(135deg, #00ff00, #00ffff);
            box-shadow: 0 0 30px rgba(0, 255, 255, 0.5);
        }
        
        .message {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            font-size: 1.1em;
        }
        .message.success {
            background: rgba(0, 255, 0, 0.1);
            border: 2px solid #00ff00;
            color: #00ff00;
        }
        .message.error {
            background: rgba(255, 0, 0, 0.1);
            border: 2px solid #ff0000;
            color: #ff0000;
        }
        
        .hint { 
            margin-top: 30px; 
            padding: 25px; 
            background: rgba(0, 0, 0, 0.5);
            border: 1px dashed #00ffff;
            border-radius: 10px;
        }
        .hint h4 { color: #ff0; margin-bottom: 15px; }
        .hint p { color: #888; line-height: 1.8; }
        .hint code { color: #ff0; background: #000; padding: 2px 8px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📤 文件上传</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>这是一个文件上传系统，限制了某些危险文件类型。<br>
            <strong>目标:</strong> 绕过限制上传WebShell获取Flag<br>
            <span style="color:#ff0;">提示:</span> 黑名单不等于安全！</p>
        </div>
        
        <?php if ($message): ?>
        <div class="message <?= strpos($message, '成功') !== false ? 'success' : 'error' ?>">
            <?= $message ?>
            <?php if ($flag): ?>
            <br><br><strong style="font-size:1.3em;"><?= $flag ?></strong>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="upload-box">
            <form method="POST" enctype="multipart/form-data">
                <p>> 选择要上传的文件:</p>
                <input type="file" name="file" required>
                <br>
                <button type="submit">[ 上传文件 ]</button>
            </form>
        </div>
        
        <div class="hint">
            <h4>📚 文件上传绕过技巧:</h4>
            <p>
            <strong>方法1 - 大小写绕过:</strong><br>
            <code>shell.PHP</code> 或 <code>shell.PhP</code><br><br>
            
            <strong>方法2 - 双后缀绕过:</strong><br>
            <code>shell.php.jpg</code><br><br>
            
            <strong>方法3 - 特殊后缀:</strong><br>
            <code>shell.php3</code> <code>shell.php5</code> <code>shell.phtml</code><br><br>
            
            <strong>方法4 - 文件头伪装:</strong><br>
            在PHP文件开头添加图片文件头绕过检测</p>
        </div>
    </div>
</body>
</html>