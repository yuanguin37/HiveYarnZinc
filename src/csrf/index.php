<?php
session_start();
require_once('../flag_helper.php');
$challengeName = 'csrf';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
if (!isset($_SESSION['csrf_balance'])) {
    $_SESSION['csrf_balance'] = 1000;
    $_SESSION['csrf_logged_in'] = true;
}

// 生成CSRF令牌
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// 模拟转账
if (isset($_GET['action']) && $_GET['action'] === 'transfer') {
    $showTransferForm = true;
    $amount = $_GET['amount'] ?? 0;
    $to = $_GET['to'] ?? '';
    $token = $_GET['token'] ?? '';
    
    // 注意：这里故意没有验证CSRF令牌！
    // 漏洞：不验证token来源
    if ($amount > 0 && $amount <= $_SESSION['csrf_balance']) {
        $_SESSION['csrf_balance'] -= $amount;
        $message = "success";
    } else {
        $message = "error";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $answer = trim($_POST['answer']);
    if ($answer === $flag) {
        $message = "flag_success";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>CSRF跨站请求伪造 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff4444; }
        .header h1 { font-size: 2.5em; color: #ff4444; text-shadow: 0 0 20px #ff4444; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,68,68,0.05); border: 1px solid #ff4444; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .bank-box { background: #000; border: 2px solid #ff4444; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .balance { font-size: 1.5em; color: #0f0; text-align: center; padding: 20px; }
        .transfer-form { background: rgba(255,68,68,0.05); border: 1px solid #ff4444; border-radius: 10px; padding: 25px; margin-top: 20px; }
        .transfer-form input { width: 100%; padding: 12px; background: #111; border: 1px solid #ff4444; color: #ff4444; border-radius: 5px; font-family: 'Courier New', monospace; margin-bottom: 10px; }
        .transfer-form button { padding: 12px 40px; background: linear-gradient(135deg, #ff4444, #cc0000); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #ff4444; border-radius: 15px; padding: 40px; text-align: center; margin-top: 30px; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #ff4444; color: #ff4444; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #ff4444, #cc0000); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
        .csrf-demo { background: #000; border: 1px solid #333; padding: 15px; border-radius: 5px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🔓 CSRF跨站请求伪造</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>这是一个银行转账系统，存在CSRF漏洞。<br>
            <strong>目标:</strong> 构造一个恶意链接，让管理员执行转账操作<br>
            <span style="color:#ff0;">提示:</span> 使用 <code>?action=transfer&amount=1000&to=attacker</code></p>
        </div>
        
        <div class="bank-box">
            <h3 style="color:#ff4444;text-align:center;">🏦 网上银行</h3>
            <div class="balance">余额: $<?= $_SESSION['csrf_balance'] ?></div>
            <div class="transfer-form">
                <h4 style="color:#ff4444;margin-bottom:15px;">💸 转账</h4>
                <a href="?action=transfer&amount=100&to=admin&token=<?= $_SESSION['csrf_token'] ?>" style="color:#0f0;">[ 点击领取代金券 ]</a>
                <p style="color:#666;margin-top:10px;font-size:0.9em;">点击上方链接即可领取100元代金券！</p>
                <div class="csrf-demo">
                    <p style="color:#ff4444;">⚠️ 攻击向量:</p>
                    <code style="color:#0f0;">&lt;img src="?action=transfer&amount=1000&to=attacker" width="0" height="0"&gt;</code>
                </div>
            </div>
        </div>
        
        <?php if ($message === 'flag_success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>你成功构造了CSRF攻击！</p></div>
        <?php elseif ($message === 'success'): ?>
        <div class="result success"><h2>✅ 转账成功！</h2><p>你成功触发了转账操作。当前余额: $<?= $_SESSION['csrf_balance'] ?></p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 转账失败</h2></div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#ff4444;margin-bottom:20px;">📝 提交Flag</h3>
            <p style="color:#888;margin-bottom:15px;">通过CSRF漏洞将余额转至 attacker 账户后获取Flag</p>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
