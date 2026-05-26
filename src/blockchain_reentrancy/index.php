<?php
require_once('../flag_helper.php');
$challengeName = 'blockchain_reentrancy';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$output = "";

// 模拟重入攻击
$contractState = [
    'balance' => 10,
    'withdrawn' => false,
    'exploit_result' => ''
];

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'contract') {
        $output = "合约状态:\n余额: " . $contractState['balance'] . " ETH\n已提款: " . ($contractState['withdrawn'] ? '是' : '否');
    } elseif ($_GET['action'] === 'attack') {
        $output = "⚔️ 重入攻击执行中...\n\n[1] withdraw(1 ETH) -> 合约转账\n[2] fallback() 触发 -> 再次调用 withdraw()\n[3] 合约未更新余额 -> 再次转账\n[4] 循环直到gas耗尽或余额为0\n\n漏洞: 合约在更新余额前就转账\n\n🎯 " . $flag;
    } elseif ($_GET['action'] === 'fix') {
        $output = "✅ 修复方案:\n1. 先更新余额(checks-effects)、再转账(interactions)\n2. 使用 ReentrancyGuard\n3. 限制提款额度";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $answer = trim($_POST['answer']);
    $message = ($answer === $flag) ? "success" : "error";
}
?>
<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>重入攻击 - HiveYarnZinc</title><style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:'Courier New',monospace;background:#0a0a0f;color:#0f0;min-height:100vh;padding:20px}
.container{max-width:800px;margin:0 auto}.header{text-align:center;margin-bottom:40px;padding:30px;border-bottom:2px solid #f44}
.header h1{font-size:2.5em;color:#f44;text-shadow:0 0 20px #f44}.back-link{color:#0ff;text-decoration:none}
.info-box{background:rgba(255,68,68,0.05);border:1px solid #f44;border-radius:10px;padding:25px;margin-bottom:30px}
.box{background:#000;border:2px solid #f44;border-radius:15px;padding:30px;margin-bottom:30px}
.box a{color:#0ff;margin:5px;display:inline-block;padding:8px 16px;border:1px solid #f44;border-radius:5px;text-decoration:none}
.output{background:#1a1a2e;padding:20px;border-radius:5px;margin-top:15px;white-space:pre-wrap;color:#0f0}
.result.success{background:rgba(0,255,0,0.1);border:2px solid #0f0;color:#0f0;padding:20px;border-radius:10px;text-align:center;margin-bottom:20px}
.result.error{background:rgba(255,0,0,0.1);border:2px solid #f00;color:#f00;padding:20px;border-radius:10px;text-align:center;margin-bottom:20px}
.submit-box{background:rgba(0,0,0,0.8);border:2px dashed #f44;border-radius:15px;padding:40px;text-align:center}
.submit-box input{width:100%;max-width:400px;padding:15px;background:#000;border:1px solid #f44;color:#f44;font-size:1.2em;border-radius:5px;text-align:center}
.submit-box button{margin-top:20px;padding:15px 50px;background:linear-gradient(135deg,#f44,#c00);border:none;border-radius:5px;color:#fff;cursor:pointer;font-size:1.1em}
</style></head><body>
<div class="container">
<div class="header"><h1>🔄 重入攻击</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
<div class="info-box"><h3>💡 题目描述</h3><p>智能合约的withdraw函数在更新余额前转账，存在重入攻击漏洞。<br><strong>目标:</strong> 利用fallback函数发起重入攻击<br><span style="color:#ff0;">提示:</span> checks-effects-interactions模式</p></div>
<div class="box">
<h3 style="color:#f44;margin-bottom:15px;">📋 合约交互</h3>
<a href="?action=contract">📊 查看合约状态</a>
<a href="?action=attack">⚔️ 执行重入攻击</a>
<a href="?action=fix">🔧 查看修复方案</a>
<?php if ($output): ?><div class="output"><?= nl2br(htmlspecialchars($output)) ?></div><?php endif; ?>
</div>
<?php if ($message === 'success'): ?><div class="result success"><h2>🎉 恭喜！</h2></div>
<?php elseif ($message === 'error'): ?><div class="result error"><h2>❌ 错误</h2></div><?php endif; ?>
<div class="submit-box"><h3 style="color:#f44;margin-bottom:20px;">📝 提交Flag</h3>
<form method="POST"><input type="text" name="answer" placeholder="HiveYarnZinc{...}"><button type="submit">[ 提交 ]</button></form></div>
</div></body></html>
