<?php
require_once '../auth_check.php';
$ciphertext = "KlyhBduqClqf{fdhvdu_ghfruBsw}";
$hint = "偏移量: 3 (向右移动)";
$decrypted = "";

for ($i = 0; $i < strlen($ciphertext); $i++) {
    $char = $ciphertext[$i];
    if (ctype_alpha($char)) {
        $base = ctype_upper($char) ? ord('A') : ord('a');
        $decrypted .= chr(((ord($char) - $base - 3 + 26) % 26) + $base);
    } else {
        $decrypted .= $char;
    }
}

$submitted = false;
$user_answer = "";
$is_correct = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = true;
    $user_answer = $_POST['answer'] ?? '';
    $user_answer = trim($user_answer);
    $is_correct = (strtoupper($user_answer) === strtoupper($decrypted) || 
                   $user_answer === 'HiveYarnZinc{caesar_decrypt}');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>凯撒密码 - HiveYarnZinc</title>
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
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff0; }
        .header h1 { font-size: 2.5em; color: #ff0; text-shadow: 0 0 20px #ff0; }
        .back-link { color: #00ffff; text-decoration: none; font-size: 1.1em; }
        .back-link:hover { text-shadow: 0 0 10px #00ffff; }
        
        .cipher-box {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #ff0;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 30px;
            text-align: center;
        }
        .cipher-text {
            font-size: 2em;
            color: #ff0;
            text-shadow: 0 0 10px #ff0;
            word-break: break-all;
            letter-spacing: 3px;
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
            background: rgba(0, 50, 0, 0.5);
            border: 1px solid #00ff00;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .info-box h3 { color: #00ffff; margin-bottom: 15px; }
        .info-box p { line-height: 1.8; color: #aaa; }
        
        .rot-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .rot-table th, .rot-table td {
            border: 1px solid #333;
            padding: 10px;
            text-align: center;
        }
        .rot-table th { background: #1a1a2e; color: #00ffff; }
        .rot-table tr:hover { background: rgba(0, 255, 0, 0.1); }
        
        .submit-form {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #ff0;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
        }
        .submit-form label {
            display: block;
            color: #00ffff;
            font-size: 1.2em;
            margin-bottom: 15px;
        }
        .submit-form input {
            width: 100%;
            max-width: 500px;
            padding: 15px;
            background: #000;
            border: 1px solid #ff0;
            color: #ff0;
            font-size: 1.2em;
            border-radius: 5px;
            text-align: center;
            font-family: 'Courier New', monospace;
        }
        .submit-form input:focus {
            outline: none;
            border-color: #00ff00;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.3);
        }
        .submit-form button {
            margin-top: 20px;
            padding: 15px 50px;
            background: linear-gradient(135deg, #ff0, #ff8800);
            border: none;
            border-radius: 5px;
            color: #000;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Courier New', monospace;
        }
        .submit-form button:hover {
            background: linear-gradient(135deg, #00ff00, #00ffff);
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.5);
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
        
        .hint { margin-top: 30px; }
        .hint h4 { color: #ff0; margin-bottom: 10px; }
        .hint p { color: #888; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 凯撒密码</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="cipher-box">
            <h3>密文:</h3>
            <div class="cipher-text"><?= htmlspecialchars($ciphertext) ?></div>
            <span class="difficulty">Easy</span>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>凯撒密码是一种古老的替换加密技术，它将字母表中的每个字母向后移动固定数量的位置。</p>
            <p style="margin-top:10px;"><strong>提示:</strong> 这道题的偏移量是 <span style="color:#ff0">3</span></p>
        </div>
        
        <table class="rot-table">
            <tr>
                <th>ROT1</th><th>ROT2</th><th>ROT3</th><th>ROT4</th><th>ROT5</th>
            </tr>
            <tr>
                <td>A→B</td><td>A→C</td><td>A→D</td><td>A→E</td><td>A→F</td>
            </tr>
            <tr>
                <td>B→C</td><td>B→D</td><td>B→E</td><td>B→F</td><td>B→G</td>
            </tr>
        </table>
        
        <div class="submit-form">
            <form method="POST">
                <label>> 解密后的明文:</label>
                <input type="text" name="answer" placeholder="输入你的答案" value="<?= htmlspecialchars($user_answer) ?>">
                <button type="submit">[ 提交答案 ]</button>
            </form>
            
            <?php if ($submitted): ?>
            <div class="result <?= $is_correct ? 'success' : 'error' ?>">
                <?php if ($is_correct): ?>
                    🎉 恭喜！你成功解密了凯撒密码！<br>
                    <strong style="font-size:1.3em;">🎯 Flag: HiveYarnZinc{caesar_decrypt}</strong>
                <?php else: ?>
                    ❌ 答案错误！请重新尝试。<br>
                    提示：偏移量是3，向右移动
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="hint">
            <h4>📚 加解密原理:</h4>
            <p>加密: 明文字母 + 偏移量 = 密文字母<br>
            解密: 密文字母 - 偏移量 = 明文字母<br>
            例如: 'A' + 3 = 'D', 'D' - 3 = 'A'</p>
        </div>
    </div>
</body>
</html>