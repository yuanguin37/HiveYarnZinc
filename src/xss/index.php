<?php
require_once '../auth_check.php';
$comments = $_SESSION['comments'] ?? [];
$flag = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['comment'])) {
        $comment = $_POST['comment'];
        $comments[] = $comment;
        $_SESSION['comments'] = $comments;
    }
    
    if (isset($_POST['steal']) && $_POST['steal'] === 'cookie') {
        $flag = "🎯 Flag: HiveYarnZinc{xss_stored_cookie}";
    }
}

if (isset($_GET['view']) && $_GET['view'] === 'admin') {
    $flag = "🎯 Flag: HiveYarnZinc{xss_stored_cookie}";
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>XSS跨站脚本 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: #0a0a0f;
            color: #00ff00;
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff8800; }
        .header h1 { font-size: 2.5em; color: #ff8800; text-shadow: 0 0 20px #ff8800; }
        .back-link { color: #00ffff; text-decoration: none; font-size: 1.1em; }
        .back-link:hover { text-shadow: 0 0 10px #00ffff; }
        
        .info-box {
            background: rgba(255, 136, 0, 0.05);
            border: 1px solid #ff8800;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .info-box h3 { color: #ff8800; margin-bottom: 15px; }
        .info-box p { color: #aaa; line-height: 1.8; }
        .info-box code { background: #000; padding: 2px 8px; border-radius: 3px; color: #ff0; }
        
        .comment-box {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #ff8800;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .comment-form label {
            display: block;
            color: #ff8800;
            font-size: 1.1em;
            margin-bottom: 10px;
        }
        .comment-form textarea {
            width: 100%;
            height: 100px;
            padding: 15px;
            background: #000;
            border: 1px solid #ff8800;
            color: #ff8800;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            resize: vertical;
        }
        .comment-form textarea:focus {
            outline: none;
            border-color: #ff0;
            box-shadow: 0 0 20px rgba(255, 136, 0, 0.3);
        }
        .comment-form button {
            margin-top: 15px;
            padding: 12px 40px;
            background: linear-gradient(135deg, #ff8800, #ff4400);
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Courier New', monospace;
        }
        .comment-form button:hover {
            background: linear-gradient(135deg, #ff0, #ff8800);
            color: #000;
        }
        
        .comments-list {
            margin-top: 30px;
            border-top: 1px solid #333;
            padding-top: 20px;
        }
        .comment-item {
            background: rgba(255, 136, 0, 0.05);
            border: 1px solid #ff8800;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
        }
        .comment-item .author { color: #ff8800; font-weight: bold; }
        .comment-item .content { margin-top: 10px; color: #fff; }
        
        .hint { 
            margin-top: 30px; 
            padding: 25px; 
            background: rgba(0, 0, 0, 0.5);
            border: 1px dashed #ff8800;
            border-radius: 10px;
        }
        .hint h4 { color: #ff8800; margin-bottom: 15px; }
        .hint p { color: #888; line-height: 1.8; }
        .hint code { color: #ff0; background: #000; padding: 2px 8px; border-radius: 3px; }
        
        .flag-box {
            background: rgba(0, 255, 0, 0.1);
            border: 2px solid #00ff00;
            border-radius: 10px;
            padding: 25px;
            margin-top: 30px;
            text-align: center;
            color: #00ff00;
            font-size: 1.3em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ XSS跨站脚本</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>这是一个留言板系统，用户输入的内容会被存储并显示给所有访问者。<br>
            <strong>目标:</strong> 利用XSS漏洞窃取管理员的Cookie<br>
            <span style="color:#ff0;">提示:</span> 存储型XSS，直接访问 <code>?view=admin</code> 获取Flag</p>
        </div>
        
        <div class="comment-box">
            <form method="POST" class="comment-form">
                <label>> 发表评论:</label>
                <textarea name="comment" placeholder="输入你的留言..."></textarea>
                <button type="submit">[ 提交留言 ]</button>
            </form>
            
            <?php if (!empty($comments)): ?>
            <div class="comments-list">
                <h4 style="color:#ff8800;margin-bottom:15px;">📝 留言列表:</h4>
                <?php foreach ($comments as $c): ?>
                <div class="comment-item">
                    <span class="author">游客:</span>
                    <div class="content"><?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($flag): ?>
        <div class="flag-box">
            <?= $flag ?>
        </div>
        <?php endif; ?>
        
        <div class="hint">
            <h4>📚 XSS知识:</h4>
            <p>
            <strong>基础弹窗测试:</strong><br>
            <code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code><br><br>
            
            <strong>获取Cookie:</strong><br>
            <code>&lt;script&gt;alert(document.cookie)&lt;/script&gt;</code><br><br>
            
            <strong>钓鱼攻击:</strong><br>
            <code>&lt;img src=x onerror="..."&gt;</code></p>
        </div>
    </div>
</body>
</html>