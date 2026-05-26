<?php
require_once '../auth_check.php';
$users = [
    1 => ['name' => '张三', 'email' => 'zhangsan@example.com', 'role' => 'user', 'secret' => '普通用户，无权限'],
    2 => ['name' => '李四', 'email' => 'lisi@example.com', 'role' => 'user', 'secret' => '普通用户，无权限'],
    3 => ['name' => '王五', 'email' => 'wangwu@example.com', 'role' => 'vip', 'secret' => 'VIP用户，额外优惠码: CTF{v1p_us3r}'],
    4 => ['name' => '管理员', 'email' => 'admin@hiveyarnzinc.com', 'role' => 'admin', 'secret' => 'CTF{1d0r_vuln3r4b1l1ty}']
];

$uid = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$user = $users[$uid] ?? $users[1];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>IDOR越权访问 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1a1a2e, #16213e); min-height: 100vh; color: #fff; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #e94560; font-size: 2.5em; }
        .header a { color: #e94560; text-decoration: none; }
        .info { background: rgba(233,69,96,0.15); border: 1px solid #e94560; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .info h3 { color: #e94560; }
        .user-list { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
        .user-btn { padding: 10px 20px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; color: #fff; text-decoration: none; cursor: pointer; }
        .user-btn:hover { background: rgba(233,69,96,0.3); border-color: #e94560; }
        .profile { background: rgba(255,255,255,0.05); border-radius: 15px; padding: 30px; }
        .profile h2 { color: #e94560; margin-bottom: 15px; }
        .profile p { margin: 10px 0; color: #ccc; }
        .role-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 0.9em; }
        .role-admin { background: #f44336; }
        .role-vip { background: #ff9800; }
        .role-user { background: #4caf50; }
        .secret { background: rgba(233,69,96,0.2); padding: 15px; border-radius: 8px; margin-top: 20px; border-left: 3px solid #e94560; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>🔓 IDOR越权访问</h1><p><a href="../index.php">← 返回首页</a></p></div>
        <div class="info"><h3>💡 挑战</h3><p>这是一个用户资料页面。<br><strong>目标:</strong> 通过修改URL参数访问管理员或其他用户的敏感信息</p></div>
        <div class="user-list">
            <a href="?id=1" class="user-btn">用户1</a>
            <a href="?id=2" class="user-btn">用户2</a>
            <a href="?id=3" class="user-btn">用户3</a>
            <a href="?id=4" class="user-btn">用户4</a>
        </div>
        <div class="profile">
            <h2><?= htmlspecialchars($user['name']) ?></h2>
            <p><strong>邮箱:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>角色:</strong> <span class="role-badge role-<?= $user['role'] ?>"><?= strtoupper($user['role']) ?></span></p>
            <div class="secret"><strong>私密信息:</strong> <?= htmlspecialchars($user['secret']) ?></div>
        </div>
        <div class="info" style="margin-top:20px;"><h4>📚 提示:</h4><p>尝试修改URL中的 <code>id=1</code> 参数为其他值</p></div>
    </div>
</body>
</html>
