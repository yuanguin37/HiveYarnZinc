<?php
/**
 * 进度追踪辅助脚本
 * 在每个题目页面引用此文件，自动添加"标记完成"功能
 * 
 * 使用方法：
 * 在页面 <head> 中引入：<?php require_once '../progress_helper.php'; ?>
 * 即可实现：
 * 1. 页面加载时自动检测是否已完成
 * 2. 通过检测成功指示器（🎉、.success类等）自动标记完成
 * 3. 提供 markChallengeComplete() / checkChallengeCompleted() 函数
 */

// 获取当前页面文件名作为 challengeId
function getCurrentChallengeId() {
    $basename = basename(dirname($_SERVER['PHP_SELF']));
    return $basename === '.' ? basename($_SERVER['PHP_SELF'], '.php') : $basename;
}

$currentId = getCurrentChallengeId();

// 获取登录用户名
session_start();
$currentUser = $_SESSION['ctf_username'] ?? '';

// 未登录用户禁止访问题目页面，重定向到首页
if (empty($currentUser)) {
    header('Location: /');
    exit;
}
?>
<!-- 进度追踪脚本 -->
<script>
(function() {
    // 进度 API 地址（与 script.js 中的逻辑保持一致）
    const host = window.location.hostname;
    const PROGRESS_API_BASE = 'http://' + host + ':3001/api/progress';
    const PROGRESS_RESET_API = 'http://' + host + ':3001/api/progress/reset';
    
    // 当前登录用户（由 PHP 会话注入）
    window.CTF_USERNAME = '<?= htmlspecialchars($currentUser, ENT_QUOTES) ?>';
    
    // 当前题目 ID（由 PHP 自动获取目录名）
    window.CURRENT_CHALLENGE_ID = '<?= $currentId ?>';
    
    // ========== 工具函数 ==========
    
    // 给 API URL 添加用户参数
    function withUser(url) {
        if (window.CTF_USERNAME) {
            const sep = url.includes('?') ? '&' : '?';
            return url + sep + 'user=' + encodeURIComponent(window.CTF_USERNAME);
        }
        return url;
    }
    
    // 给请求体添加用户参数
    function withUserBody(body) {
        if (window.CTF_USERNAME) {
            body.user = window.CTF_USERNAME;
        }
        return body;
    }
    
    // 检查页面上是否存在成功/Flag 指示器
    function hasSuccessIndicator() {
        const selectors = '.success, .result.success, .flag-box, .result-box.success, .message-success';
        if (document.querySelector(selectors)) return true;
        
        if (document.body && document.body.innerHTML.indexOf('🎉') !== -1) return true;
        
        const text = document.body ? document.body.innerText : '';
        if (text.indexOf('Flag:') !== -1 || text.indexOf('Flag正确') !== -1 || text.indexOf('恭喜') !== -1) return true;
        
        return false;
    }
    
    // ========== 核心函数 ==========
    
    // 标记题目完成（手动点击按钮时调用）
    window.markChallengeComplete = function(challengeId) {
        fetch(PROGRESS_API_BASE, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(withUserBody({challengeId: challengeId, completed: true}))
        })
        .then(resp => resp.json())
        .then(data => {
            alert('✅ 进度已更新！已完成 ' + data.solvedCount + '/' + data.total + ' 题');
            const btn = document.getElementById('markCompleteBtn');
            if (btn) {
                btn.textContent = '✅ 已完成';
                btn.disabled = true;
                btn.style.background = 'linear-gradient(135deg, #00aa00, #006600)';
            }
        })
        .catch(err => {
            console.error('进度更新失败:', err);
            alert('⚠️ 进度服务连接失败，但你的答案正确！\n请返回首页点击"☆ 标记"按钮手动标记。');
        });
    };
    
    // 自动标记完成（题目页面自动调用，静默更新）
    window.markChallengeCompleteAuto = function(challengeId) {
        fetch(withUser(PROGRESS_API_BASE))
        .then(resp => resp.json())
        .then(data => {
            if (data.solved && data.solved[challengeId]) {
                console.log('[进度] 题目 ' + challengeId + ' 已完成，跳过');
                return;
            }
            
            return fetch(PROGRESS_API_BASE, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(withUserBody({challengeId: challengeId, completed: true}))
            });
        })
        .then(resp => resp ? resp.json() : null)
        .then(data => {
            if (data) {
                console.log('[进度] 题目 ' + challengeId + ' 已自动标记为完成');
                const toast = document.createElement('div');
                toast.textContent = '✅ 进度已自动更新！已完成 ' + data.solvedCount + '/' + data.total + ' 题';
                toast.style.cssText = 'position:fixed;top:20px;right:20px;background:#00aa00;color:#000;padding:15px 20px;border-radius:5px;font-family:monospace;font-weight:bold;z-index:9999;box-shadow:0 0 20px #00ff00;';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            }
        })
        .catch(err => {
            console.error('[进度] 自动标记失败:', err);
        });
    };
    
    // 检查当前题目是否已完成
    window.checkChallengeCompleted = function(challengeId) {
        fetch(withUser(PROGRESS_API_BASE))
        .then(resp => resp.json())
        .then(data => {
            if (data.solved && data.solved[challengeId]) {
                const btn = document.getElementById('markCompleteBtn');
                if (btn) {
                    btn.textContent = '✅ 已完成';
                    btn.disabled = true;
                    btn.style.background = 'linear-gradient(135deg, #00aa00, #006600)';
                }
            }
        })
        .catch(() => {});
    };
    
    // ========== 自动检测与标记 ==========
    
    window.addEventListener('DOMContentLoaded', function() {
        const challengeId = window.CURRENT_CHALLENGE_ID;
        if (!challengeId) return;
        
        if (window.checkChallengeCompleted) {
            window.checkChallengeCompleted(challengeId);
        }
        
        setTimeout(function() {
            if (hasSuccessIndicator()) {
                console.log('[进度] 检测到成功指示器，自动标记题目 ' + challengeId + ' 完成');
                if (window.markChallengeCompleteAuto) {
                    window.markChallengeCompleteAuto(challengeId);
                }
            }
        }, 500);
    });
})();
</script>
