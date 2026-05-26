<?php
require_once('../flag_helper.php');

$challengeName = 'file_check';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// 动态生成文件
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    // 安全清除所有输出缓冲区
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    // 生成随机字节
    $randBytes = bin2hex(random_bytes(32)); // 64字符随机数据
    
    // 将flag嵌入到文件中的特定位置
    $flagHex = bin2hex($flag);
    
    // 创建一个"伪装"的文件：看起来像png但实际是混合文件
    // PNG文件头 + 垃圾数据 + flag + 垃圾数据 + PNG尾部
    $pngHeader = "\x89PNG\r\n\x1a\n";
    $pngFooter = "IEND\xaeB`\x82";
    
    // 动态生成不同的文件
    $filler1 = bin2hex(random_bytes(rand(20, 50)));
    $filler2 = bin2hex(random_bytes(rand(10, 30)));
    
    // 组合文件内容
    $content = $pngHeader . $filler1 . $flagHex . $filler2 . $pngFooter;
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="mystery_file.png"');
    header('Content-Length: ' . strlen($content));
    echo $content;
    exit();
}

// 处理提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answer = trim($_POST['answer'] ?? '');
    if ($flagManager->verifyFlag($challengeName, $answer)) {
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
    <title>文件类型识别 - HiveYarnZinc</title>
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
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ff00; }
        .header h1 { font-size: 2.5em; color: #00ff00; text-shadow: 0 0 20px #00ff00; }
        .back-link { color: #00ffff; text-decoration: none; font-size: 1.1em; }
        
        .info-box {
            background: rgba(0, 255, 0, 0.05);
            border: 1px solid #00ff00;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .info-box h3 { color: #00ff00; margin-bottom: 15px; }
        .info-box p { color: #aaa; line-height: 1.8; }
        .info-box code { background: #000; padding: 2px 8px; border-radius: 3px; color: #ff0; }
        
        .download-section {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #00ff00;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
        }
        .download-btn {
            display: inline-block;
            padding: 20px 50px;
            background: linear-gradient(135deg, #00ff00, #00aa00);
            color: #000;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.3em;
            font-weight: bold;
            transition: all 0.3s;
            font-family: 'Courier New', monospace;
        }
        .download-btn:hover {
            background: linear-gradient(135deg, #00ffff, #00ff00);
            box-shadow: 0 0 30px rgba(0, 255, 255, 0.5);
        }
        
        .tools-box {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #00ff00;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .tools-box h3 { color: #00ff00; margin-bottom: 20px; }
        .tool-item {
            background: rgba(0, 255, 0, 0.05);
            border: 1px solid #00ff00;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .tool-item strong { color: #ff0; }
        .tool-item code { color: #0ff; background: #000; padding: 3px 6px; border-radius: 3px; display: block; margin-top: 5px; }
        
        .result-box {
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        .result-box.success {
            background: rgba(0, 255, 0, 0.1);
            border: 2px solid #00ff00;
            color: #00ff00;
        }
        .result-box.error {
            background: rgba(255, 0, 0, 0.1);
            border: 2px solid #ff0000;
            color: #ff0000;
        }
        
        .submit-box {
            background: rgba(0, 0, 0, 0.8);
            border: 2px dashed #00ff00;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
        }
        .submit-box input {
            width: 100%;
            max-width: 400px;
            padding: 15px;
            background: #000;
            border: 1px solid #00ff00;
            color: #00ff00;
            font-size: 1.2em;
            border-radius: 5px;
            text-align: center;
            font-family: 'Courier New', monospace;
        }
        .submit-box input:focus {
            outline: none;
            border-color: #ff0;
            box-shadow: 0 0 20px rgba(255, 255, 0, 0.3);
        }
        .btn {
            padding: 15px 40px;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #ff0, #ff8800);
            color: #000;
            margin-top: 20px;
        }
        .btn:hover { background: linear-gradient(135deg, #ff8800, #ff0); }
        
        .hint-box {
            background: rgba(0, 255, 0, 0.1);
            border: 2px solid #00ff00;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .hint-box h3 { color: #00ff00; margin-bottom: 15px; }
        .hint-box pre {
            background: #000;
            padding: 15px;
            border-radius: 5px;
            color: #ff0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📁 文件类型识别</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>一个神秘的文件被下载了，但它看起来有点奇怪...<br>
            文件扩展名是 <code>.png</code>，但它真的只是一个PNG图片吗？<br><br>
            <strong>目标:</strong> 分析这个文件，找出隐藏在其中的Flag<br>
            <span style="color:#ff0;">提示:</span> 使用 <code>file</code> 命令、hex编辑器或 <code>xxd</code> 查看文件真实内容</p>
        </div>
        
        <div class="download-section">
            <h3 style="color:#00ff00; margin-bottom:20px;">📥 下载神秘文件</h3>
            <p style="color:#aaa; margin-bottom:25px;">文件看起来像PNG，但可能有隐藏内容</p>
            <a href="?action=download" class="download-btn">[ 📦 下载 mystery_file.png ]</a>
        </div>
        
        <div class="tools-box">
            <h3>🛠️ 文件分析工具</h3>
            <div class="tool-item">
                <strong>file 命令</strong>
                <code>file mystery_file.png</code>
                <span>识别文件真实类型</span>
            </div>
            <div class="tool-item">
                <strong>xxd / hexdump</strong>
                <code>xxd mystery_file.png | head -50</code>
                <span>查看文件十六进制内容</span>
            </div>
            <div class="tool-item">
                <strong>strings</strong>
                <code>strings mystery_file.png | grep Hive</code>
                <span>查找嵌入的字符串</span>
            </div>
            <div class="tool-item">
                <strong>binwalk</strong>
                <code>binwalk mystery_file.png</code>
                <span>分析文件结构和隐藏数据</span>
            </div>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result-box success">
            <h2>🎉 恭喜！</h2>
            <p style="font-size:1.3em; margin:15px 0;">Flag正确！你成功识别了文件中的隐藏数据。</p>
        </div>
        <?php elseif ($message === 'error'): ?>
        <div class="result-box error">
            <h2>❌ 错误</h2>
            <p>Flag不正确，请继续分析文件！</p>
        </div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#00ff00; margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit" class="btn">提交</button>
            </form>
        </div>
    </div>
</body>
</html>
