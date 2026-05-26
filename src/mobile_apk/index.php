<?php
require_once('../flag_helper.php');
$challengeName = 'mobile_apk';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// 模拟APK反编译挑战
$apkStructure = [
    'AndroidManifest.xml' => "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<manifest package=\"com.example.ctfapp\">\n    <application android:label=\"CTF Challenge\">\n        <activity android:name=\".MainActivity\">\n            <intent-filter>\n                <action android:name=\"android.intent.action.MAIN\" />\n                <category android:name=\"android.intent.category.LAUNCHER\" />\n            </intent-filter>\n        </activity>\n    </application>\n</manifest>",
    'classes.dex' => "DEX file (模拟): 使用 jadx 反编译查看源码",
    'res/values/strings.xml' => "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<resources>\n    <string name=\"app_name\">CTF Challenge</string>\n    <string name=\"secret\">" . $flag . "</string>\n    <string name=\"api_key\">H4ck3r_K3y_2024</string>\n</resources>",
    'res/values/keys.xml' => "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<resources>\n    <string name=\"encryption_key\">SuperSecretKey123</string>\n</resources>"
];

$output = "";
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    if (isset($apkStructure[$file])) {
        $output = htmlspecialchars($apkStructure[$file]);
    } elseif ($file === 'decompile') {
        $output = "使用 jadx-gui app.apk 反编译APK\n查看 res/values/strings.xml 中的secret字符串";
    } else {
        $output = "文件 '$file' 未找到";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $answer = trim($_POST['answer']);
    $message = ($answer === $flag) ? "success" : "error";
}
?>
<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>APK反编译 - HiveYarnZinc</title><style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:'Courier New',monospace;background:#0a0a0f;color:#0f0;min-height:100vh;padding:20px}
.container{max-width:900px;margin:0 auto}.header{text-align:center;margin-bottom:40px;padding:30px;border-bottom:2px solid #0ff}
.header h1{font-size:2.5em;color:#0ff;text-shadow:0 0 20px #0ff}.back-link{color:#0ff;text-decoration:none}
.info-box{background:rgba(0,255,255,0.05);border:1px solid #0ff;border-radius:10px;padding:25px;margin-bottom:30px}
.info-box h3{color:#0ff;margin-bottom:15px}.info-box p{color:#aaa;line-height:1.8}
.apk-box{background:#000;border:2px solid #0ff;border-radius:15px;padding:30px;margin-bottom:30px}
.apk-box a{color:#0ff;display:inline-block;margin:5px;padding:8px 16px;border:1px solid #0ff;border-radius:5px;text-decoration:none}
.apk-box a:hover{background:rgba(0,255,255,0.2)}.output{background:#1a1a2e;padding:20px;border-radius:5px;margin-top:15px;white-space:pre-wrap;color:#0f0}
.result{padding:20px;border-radius:10px;text-align:center;margin-bottom:20px}
.result.success{background:rgba(0,255,0,0.1);border:2px solid #0f0;color:#0f0}
.result.error{background:rgba(255,0,0,0.1);border:2px solid #f00;color:#f00}
.submit-box{background:rgba(0,0,0,0.8);border:2px dashed #0ff;border-radius:15px;padding:40px;text-align:center}
.submit-box input{width:100%;max-width:400px;padding:15px;background:#000;border:1px solid #0ff;color:#0ff;font-size:1.2em;border-radius:5px;text-align:center}
.submit-box button{margin-top:20px;padding:15px 50px;background:linear-gradient(135deg,#0ff,#08f);border:none;border-radius:5px;color:#000;cursor:pointer;font-size:1.1em}
</style></head><body>
<div class="container">
<div class="header"><h1>📱 APK反编译</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
<div class="info-box"><h3>💡 题目描述</h3><p>Android APK文件中隐藏了敏感信息。<br><strong>目标:</strong> 分析APK结构，找到隐藏的Flag<br><span style="color:#ff0;">提示:</span> 检查 <code>res/values/strings.xml</code></p></div>
<div class="apk-box">
<h3 style="color:#0ff;margin-bottom:15px;">📂 APK文件结构</h3>
<p style="color:#888;margin-bottom:15px;">模拟APK结构 - 点击查看各文件内容:</p>
<a href="?file=AndroidManifest.xml">AndroidManifest.xml</a>
<a href="?file=classes.dex">classes.dex</a>
<a href="?file=res/values/strings.xml">strings.xml</a>
<a href="?file=res/values/keys.xml">keys.xml</a>
<a href="?file=decompile">反编译教程</a>
<?php if ($output): ?><div class="output"><?= nl2br($output) ?></div><?php endif; ?>
</div>
<div class="info-box"><h3>🛠️ 工具</h3><p>• <code>jadx-gui app.apk</code> - APK反编译<br>• <code>apktool d app.apk</code> - 资源提取<br>• <code>dex2jar</code> - DEX转JAR</p></div>
<?php if ($message === 'success'): ?><div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！</p></div>
<?php elseif ($message === 'error'): ?><div class="result error"><h2>❌ 错误</h2></div><?php endif; ?>
<div class="submit-box"><h3 style="color:#0ff;margin-bottom:20px;">📝 提交Flag</h3>
<form method="POST"><input type="text" name="answer" placeholder="HiveYarnZinc{...}"><button type="submit">[ 提交 ]</button></form></div>
</div></body></html>
