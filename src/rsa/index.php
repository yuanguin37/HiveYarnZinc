<?php
require_once('../flag_helper.php');
$challengeName = 'rsa';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
// RSA Challenge - 小公钥指数攻击
// 使用原生PHP实现（无需GMP/BCMath扩展）
$p = 61;
$q = 53;
$n = $p * $q; // 3233
$e = 17; // 公钥指数 (小值，易受攻击)
$phi = ($p - 1) * ($q - 1);

// 使用扩展欧几里得算法求模逆
function modInverse($a, $m) {
    $a = $a % $m;
    for ($x = 1; $x < $m; $x++) {
        if (($a * $x) % $m == 1) {
            return $x;
        }
    }
    return 1;
}
$d = modInverse($e, $phi);

// 快速模幂运算
function modPow($base, $exp, $mod) {
    $result = 1;
    $base = $base % $mod;
    while ($exp > 0) {
        if ($exp % 2 == 1) {
            $result = ($result * $base) % $mod;
        }
        $exp = (int)($exp / 2);
        $base = ($base * $base) % $mod;
    }
    return $result;
}

// 将flag转换为数字并加密
$flag = "HiveYarnZinc{rsa_w1th_sm4ll_3xp}";
$flagHex = bin2hex($flag);
$flagNum = hexdec($flagHex);
$cipher = modPow($flagNum, $e, $n);

$decrypted = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d_input = $_POST['d'] ?? '';
    $c_input = $_POST['cipher'] ?? $cipher;
    if (is_numeric($d_input)) {
        $d_int = (int)$d_input;
        $m = modPow((int)$c_input, $d_int, $n);
        $hex = dechex($m);
        $decrypted = @hex2bin($hex);
        if ($decrypted === false || strlen($decrypted) < 4) {
            $decrypted = "使用私钥d=$d_int解密后的数字: $m";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>RSA加密 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1a1a2e, #16213e); min-height: 100vh; color: #fff; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #e94560; font-size: 2.5em; }
        .header a { color: #e94560; text-decoration: none; }
        .info { background: rgba(233,69,96,0.15); border: 1px solid #e94560; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .box { background: rgba(255,255,255,0.05); border-radius: 15px; padding: 25px; margin-bottom: 20px; }
        .box h3 { color: #e94560; margin-bottom: 15px; }
        input { width: 100%; padding: 12px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: rgba(255,255,255,0.05); color: #fff; font-size: 1em; margin-bottom: 10px; }
        button { padding: 12px 40px; background: #e94560; border: none; border-radius: 8px; color: #fff; cursor: pointer; }
        .result { margin-top: 15px; padding: 15px; background: rgba(76,175,80,0.2); border-radius: 8px; }
        code { background: rgba(0,0,0,0.3); padding: 2px 6px; border-radius: 4px; font-family: monospace; }
        .key-pair { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .key-box { background: rgba(0,0,0,0.3); padding: 15px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🔐 RSA加密</h1><p><a href="../index.php">← 返回首页</a></p></div>
        <div class="info"><h3>💡 挑战</h3><p>RSA是非对称加密算法。这里提供了一组小素数p=61和q=53。<br><strong>目标:</strong> 分解n获取私钥d，解密Flag!</p></div>
        
        <div class="box">
            <h3>🔑 公钥</h3>
            <div class="key-pair">
                <div class="key-box"><strong>n (模数):</strong><br><code style="word-break:break-all;"><?= $n ?></code></div>
                <div class="key-box"><strong>e (公钥指数):</strong><br><code><?= $e ?></code></div>
            </div>
        </div>
        
        <div class="box">
            <h3>🔒 密文</h3>
            <code style="font-size:1.2em;word-break:break-all;"><?= $cipher ?></code>
            <p style="margin-top:10px;color:#a0a0a0;"><strong>提示:</strong> n = <?= $n ?> = p × q，你需要分解n并计算私钥d</p>
        </div>
        
        <div class="box">
            <h3>🔓 解密工具</h3>
            <form method="POST">
                <label>密文:</label>
                <input type="text" name="cipher" value="<?= $cipher ?>">
                <label>私钥 d:</label>
                <input type="text" name="d" placeholder="输入私钥d">
                <button type="submit">解密</button>
            </form>
            <?php if ($decrypted): ?>
            <div class="result"><strong>解密结果:</strong><br><?= htmlspecialchars($decrypted) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="info"><h4>📚 RSA原理:</h4><p>• n = p × q (p和q是大素数)<br>• φ(n) = (p-1)(q-1)<br>• d × e ≡ 1 (mod φ(n))<br>• 解密: m = c^d mod n</p></div>
    </div>
</body>
</html>
