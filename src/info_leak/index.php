<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>敏感信息泄露 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #0a0a0f; color: #00ff00; min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #ff3333; }
        .header h1 { font-size: 2.5em; color: #ff3333; text-shadow: 0 0 20px #ff3333; }
        .back-link { color: #00ffff; text-decoration: none; }
        .info-box { background: rgba(255,50,50,0.05); border: 1px solid #ff3333; border-radius: 10px; padding: 25px; margin-bottom: 30px; }
        .info-box h3 { color: #ff3333; margin-bottom: 15px; }
        .info-box p { color: #aaa; line-height: 1.8; }
        .info-box code { background: #000; padding: 2px 8px; border-radius: 3px; color: #ff0; }
        .page-content { background: rgba(0,0,0,0.8); border: 2px solid #ff3333; border-radius: 15px; padding: 40px; }
        .page-content h2 { color: #ff3333; margin-bottom: 20px; }
        .page-content p { color: #aaa; margin-bottom: 15px; line-height: 1.8; }
        .secret-box { background: rgba(255,255,255,0.03); border: 1px dashed #666; border-radius: 10px; padding: 20px; margin: 20px 0; }
        .secret-box .label { color: #666; font-size: 0.9em; }
        .secret-box .value { color: #00ffff; font-size: 1.1em; margin-top: 5px; }
        .comment-section { margin-top: 30px; padding-top: 20px; border-top: 1px solid #333; }
        .comment { background: rgba(0,0,0,0.3); padding: 15px; border-radius: 5px; margin-bottom: 10px; }
        .comment .author { color: #ff8800; }
        .comment .text { color: #aaa; margin-top: 5px; }
        .admin-note { background: #000; border: 1px solid #ff0; padding: 15px; border-radius: 5px; margin-top: 30px; color: #ff0; }
        .footer-comment { color: #666; font-size: 0.8em; margin-top: 20px; }
        .flag-reveal { background: rgba(0,255,0,0.1); border: 2px solid #00ff00; padding: 20px; border-radius: 10px; margin-top: 30px; text-align: center; display: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔓 敏感信息泄露</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>开发者在发布网站时经常忘记删除调试信息、注释和备份文件。<br>
            <strong>目标:</strong> 在页面中找到管理员遗留的敏感信息，获取Flag</p>
        </div>
        
        <div class="page-content">
            <h2>📄 关于我们</h2>
            <p>欢迎访问我们的官方网站！我们致力于为用户提供最优质的服务。</p>
            <p>联系电话: 400-123-4567</p>
            <p>邮箱: contact@example.com</p>
            
            <div class="secret-box">
                <div class="label">// 开发环境数据库配置 (测试用)</div>
                <div class="value">Host: localhost</div>
                <div class="value">User: root</div>
                <div class="value">Pass: <?php echo "adm1n_p@ssw0rd_2024"; ?></div>
                <div class="value">Database: ctf_production</div>
            </div>
            
            <div class="comment-section">
                <h3 style="color:#ff8800;margin-bottom:15px;">💬 用户留言</h3>
                <div class="comment">
                    <span class="author">游客001:</span>
                    <div class="text">网站很不错，支持一下！</div>
                </div>
                <div class="comment">
                    <span class="author">admin:</span>
                    <div class="text">感谢反馈！有任何问题请联系 support@example.com</div>
                </div>
                <!-- TODO: 记得删除这个测试Flag: HiveYarnZinc{info_leak_source} -->
                <div class="comment">
                    <span class="author">开发者:</span>
                    <div class="text">新版本已上线，修复了若干bug。</div>
                </div>
                <!-- 
                    内部备注 - 不要发布到生产环境!
                    数据库备份: /backup/db_backup_2024.sql
                    Flag: HiveYarnZinc{source_code_comment}
                -->
                <div class="comment">
                    <span class="author">游客002:</span>
                    <div class="text">希望越做越好！</div>
                </div>
            </div>
            
            <div class="admin-note">
                ⚠️ 管理员备注: 这是测试环境Flag <code>HiveYarnZinc{info_leak_admin}</code>，记得上线前删除！
            </div>
            
            <p class="footer-comment">© 2024 Example Company. All rights reserved.</p>
            
            <!-- ================== BACKUP FILE ================== -->
            <!-- Version: 2.0 | Date: 2024-01-15 | Author: Tiwing -->
            <!-- Hidden Flag: HiveYarnZinc{info_leak_backup} -->
            <!-- =============================================== -->
        </div>
        
        <div class="info-box" style="margin-top:30px;">
            <h3>🔍 提示</h3>
            <p>查看网页源代码(Ctrl+U)，寻找被注释掉的内容！</p>
        </div>
    </div>
</body>
</html>