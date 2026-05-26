<?php
require_once('../flag_helper.php');
$challengeName = 'blockchain_solidity';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$output = "";

// 模拟Solidity智能合约漏洞
$contractCode = "// SPDX-License-Identifier: MIT\npragma solidity ^0.8.0;\n\ncontract Vault {\n    // 漏洞：private变量可以被读取\n    bytes32 private password;\n    uint256 public balance;\n    \n    constructor() {\n        password = 0x" . bin2hex($flag) . ";\n        balance = 100 ether;\n    }\n    \n    function withdraw(bytes32 _password) public {\n        require(_password == password, \"Wrong password!\");\n        payable(msg.sender).transfer(balance);\n    }\n}";

$analysis = "";
if (isset($_GET['view'])) {
    $view = $_GET['view'];
    if ($view === 'storage') {
        $analysis = "存储布局分析:\nslot 0: password (bytes32)\n  -> 存储内容: 0x" . bin2hex($flag) . "\nslot 1: balance (uint256)\n  -> 存储内容: 0x" . dechex(100e18) . "\n\n使用 web3.eth.getStorageAt(contractAddress, 0) 读取password!";
    } elseif ($view === 'exploit') {
        $analysis = "利用代码:\n\n// 读取合约存储\nconst password = await web3.eth.getStorageAt(contractAddress, 0);\n\n// 调用withdraw\nawait contract.methods.withdraw(password).send({from: attacker});\n\n\n🎯 " . $flag;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $answer = trim($_POST['answer']);
    $message = ($answer === $flag) ? "success" : "error";
}
?>
<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>Solidity合约漏洞 - HiveYarnZinc</title><style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:'Courier New',monospace;background:#0a0a0f;color:#0f0;min-height:100vh;padding:20px}
.container{max-width:900px;margin:0 auto}.header{text-align:center;margin-bottom:40px;padding:30px;border-bottom:2px solid #0f0}
.header h1{font-size:2.5em;color:#0f0;text-shadow:0 0 20px #0f0}.back-link{color:#0ff;text-decoration:none}
.info-box{background:rgba(0,255,0,0.05);border:1px solid #0f0;border-radius:10px;padding:25px;margin-bottom:30px}
.box{background:#000;border:2px solid #0f0;border-radius:15px;padding:30px;margin-bottom:30px}
.box a{color:#0ff;margin:5px;display:inline-block;padding:8px 16px;border:1px solid #0f0;border-radius:5px;text-decoration:none}
.output{background:#1a1a2e;padding:20px;border-radius:5px;margin-top:15px;white-space:pre-wrap;color:#0f0;overflow-x:auto}
code{background:#000;padding:2px 6px;border-radius:3px;color:#ff0}
.result.success{background:rgba(0,255,0,0.1);border:2px solid #0f0;color:#0f0;padding:20px;border-radius:10px;text-align:center;margin-bottom:20px}
.result.error{background:rgba(255,0,0,0.1);border:2px solid #f00;color:#f00;padding:20px;border-radius:10px;text-align:center;margin-bottom:20px}
.submit-box{background:rgba(0,0,0,0.8);border:2px dashed #0f0;border-radius:15px;padding:40px;text-align:center}
.submit-box input{width:100%;max-width:400px;padding:15px;background:#000;border:1px solid #0f0;color:#0f0;font-size:1.2em;border-radius:5px;text-align:center}
.submit-box button{margin-top:20px;padding:15px 50px;background:linear-gradient(135deg,#0f0,#0a0);border:none;border-radius:5px;color:#000;cursor:pointer;font-size:1.1em}
</style></head><body>
<div class="container">
<div class="header"><h1>⛓️ Solidity合约漏洞</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
<div class="info-box"><h3>💡 题目描述</h3><p>智能合约将密码存储在<code>private</code>变量中，但区块链上所有数据都是公开的。<br><strong>目标:</strong> 读取链上存储数据，获取密码<br><span style="color:#ff0;">提示:</span> 使用<code>web3.eth.getStorageAt()</code>读取存储槽</p></div>
<div class="box">
<h3 style="color:#0f0;margin-bottom:15px;">📜 合约源码</h3>
<pre style="background:#000;padding:15px;border-radius:5px;color:#0f0;overflow-x:auto;"><?= htmlspecialchars($contractCode) ?></pre>
<a href="?view=storage">🔍 存储布局分析</a>
<a href="?view=exploit">⚡ 利用代码</a>
<?php if ($analysis): ?><div class="output"><?= nl2br(htmlspecialchars($analysis)) ?></div><?php endif; ?>
</div>
<div class="info-box"><h3>🔧 工具</h3><p>• <code>web3.eth.getStorageAt(addr, slot)</code> - 读取存储<br>• <code>cast storage contractAddress slot</code> - Foundry工具</p></div>
<?php if ($message === 'success'): ?><div class="result success"><h2>🎉 恭喜！</h2></div>
<?php elseif ($message === 'error'): ?><div class="result error"><h2>❌ 错误</h2></div><?php endif; ?>
<div class="submit-box"><h3 style="color:#0f0;margin-bottom:20px;">📝 提交Flag</h3>
<form method="POST"><input type="text" name="answer" placeholder="HiveYarnZinc{...}"><button type="submit">[ 提交 ]</button></form></div>
</div></body></html>
