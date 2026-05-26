<?php
require_once('../flag_helper.php');
$challengeName = 'mobile_root';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// 模拟Root检测绕过
$rootChecks = [
    '/system/app/Superuser.apk' => '存在',
    '/sbin/su' => '存在',
    '/system/xbin/su' => '存在',
    'which su' => '/system/xbin/su',
    'build_tags' => 'test-keys',
];

if (isset($_GET['check'])) {
    $check = $_GET['check'];
    if ($check === 'bypass') {
        $output = "✅ Root检测绕过成功！\n\n🎯 " . $flag;
    } elseif (isset($rootChecks[$check])) {
        $output = "$check 检测结果: " . $rootChecks[$check] . " (检测到Root!)";
    } else {
        $output = "$check 检测结果: 未发现异常";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $answer = trim($_POST['answer']);
    $message = ($answer === $flag) ? "success" : "error";
}
?>
<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>Root检测绕过 - HiveYarnZinc</title><style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:'Courier New',monospace;background:#0a0a0f;color:#0f0;min-height:100vh;padding:20px}
.container{max-width:800px;margin:0 auto}.header{text-align:center;margin-bottom:40px;padding:30px;border-bottom:2px solid #f0f}
.header h1{font-size:2.5em;color:#f0f;text-shadow:0 0 20px #f0f}.back-link{color:#0ff;text-decoration:none}
.info-box{background:rgba(255,0,255,0.05);border:1px solid #f0f;border-radius:10px;padding:25px;margin-bottom:30px}
.box{background:#000;border:2px solid #f0f;border-radius:15px;padding:30px;margin-bottom:30px}
.box a{color:#0ff;display:inline-block;margin:5px;padding:8px 16px;border:1px solid #f0f;border-radius:5px;text-decoration:none}
.output{background:#1a1a2e;padding:20px;border-radius:5px;margin-top:15px;white-space:pre-wrap;color:#0f0}
.result{padding:20px;border-radius:10px;text-align:center;margin-bottom:20px}
.result.success{background:rgba(0,255,0,0.1);border:2px solid #0f0;color:#0f0}.result.error{background:rgba(255,0,0,0.1);border:2px solid #f00;color:#f00}
.submit-box{background:rgba(0,0,0,0.8);border:2px dashed #f0f;border-radius:15px;padding:40px;text-align:center}
.submit-box input{width:100%;max-width:400px;padding:15px;background:#000;border:1px solid #f0f;color:#f0f;font-size:1.2em;border-radius:5px;text-align:center}
.submit-box button{margin-top:20px;padding:15px 50px;background:linear-gradient(135deg,#f0f,#c0c);border:none;border-radius:5px;color:#fff;cursor:pointer;font-size:1.1em}
</style></head><body>
<div class="container">
<div class="header"><h1>🔓 Root检测绕过</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
<div class="info-box"><h3>💡 题目描述</h3><p>Android应用检测到设备已Root，拒绝运行。<br><strong>目标:</strong> 绕过Root检测机制<br><span style="color:#ff0;">提示:</span> 使用Frida或Xposed框架HOOK检测函数</p></div>
<div class="box">
<h3 style="color:#f0f;margin-bottom:15px;">🔍 Root检测项</h3>
<a href="?check=/system/app/Superuser.apk">Superuser.apk</a>
<a href="?check=/sbin/su">/sbin/su</a>
<a href="?check=/system/xbin/su">/system/xbin/su</a>
<a href="?check=build_tags">build_tags</a>
<a href="?check=bypass">🚀 执行绕过</a>
<?php if ($output): ?><div class="output"><?= nl2br(htmlspecialchars($output)) ?></div><?php endif; ?>
</div>
<div class="info-box"><h3>🛠️ 绕过工具</h3><p>• <code>Frida</code>: <code>frida -U -f com.example.app -l hook.js</code><br>• Hook代码: 修改buildTags返回值</p></div>
<?php if ($message === 'success'): ?><div class="result success"><h2>🎉 恭喜！</h2></div>
<?php elseif ($message === 'error'): ?><div class="result error"><h2>❌ 错误</h2></div><?php endif; ?>
<div class="submit-box"><h3 style="color:#f0f;margin-bottom:20px;">📝 提交Flag</h3>
<form method="POST"><input type="text" name="answer" placeholder="HiveYarnZinc{...}"><button type="submit">[ 提交 ]</button></form></div>
</div></body></html>
