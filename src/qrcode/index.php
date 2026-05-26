<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>社工题目 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ffff; }
        .header h1 { font-size: 2.5em; color: #00ffff; text-shadow: 0 0 20px #00ffff; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(0,255,255,0.05); border: 1px solid #00ffff; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .info-box h3 { color: #00ffff; margin-bottom: 15px; }
        .info-box p { color: #aaa; line-height: 1.8; }
        .info-box code { background: #000; padding: 2px 8px; border-radius: 3px; color: #ff0; }
        .content-box { background: rgba(0,0,0,0.8); border: 2px solid #00ffff; border-radius: 15px; padding: 40px; }
        .profile-card { background: rgba(0,255,255,0.05); border: 1px solid #00ffff; border-radius: 10px; padding: 30px; margin-bottom: 30px; }
        .profile-header { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
        .avatar { width: 80px; height: 80px; background: linear-gradient(135deg, #00ffff, #ff00ff); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2em; }
        .profile-name { color: #00ffff; font-size: 1.5em; }
        .profile-username { color: #666; font-size: 0.9em; }
        .profile-info { margin-top: 20px; }
        .profile-info p { margin: 10px 0; color: #aaa; }
        .profile-info strong { color: #ff0; }
        .submit-box { background: rgba(0,0,0,0.8); border: 2px dashed #00ffff; border-radius: 15px; padding: 40px; text-align: center; margin-top: 30px; }
        .submit-box input { width: 100%; max-width: 400px; padding: 15px; background: #000; border: 1px solid #00ffff; color: #00ffff; font-size: 1.2em; border-radius: 5px; text-align: center; font-family: 'Courier New', monospace; }
        .submit-box button { margin-top: 20px; padding: 15px 50px; background: linear-gradient(135deg, #00ffff, #0088ff); border: none; border-radius: 5px; color: #000; font-size: 1.1em; font-weight: bold; cursor: pointer; font-family: 'Courier New', monospace; }
        .result { margin-top: 30px; padding: 20px; border-radius: 10px; text-align: center; }
        .result.success { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; color: #00ff00; }
        .result.error { background: rgba(255,0,0,0.1); border: 2px solid #ff0000; color: #ff0000; }
        .hint-box { background: rgba(255,255,0,0.05); border: 1px solid #ff0; border-radius: 10px; padding: 20px; margin-top: 30px; }
        .hint-box h4 { color: #ff0; margin-bottom: 10px; }
        .hint-box p { color: #888; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 社工题目</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>社会工程学是利用人性弱点获取信息的艺术。<br>
            <strong>目标:</strong> 从下面的个人信息中找出管理员的密码提示<br>
            <span style="color:#ff0;">提示:</span> 密码格式为 Hacker's ID + 出生年份 + @</p>
        </div>
        
        <div class="content-box">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="avatar">👤</div>
                    <div>
                        <div class="profile-name">Alice Wang</div>
                        <div class="profile-username">@alice_cyber</div>
                    </div>
                </div>
                
                <div class="profile-info">
                    <p><strong>📧 邮箱:</strong> alice@example.com</p>
                    <p><strong>📱 电话:</strong> 138****8888</p>
                    <p><strong>🎂 生日:</strong> 2003年5月12日</p>
                    <p><strong>📍 所在地:</strong> 中国·北京</p>
                    <p><strong>💼 职业:</strong> 网络安全工程师</p>
                    <p><strong>🔗 个人网站:</strong> https://alice-cyber.github.io</p>
                    <p><strong>📝 签名:</strong> "Keep calm and hack the planet!"</p>
                </div>
            </div>
            
            <div class="hint-box">
                <h4>💬 其他用户留言:</h4>
                <p>Bob: "Alice的密码很好记，就是她的ID加上她的幸运数字！"</p>
                <p>Charlie: "她上次说密码和她的GitHub有关" </p>
                <p>David: "我记得她的ID是 alice_root，密码结尾是@"</p>
            </div>
            
            <div class="hint-box">
                <h4>🔐 密码提示:</h4>
                <p>格式: [ID][出生年份]@<br>
                例如: admin2000@</p>
            </div>
        </div>
        
<?php
require_once('../flag_helper.php');
$challengeName = 'social_engineering';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$submitted = false;
        $is_correct = false;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $submitted = true;
            $answer = trim($_POST['answer'] ?? '');
            // 正确答案: alice_root2003@ (ID是alice_root, 出生2003年)
            if ($answer === 'alice_root2003@' || $answer === $flag) {
                $is_correct = true;
            }
        }
        ?>
        
        <div class="submit-box">
            <form method="POST">
                <label style="color:#00ffff;">> 猜测管理员密码:</label>
                <input type="text" name="answer" placeholder="输入你猜测的密码">
                <button type="submit">[ 登录 ]</button>
            </form>
            
            <?php if ($submitted): ?>
            <div class="result <?= $is_correct ? 'success' : 'error' ?>">
                <?php if ($is_correct): ?>
                    🎉 恭喜！密码正确！<br><br>
                    <strong style="font-size:1.3em;">🎯 <?= $flag ?></strong>
                <?php else: ?>
                    ❌ 密码错误！<br>
                    提示: 仔细阅读密码提示和其他用户的留言
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>