<?php
require_once('../flag_helper.php');
$challengeName = 'php_deserialize';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";
$output = "";

// 模拟PHP反序列化漏洞
class UserProfile {
    public $username = 'guest';
    public $role = 'user';
    public $avatar = '/default.png';
    
    // __destruct 魔术方法 - 反序列化时自动调用
    public function __destruct() {
        if ($this->role === 'admin' && $this->username === 'admin') {
            // 模拟获取管理员权限
            global $flag;
            $output = "🎉 反序列化利用成功！\n用户: admin\n角色: admin\n\n🎯 " . $flag;
        } else {
            $output = "用户: {$this->username}, 角色: {$this->role}";
        }
    }
}

if (isset($_GET['data'])) {
    $data = $_GET['data'];
    try {
        $obj = unserialize(base64_decode($data));
    } catch (Throwable $e) {
        $output = "数据解析失败";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $answer = trim($_POST['answer']);
    if ($answer === $flag) {
        $message = "success";
    } else {
        $message = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>PHP反序列化 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff8800; }
        .header h1 { font-size: 2.5em; color: #ff8800; text-shadow: 0 0 20px #ff8800; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,136,0,0.05); border: 1px solid #ff8800; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .serial-box { background: #000; border: 2px solid #ff8800; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
        .serial-box input { width: 100%; padding: 15px; background: #111; border: 1px solid #ff8800; color: #ff8800; font-size: 0.9em; border-radius: 5px; font-family: 'Courier New', monospace; }
        .serial-box button { margin-top: 15px; padding: 12px 40px; background: linear-gradient(135deg, #ff8800, #ff4400); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
        .output { background: #1a1a2e; padding: 20px; border-radius: 5px; margin-top: 15px; white-space: pre-wrap; color: #0f0; }
        .code-box { background: #000; padding: 15px; border-radius: 5px; color: #0f0; margin: 15px 0; font-size: 0.9em; line-height: 1.6; }
        .result { padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #ff8800; border-radius: 15px; padding: 40px; text-align: center; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #ff8800; color: #ff8800; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #ff8800, #ff4400); border: none; border-radius: 5px; color: #fff; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>📦 PHP反序列化</h1><a href="../index.php" class="back-link">[ ← 返回首页 ]</a></div>
        <div class="info-box"><h3>💡 题目描述</h3>
            <p>程序使用 <code>unserialize()</code> 处理用户输入，存在反序列化漏洞。<br>
            <strong>目标:</strong> 构造反序列化Payload，使 <code>role=admin</code> 且 <code>username=admin</code><br>
            <span style="color:#ff0;">提示:</span> 数据用Base64编码，类名为 <code>UserProfile</code></p>
        </div>
        
        <div class="serial-box">
            <h3 style="color:#ff8800;margin-bottom:15px;">📤 序列化数据解析</h3>
            <div class="code-box">
// UserProfile 类结构:<br>
class UserProfile {<br>
&nbsp;&nbsp;public $username = 'guest';<br>
&nbsp;&nbsp;public $role = 'user';<br>
&nbsp;&nbsp;public $avatar = '/default.png';<br>
}<br><br>
// 序列化格式示例:<br>
O:11:"UserProfile":3:{s:8:"username";s:5:"guest";s:4:"role";s:4:"user";s:6:"avatar";s:12:"/default.png";}
            </div>
            <form method="GET">
                <input type="text" name="data" placeholder="输入Base64编码的序列化数据">
                <button type="submit">[ 反序列化 ]</button>
            </form>
            <?php if ($output): ?>
            <div class="output"><?= nl2br(htmlspecialchars($output)) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="info-box"><h3>🔧 生成Payload</h3>
            <pre style="background:#000;padding:15px;border-radius:5px;color:#0f0;">php &lt;&lt;EOF
&lt;?php
class UserProfile {
    public \$username = 'admin';
    public \$role = 'admin';
    public \$avatar = '/default.png';
}
echo base64_encode(serialize(new UserProfile()));
EOF</pre>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result success"><h2>🎉 恭喜！</h2><p>Flag正确！</p></div>
        <?php elseif ($message === 'error'): ?>
        <div class="result error"><h2>❌ 错误</h2><p>Flag不正确！</p></div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#ff8800;margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit">[ 提交 ]</button>
            </form>
        </div>
    </div>
</body>
</html>
