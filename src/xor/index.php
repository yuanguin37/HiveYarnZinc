<?php
$flag = "CTF{x0r_1s_r3v3rs1bl3}";
$key = 0x42;
$cipher = '';
for ($i = 0; $i < strlen($flag); $i++) {
    $cipher .= sprintf("%02x", ord($flag[$i]) ^ $key);
}
$decrypted = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key_input = hexdec($_POST['key'] ?? '0');
    $cipher_input = $_POST['cipher'] ?? '';
    $result = '';
    for ($i = 0; $i < strlen($cipher_input); $i += 2) {
        $byte = hexdec(substr($cipher_input, $i, 2));
        $result .= chr($byte ^ $key_input);
    }
    $decrypted = $result;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>XOR异或 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1a1a2e, #16213e); min-height: 100vh; color: #fff; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #e94560; font-size: 2.5em; }
        .header a { color: #e94560; text-decoration: none; }
        .info { background: rgba(233,69,96,0.15); border: 1px solid #e94560; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .box { background: rgba(255,255,255,0.05); border-radius: 15px; padding: 25px; margin-bottom: 20px; }
        .box h3 { color: #e94560; margin-bottom: 15px; }
        input { width: 100%; padding: 12px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: rgba(255,255,255,0.05); color: #fff; font-family: monospace; margin-bottom: 10px; }
        button { padding: 12px 40px; background: #e94560; border: none; border-radius: 8px; color: #fff; cursor: pointer; }
        .result { margin-top: 15px; padding: 15px; background: rgba(76,175,80,0.2); border-radius: 8px; }
        code { background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🔐 XOR异或</h1><p><a href="../index.php">← 返回首页</a></p></div>
        <div class="info"><h3>💡 挑战</h3><p>XOR运算具有可逆性: A ⊕ B = C, 则 C ⊕ B = A<br><strong>Flag使用单字节XOR加密，找出密钥解密!</strong></p></div>
        <div class="box">
            <h3>密文 (十六进制)</h3>
            <code style="font-size:1.3em;word-break:break-all;"><?= $cipher ?></code>
        </div>
        <div class="box">
            <h3>🔓 解密工具</h3>
            <form method="POST">
                <label>密文 (十六进制):</label>
                <input type="text" name="cipher" value="<?= $cipher ?>">
                <label>密钥 (十进制或0x十六进制):</label>
                <input type="text" name="key" placeholder="例如: 66 或 0x42">
                <button type="submit">解密</button>
            </form>
            <?php if ($decrypted): ?>
            <div class="result"><strong>解密结果:</strong><br><?= htmlspecialchars($decrypted) ?></div>
            <?php endif; ?>
        </div>
        <div class="info"><h4>📚 XOR特性:</h4><p>• 可逆: A ⊕ B ⊕ B = A<br>• 单字节XOR可用暴力破解<br>• 如果知道明文某部分，可反推密钥</p></div>
    </div>
</body>
</html>
