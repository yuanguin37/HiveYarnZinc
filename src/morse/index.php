<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Morse码 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ff00; }
        .header h1 { font-size: 2.5em; color: #00ff00; text-shadow: 0 0 20px #00ff00; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(0,255,0,0.05); border: 1px solid #00ff00; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .info-box h3 { color: #00ff00; margin-bottom: 15px; }
        .info-box p { color: #aaa; line-height: 1.8; }
        .morse-box { background: #000; border: 2px solid #00ff00; border-radius: 15px; padding: 40px; text-align: center; margin-bottom: 30px; }
        .morse-text { font-size: 2em; color: #00ff00; letter-spacing: 5px; word-break: break-all; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px solid #00ff00; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #00ff00; color: #00ff00; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #00ff00, #00aa00); border: none; border-radius: 5px; color: #000; font-size: 1.1em; font-weight: bold; cursor: pointer; font-family: 'Courier New', monospace; }
        .result { margin-top: 30px; padding: 20px; border-radius: 10px; text-align: center; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .reference { background: rgba(0,0,0,0.5); border: 1px dashed #00ff00; border-radius: 10px; padding: 20px; margin-top: 30px; }
        .reference h4 { color: #00ff00; margin-bottom: 15px; }
        .morse-table { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; }
        .morse-item { background: rgba(0,255,0,0.05); padding: 8px; border-radius: 5px; text-align: center; }
        .morse-item .char { color: #ff0; font-size: 1.2em; }
        .morse-item .code { color: #00ff00; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📡 Morse码</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>Morse码是一种时断时续的信号代码，通过不同的排列顺序表达不同字母。<br>
            <strong>目标:</strong> 解码下面的Morse码获取Flag</p>
        </div>
        
        <div class="morse-box">
            <h3 style="color:#00ff00;margin-bottom:20px;">📨 Morse码:</h3>
            <div class="morse-text">
                .... .. ...- . -.-- .- .-. -. --.. .. -. -.-. { -- --- .-. ... . -.. . -.-. --- -.. . }
            </div>
            <p style="color:#666;margin-top:15px;">提示: 空格分隔字母，花括号内是Flag</p>
        </div>
        
        <?php
require_once('../flag_helper.php');
$challengeName = 'morse_decode';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$submitted = false;
$is_correct = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = true;
    $answer = trim($_POST['answer'] ?? '');
    $is_correct = (strtoupper($answer) === strtoupper($flag));
}
?>
        
        <div class="submit-box">
            <form method="POST">
                <label style="color:#00ff00;">> 解码后的明文:</label>
                <input type="text" name="answer" placeholder="输入你的答案">
                <button type="submit">[ 提交 ]</button>
            </form>
            
            <?php if ($submitted): ?>
            <div class="result <?= $is_correct ? 'success' : 'error' ?>">
                <?php if ($is_correct): ?>
                    🎉 恭喜！Morse码解码成功！<br><br>
                    <strong style="font-size:1.3em;">🎯 <?= $flag ?></strong>
                <?php else: ?>
                    ❌ 答案不正确！<br>
                    提示: 参考下面的Morse码对照表
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="reference">
            <h4>📖 Morse码对照表</h4>
            <div class="morse-table">
                <div class="morse-item"><span class="char">A</span><span class="code">.-</span></div>
                <div class="morse-item"><span class="char">B</span><span class="code">-...</span></div>
                <div class="morse-item"><span class="char">C</span><span class="code">-.-.</span></div>
                <div class="morse-item"><span class="char">D</span><span class="code">-..</span></div>
                <div class="morse-item"><span class="char">E</span><span class="code">.</span></div>
                <div class="morse-item"><span class="char">F</span><span class="code">..-.</span></div>
                <div class="morse-item"><span class="char">G</span><span class="code">--.</span></div>
                <div class="morse-item"><span class="char">H</span><span class="code">....</span></div>
                <div class="morse-item"><span class="char">I</span><span class="code">..</span></div>
                <div class="morse-item"><span class="char">J</span><span class="code">.---</span></div>
                <div class="morse-item"><span class="char">K</span><span class="code">-.-</span></div>
                <div class="morse-item"><span class="char">L</span><span class="code">.-..</span></div>
                <div class="morse-item"><span class="char">M</span><span class="code">--</span></div>
                <div class="morse-item"><span class="char">N</span><span class="code">-.</span></div>
                <div class="morse-item"><span class="char">O</span><span class="code">---</span></div>
                <div class="morse-item"><span class="char">P</span><span class="code">.--.</span></div>
                <div class="morse-item"><span class="char">Q</span><span class="code">--.-</span></div>
                <div class="morse-item"><span class="char">R</span><span class="code">.-.</span></div>
                <div class="morse-item"><span class="char">S</span><span class="code">...</span></div>
                <div class="morse-item"><span class="char">T</span><span class="code">-</span></div>
                <div class="morse-item"><span class="char">U</span><span class="code">..-</span></div>
                <div class="morse-item"><span class="char">V</span><span class="code">...-</span></div>
                <div class="morse-item"><span class="char">W</span><span class="code">.--</span></div>
                <div class="morse-item"><span class="char">X</span><span class="code">-..-</span></div>
                <div class="morse-item"><span class="char">Y</span><span class="code">-.--</span></div>
                <div class="morse-item"><span class="char">Z</span><span class="code">--..</span></div>
            </div>
            <p style="color:#888;margin-top:15px;font-size:0.9em;">
                • 点(.)短信号<br>
                • 划(-)长信号<br>
                • 字母间用一个点的长度停顿<br>
                • 单词间用三个点的长度停顿
            </p>
        </div>
    </div>
</body>
</html>