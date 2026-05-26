<?php
require_once '../auth_check.php';
$encoded = "SG92ZXlhcm5aaW5je2Jhc2U2NF9lYXN5X2ZsYWd9"; // HiveYarnZinc{base64_easy_flag}
$decoded = base64_decode($encoded);

$user_answer = "";
$submitted = false;
$is_correct = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = true;
    $user_answer = $_POST['answer'] ?? '';
    $user_answer = trim($user_answer);
    $is_correct = (strtolower($user_answer) === strtolower($decoded) || 
                   $user_answer === 'HiveYarnZinc{base64_easy_flag}');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Base64编码 - HiveYarnZinc</title>
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
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff00ff; }
        .header h1 { font-size: 2.5em; color: #ff00ff; text-shadow: 0 0 20px #ff00ff; }
        .back-link { color: #00ffff; text-decoration: none; font-size: 1.1em; }
        .back-link:hover { text-shadow: 0 0 10px #00ffff; }
        
        .cipher-box {
            background: rgba(255, 0, 255, 0.05);
            border: 2px solid #ff00ff;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
        }
        .cipher-text {
            font-size: 1.8em;
            color: #ff00ff;
            text-shadow: 0 0 10px #ff00ff;
            word-break: break-all;
            letter-spacing: 2px;
            margin: 20px 0;
        }
        .difficulty { 
            display: inline-block; 
            background: #00aa00; 
            color: #fff; 
            padding: 5px 20px; 
            border-radius: 20px;
            margin-top: 15px;
        }
        
        .info-box {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #ff00ff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .info-box h3 { color: #ff00ff; margin-bottom: 15px; }
        .info-box p { color: #aaa; line-height: 1.8; }
        .info-box code { background: #000; padding: 2px 8px; border-radius: 3px; color: #ff0; }
        
        .submit-form {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #ff00ff;
            border-radius: 15px;
            padding: 40px;
        }
        .submit-form label {
            display: block;
            color: #ff00ff;
            font-size: 1.2em;
            margin-bottom: 15px;
        }
        .submit-form input {
            width: 100%;
            padding: 15px;
            background: #000;
            border: 1px solid #ff00ff;
            color: #ff00ff;
            font-size: 1.2em;
            border-radius: 5px;
            text-align: center;
            font-family: 'Courier New', monospace;
        }
        .submit-form input:focus {
            outline: none;
            border-color: #ff0;
            box-shadow: 0 0 20px rgba(255, 0, 255, 0.3);
        }
        .submit-form button {
            margin-top: 20px;
            padding: 15px 50px;
            background: linear-gradient(135deg, #ff00ff, #ff0088);
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Courier New', monospace;
        }
        .submit-form button:hover {
            background: linear-gradient(135deg, #ff0, #ff8800);
        }
        
        .result {
            margin-top: 30px;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .result.success {
            background: rgba(0, 255, 0, 0.1);
            border: 2px solid #00ff00;
            color: #00ff00;
        }
        .result.error {
            background: rgba(255, 0, 0, 0.1);
            border: 2px solid #ff0000;
            color: #ff0000;
        }
        
        .tools { margin-top: 30px; padding: 25px; background: rgba(0,0,0,0.5); border: 1px dashed #ff00ff; border-radius: 10px; }
        .tools h4 { color: #ff00ff; margin-bottom: 15px; }
        .tools p { color: #888; line-height: 1.8; }
        .tools code { color: #ff0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Base64编码</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="cipher-box">
            <h3>密文:</h3>
            <div class="cipher-text"><?= htmlspecialchars($encoded) ?></div>
            <span class="difficulty">Easy</span>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>Base64是一种常见的编码方式，用于将二进制数据转换为ASCII字符。<br>
            <strong>目标:</strong> 解码上面的Base64字符串获取Flag</p>
            <p style="margin-top:15px;"><strong>提示:</strong> Flag格式为 <code>HiveYarnZinc{...}</code></p>
        </div>
        
        <div class="submit-form">
            <form method="POST">
                <label>> 解码后的明文:</label>
                <input type="text" name="answer" placeholder="输入你的答案" value="<?= htmlspecialchars($user_answer) ?>">
                <button type="submit">[ 提交答案 ]</button>
            </form>
            
            <?php if ($submitted): ?>
            <div class="result <?= $is_correct ? 'success' : 'error' ?>">
                <?php if ($is_correct): ?>
                    🎉 恭喜！Base64解码成功！<br>
                    <strong style="font-size:1.3em;">🎯 Flag: HiveYarnZinc{base64_easy_flag}</strong>
                <?php else: ?>
                    ❌ 答案错误！Base64编码使用64个字符(A-Z, a-z, 0-9, +, /)
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="tools">
            <h4>📚 Base64工具:</h4>
            <p>
            <strong>在线解码:</strong><br>
            • CyberChef: https://gchq.github.io/CyberChef/<br>
            • Base64 decode: https://www.base64decode.org/<br><br>
            
            <strong>命令行:</strong><br>
            <code>echo "<?= $encoded ?>" | base64 -d</code><br>
            <code>echo "<?= $encoded ?>" | base64 --decode</code><br><br>
            
            <strong>Python:</strong><br>
            <code>import base64; print(base64.b64decode("<?= $encoded ?>"))</code></p>
        </div>
    </div>
</body>
</html>