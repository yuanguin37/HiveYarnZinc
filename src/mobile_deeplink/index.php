<?php
require_once('../flag_helper.php');
$challengeName = 'mobile_deeplink';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

$deeplinks = [
    'ctfapp://open?url=https://hacker.com' => '打开恶意URL',
    'ctfapp://login?token=eyJ1c2VyIjoiYWRtaW4ifQ' => '登录令牌泄露',
    'ctfapp://pay?amount=1000&to=attacker' => '模拟支付劫持: ' . $flag,
];

if (isset($_GET['deeplink'])) {
    $link = $_GET['deeplink'];
    if (isset($deeplinks[$link])) {
        $output = "🔗 深度链接被触发: $link\n结果: " . $deeplinks[$link];
    } else {
        $output = "🔗 深度链接: $link (模拟执行)";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $answer = trim($_POST['answer']);
    $message = ($answer === $flag) ? "success" : "error";
}
?>
<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>深度链接劫持 - HiveYarnZinc</title><style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:'Courier New',monospace;background:#0a0a0f;color:#0f0;min-height:100vh;padding:20px}
.container{max-width:800px;margin:0 auto}.header{text-align:center;margin-bottom:40px;padding:30px;border-bottom:2px solid #ff0}
.header h1{font-size:2.5em;color:#ff0;text-shadow:0 0 20px #ff0}.back-link{color:#0ff;text-decoration:none}
.info-box{background:rgba(255,255,0,0.05);border:1px solid #ff0;border-radius:10px;padding:25px;margin-bottom:30px}
.box{background:#000;border:2px solid #ff0;border-radius:15px;padding:30px;margin-bottom:30px}
.box a{color:#0ff;display:block;margin:5px 0;padding:10px;border:1px solid #333;border-radius:5px;text-decoration:none;word-break:break-all}
.box a:hover{border-color:#ff0;background:rgba(255,255,0,0.1)}
.output{background:#1a1a2e;padding:20px;border-radius:5px;margin-top:15px;white-space:pre-wrap;color:#0f0}
.result.success{background:rgba(0,255,0,0.1);border:2px solid #0f0;color:#0f0;padding:20px;border-radius:10px;text-align:center;margin-bottom:20px}
.result.error{background:rgba(255,0,0,0.1);border:2px solid #f00;color:#f00;padding:20px;border-radius:10px;text-align:center;margin-bottom:20px}
.submit-box{background:rgba(0,0,0,0.8);border:2px dashed #ff0;border-radius:15px;padding:40px;text-align:center}
.submit-box input{width:100%;max-width:400px;padding:15px;background:#000;border:1px solid #ff0;color:#ff0;font-size:1.2em;border-radius:5px;text-align:center}
.submit-box button{margin-top:20px;padding:15px 50px;background:linear-gradient(135deg,#ff0,#fa0);border:none;border-radius:5px;color:#000;cursor:pointer;font-size:1.1em}
</style></head><body>
<div class="container">
<div class="header"><h1>🔗 深度链接劫持</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
<div class="info-box"><h3>💡 题目描述</h3><p>Android应用使用深度链接(Deep Link)处理外部URL，存在劫持漏洞。<br><strong>目标:</strong> 利用深度链接劫持获取Flag<br><span style="color:#ff0;">提示:</span> 尝试 <code>ctfapp://pay</code> 链接</p></div>
<div class="box">
<h3 style="color:#ff0;margin-bottom:15px;">🔗 测试深度链接</h3>
<a href="?deeplink=ctfapp://open?url=https://hacker.com">ctfapp://open?url=https://hacker.com</a>
<a href="?deeplink=ctfapp://login?token=eyJ1c2VyIjoiYWRtaW4ifQ">ctfapp://login?token=eyJ1c2VyIjoiYWRtaW4ifQ</a>
<a href="?deeplink=ctfapp://pay?amount=1000&to=attacker">ctfapp://pay?amount=1000&to=attacker</a>
<?php if ($output): ?><div class="output"><?= nl2br(htmlspecialchars($output)) ?></div><?php endif; ?>
</div>
<?php if ($message === 'success'): ?><div class="result success"><h2>🎉 恭喜！</h2></div>
<?php elseif ($message === 'error'): ?><div class="result error"><h2>❌ 错误</h2></div><?php endif; ?>
<div class="submit-box"><h3 style="color:#ff0;margin-bottom:20px;">📝 提交Flag</h3>
<form method="POST"><input type="text" name="answer" placeholder="HiveYarnZinc{...}"><button type="submit">[ 提交 ]</button></form></div>
</div></body></html>
