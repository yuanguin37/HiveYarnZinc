<?php
require_once('../flag_helper.php');
$challengeName = 'blockchain_privatekey';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$output = "";

// 模拟私钥泄露
$privateKeyHint = "0x...c001 (已截断，在源代码注释中查找完整私钥)";
$fullPrivateKey = "0xdeadbeefcafebabec001cafed00dabba";

if (isset($_GET['view'])) {
    if ($_GET['view'] === 'transaction') {
        $output = "交易哈希: 0x7a8b9c0d1e2f3a4b5c6d7e8f9a0b1c2d3e4f5a6b\n"
            . "from: 0x742d35Cc6634C0532925a3b844Bc9e7595f2bD18\n"
            . "to: 0xContract\n"
            . "value: 10 ETH\n"
            . "data: 0xdeadbeef...\n\n"
            . "注意：交易输入数据中包含敏感信息！\n"
            . "输入数据的后32字节解码后即为私钥！\n"
            . "解码: " . $fullPrivateKey;
        $output .= "\n\n使用tx中的数据恢复私钥:\n私钥: " . $fullPrivateKey . "\n\n🎯 " . $flag;
    } elseif ($_GET['view'] === 'source') {
        $output = "查看源代码(Ctrl+U)找到隐藏的私钥片段！";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $answer = trim($_POST['answer']);
    $message = ($answer === $flag) ? "success" : "error";
}
?>
<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>私钥泄露 - HiveYarnZinc</title><style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:'Courier New',monospace;background:#0a0a0f;color:#0f0;min-height:100vh;padding:20px}
.container{max-width:800px;margin:0 auto}.header{text-align:center;margin-bottom:40px;padding:30px;border-bottom:2px solid #ff0}
.header h1{font-size:2.5em;color:#ff0;text-shadow:0 0 20px #ff0}.back-link{color:#0ff;text-decoration:none}
.info-box{background:rgba(255,255,0,0.05);border:1px solid #ff0;border-radius:10px;padding:25px;margin-bottom:30px}
.box{background:#000;border:2px solid #ff0;border-radius:15px;padding:30px;margin-bottom:30px}
.box a{color:#0ff;margin:5px;display:inline-block;padding:8px 16px;border:1px solid #ff0;border-radius:5px;text-decoration:none}
.output{background:#1a1a2e;padding:20px;border-radius:5px;margin-top:15px;white-space:pre-wrap;color:#0f0;word-break:break-all}
.result.success{background:rgba(0,255,0,0.1);border:2px solid #0f0;color:#0f0;padding:20px;border-radius:10px;text-align:center;margin-bottom:20px}
.result.error{background:rgba(255,0,0,0.1);border:2px solid #f00;color:#f00;padding:20px;border-radius:10px;text-align:center;margin-bottom:20px}
.submit-box{background:rgba(0,0,0,0.8);border:2px dashed #ff0;border-radius:15px;padding:40px;text-align:center}
.submit-box input{width:100%;max-width:400px;padding:15px;background:#000;border:1px solid #ff0;color:#ff0;font-size:1.2em;border-radius:5px;text-align:center}
.submit-box button{margin-top:20px;padding:15px 50px;background:linear-gradient(135deg,#ff0,#fa0);border:none;border-radius:5px;color:#000;cursor:pointer;font-size:1.1em}
</style></head><body>
<div class="container">
<div class="header"><h1>🔑 区块链私钥泄露</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
<div class="info-box"><h3>💡 题目描述</h3><p>一笔以太坊交易中意外包含了私钥信息。<br><strong>目标:</strong> 从交易数据中恢复私钥<br><span style="color:#ff0;">提示:</span> 检查交易输入数据的后32字节</p></div>
<div class="box">
<h3 style="color:#ff0;margin-bottom:15px;">🔍 区块链浏览器</h3>
<a href="?view=transaction">📜 查看交易详情</a>
<a href="?view=source">🔎 查看源代码</a>
<?php if ($output): ?><div class="output"><?= nl2br(htmlspecialchars($output)) ?></div><?php endif; ?>
</div>
<!-- 开发者提醒: 完整私钥在源代码注释中: -->
<!-- 私钥: 0xdeadbeefcafebabec001cafed00dabba -->
<?php if ($message === 'success'): ?><div class="result success"><h2>🎉 恭喜！</h2></div>
<?php elseif ($message === 'error'): ?><div class="result error"><h2>❌ 错误</h2></div><?php endif; ?>
<div class="submit-box"><h3 style="color:#ff0;margin-bottom:20px;">📝 提交Flag</h3>
<form method="POST"><input type="text" name="answer" placeholder="HiveYarnZinc{...}"><button type="submit">[ 提交 ]</button></form></div>
</div></body></html>
