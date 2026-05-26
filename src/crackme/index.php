<?php
require_once '../auth_check.php';
// CrackMe Challenge - PHP版本
$secret = "CTF{cR4ckm3_phP}";
$hashed_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST['key'] ?? '';
    // 简单验证逻辑
    if (strlen($input) == strlen($secret)) {
        $check = true;
        for ($i = 0; $i < strlen($input); $i++) {
            $c = ord($input[$i]);
            if ($c < 32 || $c > 126) {
                $check = false;
                break;
            }
            // 提示: 每个字符应该是可见ASCII
        }
        if ($check) {
            if ($input === $secret) {
                $hashed_input = "🎉 正确! 你是注册码破解高手!\nFlag: " . $secret;
            } else {
                $hashed_input = "注册码不正确，再试试!";
            }
        }
    } else {
        $hashed_input = "长度不对! 提示: " . strlen($secret) . " 个字符";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>CrackMe逆向 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1a1a2e, #16213e); min-height: 100vh; color: #fff; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #e94560; font-size: 2.5em; }
        .header a { color: #e94560; text-decoration: none; }
        .info { background: rgba(233,69,96,0.15); border: 1px solid #e94560; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .crack-box { background: rgba(255,255,255,0.05); border-radius: 15px; padding: 30px; text-align: center; }
        input { width: 100%; padding: 15px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: rgba(255,255,255,0.05); color: #fff; font-size: 1.2em; text-align: center; margin-bottom: 15px; }
        button { padding: 15px 50px; background: #e94560; border: none; border-radius: 8px; color: #fff; font-size: 1.1em; cursor: pointer; }
        .result { margin-top: 20px; padding: 20px; background: rgba(76,175,80,0.2); border-radius: 8px; white-space: pre-line; }
        .error { background: rgba(244,67,54,0.2); border: 1px solid #f44336; padding: 15px; border-radius: 8px; margin-top: 15px; }
        .hint-box { background: rgba(255,255,255,0.05); border-radius: 15px; padding: 25px; margin-top: 20px; }
        code { background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🔧 CrackMe逆向</h1><p><a href="../index.php">← 返回首页</a></p></div>
        <div class="info"><h3>💡 挑战</h3><p>这是一个注册验证程序。<br><strong>找出正确的注册码获取Flag!</strong><br><em>提示: 仔细分析验证逻辑</em></p></div>
        <div class="crack-box">
            <h2 style="color:#e94560;margin-bottom:20px;">🔐 注册验证</h2>
            <form method="POST">
                <input type="text" name="key" placeholder="输入注册码..." autocomplete="off">
                <button type="submit">验证</button>
            </form>
            <?php if ($hashed_input): ?>
                <?php if (strpos($hashed_input, '🎉') !== false): ?>
                <div class="result"><?= $hashed_input ?></div>
                <?php else: ?>
                <div class="error"><?= $hashed_input ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="hint-box">
            <h3 style="color:#e94560;">💡 逆向分析提示</h3>
            <p>• 查看页面源代码获取更多信息<br>
            • 尝试理解验证逻辑<br>
            • Flag格式: <code>CTF{...}</code></p>
            <!-- 提示: 注册码是 CTF{cR4ckm3_phP} -->
        </div>
    </div>
</body>
</html>
