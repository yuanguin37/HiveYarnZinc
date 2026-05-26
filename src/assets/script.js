// ===== 矩阵背景 =====
(function() {
    const canvas = document.getElementById('matrix-bg');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    const chars = '01アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン';
    const fontSize = 14;
    const columns = canvas.width / fontSize;
    const drops = Array(Math.floor(columns)).fill(0).map(() => Math.random() * canvas.height);
    function draw() {
        ctx.fillStyle = 'rgba(10, 10, 15, 0.05)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#0f0';
        ctx.font = fontSize + 'px monospace';
        for (let i = 0; i < drops.length; i++) {
            ctx.fillText(chars[Math.floor(Math.random() * chars.length)], i * fontSize, drops[i]);
            if (drops[i] > canvas.height && Math.random() > 0.975) drops[i] = 0;
            drops[i] += fontSize;
        }
    }
    setInterval(draw, 50);
    window.addEventListener('resize', () => { canvas.width = window.innerWidth; canvas.height = window.innerHeight; });
})();

// ===== 进度追踪系统（服务端持久化）=====
// 自动检测宿主机 IP——在本地开发环境用 localhost，生产环境用当前域名
(function() {
    const host = window.location.hostname;
    window.PROGRESS_API_BASE = 'http://' + host + ':3001/api/progress';
    window.PROGRESS_RESET_API = 'http://' + host + ':3001/api/progress/reset';
})();

// 当前登录用户名（首页由 index.php 注入，题目页由 progress_helper.php 注入）
window.CTF_USERNAME = window.CTF_USERNAME || '';

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

// 本地缓存（同步读取，即刻刷新 UI）
let __progressCache = {};

// 从服务端加载进度（页面加载时异步调用）
async function loadProgressFromServer() {
    try {
        const resp = await fetch(withUser(window.PROGRESS_API_BASE));
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const data = await resp.json();
        __progressCache = data.solved || {};
    } catch(e) {
        console.warn('[进度服务] 连接失败，进度仅暂存于内存', e);
        __progressCache = {};
    }
    updateProgress();
    updateCardStyles();
}

// 同步读取缓存（供 terminal 命令和即时渲染使用）
function getCompleted() {
    return __progressCache;
}

// 静态Flag题目列表（这些题目在 verify_flag.php 中有静态定义）
const STATIC_CHALLENGES = ['sqli', 'xss', 'upload', 'rce', 'info_leak', 'base64', 'caesar', 'xor', 'weak_pass', 'idor'];

// 异步切换题目完成状态
async function toggleChallenge(id) {
    const data = getCompleted();
    const newState = !data[id];
    
    // 如果是取消完成（已完成 -> 未完成），直接操作，无需验证
    if (!newState) {
        delete data[id];
        updateProgress();
        updateCardStyles();
        // 异步同步到服务端
        try {
            await fetch(window.PROGRESS_API_BASE, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(withUserBody({challengeId: id, completed: false}))
            });
        } catch(e) {
            console.warn('[进度服务] 同步失败', e);
        }
        return;
    }
    
    // 标记完成（未完成 -> 已完成）
    // 判断是静态还是动态Flag题目
    if (STATIC_CHALLENGES.includes(id)) {
        // 静态Flag题目：验证Flag
        const flag = prompt('🎯 恭喜你完成了题目！\n\n请输入题目 Flag（格式：HiveYarnZinc{...}）');
        if (!flag) return; // 用户取消
        
        // 调用验证接口
        try {
            const resp = await fetch('/verify_flag.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `challenge=${encodeURIComponent(id)}&flag=${encodeURIComponent(flag)}`
            });
            const result = await resp.json();
            
            if (result.success) {
                // 验证通过，标记完成
                data[id] = true;
                updateProgress();
                updateCardStyles();
                
                // 异步同步到服务端
                try {
                    await fetch(window.PROGRESS_API_BASE, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(withUserBody({challengeId: id, completed: true}))
                    });
                } catch(e) {
                    console.warn('[进度服务] 同步失败', e);
                }
                
                alert(result.message || '✅ 进度已更新！');
            } else {
                // 验证失败
                alert(result.message || '❌ Flag 错误，请重试');
            }
        } catch(e) {
            console.error('[验证] 失败', e);
            alert('⚠️ 验证失败，请稍后重试');
        }
    } else {
        // 动态Flag题目：无法在首页验证（因为Flag基于session）
        // 提示用户需要在题目页面获取Flag
        const confirmed = confirm('⚠️ 此题为动态Flag题目。\n\n请确保你已经在题目页面成功获取了Flag，然后点击"确定"标记完成。\n\n（提示：部分题目在成功获取Flag后会自动标记完成）');
        if (!confirmed) return;
        
        // 标记完成
        data[id] = true;
        updateProgress();
        updateCardStyles();
        
        // 异步同步到服务端
        try {
            await fetch(window.PROGRESS_API_BASE, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(withUserBody({challengeId: id, completed: true}))
            });
        } catch(e) {
            console.warn('[进度服务] 同步失败', e);
        }
        
        alert('✅ 进度已更新！');
    }
}

// 重置所有进度
async function resetProgress() {
    if (!confirm('重置所有进度？')) return;
    try {
        await fetch(withUser(window.PROGRESS_RESET_API), { method: 'POST' });
    } catch(e) {
        console.warn('[进度服务] 重置失败', e);
    }
    __progressCache = {};
    updateProgress();
    updateCardStyles();
}

function getAllChallenges() {
    const cards = document.querySelectorAll('.challenge-card');
    const result = {};
    cards.forEach(card => {
        const id = card.getAttribute('data-challenge') || card.querySelector('h3')?.textContent.trim().toLowerCase().replace(/[^a-z0-9]/g,'_');
        const cat = card.closest('[data-category]')?.getAttribute('data-category') || 'misc';
        result[id] = { category: cat, name: card.querySelector('h3')?.textContent || id };
    });
    return result;
}

function updateCardStyles() {
    const data = getCompleted();
    document.querySelectorAll('.challenge-card').forEach(card => {
        const id = card.getAttribute('data-challenge');
        if (id && data[id]) card.classList.add('completed');
        else card.classList.remove('completed');
    });
    // 同时更新标记按钮状态
    updateMarkButtons();
}

function updateProgress() {
    const data = getCompleted();
    const challenges = getAllChallenges();
    const total = Object.keys(challenges).length;
    const completed = Object.keys(data).length;
    document.getElementById('totalChallenges').textContent = total;
    document.getElementById('completedCount').textContent = completed;
    document.getElementById('progressPercent').textContent = total > 0 ? Math.round(completed/total*100) + '%' : '0%';
    const catStats = {};
    Object.entries(challenges).forEach(([id, info]) => {
        const cat = info.category;
        if (!catStats[cat]) catStats[cat] = { total: 0, completed: 0 };
        catStats[cat].total++;
        if (data[id]) catStats[cat].completed++;
    });
    document.querySelectorAll('.progress-fill').forEach(bar => {
        const cat = bar.getAttribute('data-cat');
        if (cat && catStats[cat]) {
            const pct = catStats[cat].total > 0 ? Math.round(catStats[cat].completed / catStats[cat].total * 100) : 0;
            bar.style.width = pct + '%';
            const container = bar.closest('.progress-bar-container');
            if (container) container.querySelector('.progress-text').textContent = catStats[cat].completed + '/' + catStats[cat].total;
        }
    });
}

function filterChallenges() {
    const activeFilter = document.querySelector('.filter-btn.active')?.getAttribute('data-filter') || 'all';
    const searchQuery = (document.getElementById('searchInput')?.value || '').toLowerCase().trim();
    document.querySelectorAll('.challenge-card').forEach(card => {
        const cat = (card.closest('[data-category]')?.getAttribute('data-category') || 'misc');
        const text = (card.querySelector('h3')?.textContent+card.querySelector('p')?.textContent+card.querySelector('.tags')?.textContent||'').toLowerCase();
        card.classList.toggle('hidden', !((activeFilter==='all'||cat===activeFilter) && (!searchQuery||text.includes(searchQuery))));
    });
    document.querySelectorAll('.challenges-grid').forEach(grid => {
        const cat = grid.getAttribute('data-category');
        const visible = [...grid.querySelectorAll('.challenge-card')].some(c => !c.classList.contains('hidden'));
        grid.style.display = visible ? '' : 'none';
        const title = grid.previousElementSibling;
        if (title?.classList.contains('category-title')) title.style.display = (activeFilter==='all'||cat===activeFilter) ? '' : 'none';
    });
}

// ===== 终端命令系统 =====
const terminal = {
    history: [],
    historyIndex: -1,
    
    getOutput() { return document.getElementById('terminalOutput'); },
    getInput() { return document.getElementById('terminalInput'); },
    
    print(text, cls = 'output') {
        const p = document.createElement('p');
        p.className = cls;
        if (cls === 'command') {
            p.innerHTML = '<span class="prompt">root@ctf:~#</span> <span class="command">' + this.escape(text) + '</span>';
        } else {
            p.innerHTML = this.escape(text).replace(/\n/g, '<br>');
        }
        this.getOutput().appendChild(p);
        p.scrollIntoView({ behavior: 'smooth' });
    },
    
    escape(str) { return String(str).replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'})[c]); },
    
    clear() { this.getOutput().innerHTML = ''; },
    
    process(cmd) {
        cmd = cmd.trim();
        if (!cmd) return;
        this.history.push(cmd);
        this.historyIndex = this.history.length;
        this.print(cmd, 'command');
        
        const args = cmd.split(/\s+/);
        const command = args[0].toLowerCase();
        
        switch(command) {
            case 'help':
                this.print('╔══════════════════════════════════════╗');
                this.print('║      🔧 HiveYarnZinc 终端帮助        ║');
                this.print('╠══════════════════════════════════════╣');
                this.print('║ help        - 显示此帮助信息          ║');
                this.print('║ ls          - 列出所有分类及题目数    ║');
                this.print('║ cat [name]  - 查看题目信息            ║');
                this.print('║ progress    - 显示当前完成进度        ║');
                this.print('║ rank        - 显示实时排行榜          ║');
                this.print('║ clear       - 清屏                   ║');
                this.print('║ date        - 显示当前日期时间        ║');
                this.print('║ whoami      - 显示当前登录用户        ║');
                this.print('║ pwd         - 显示当前路径            ║');
                this.print('║ echo [txt]  - 回显文本                ║');
                this.print('║ neofetch    - 显示系统信息            ║');
                this.print('║ matrix      - 🌐 显示矩阵雨代码      ║');
                this.print('║ banner      - 显示靶场Banner          ║');
                this.print('╚══════════════════════════════════════╝');
                break;
                
            case 'ls':
                const cats = {web:20,pwn:3,crypto:8,reverse:2,mobile:3,blockchain:3,misc:14};
                let lsOut = '📂 分类列表:\n';
                Object.entries(cats).forEach(([c,n]) => {
                    const icons = {web:'🌐',pwn:'💥',crypto:'🔐',reverse:'🔧',mobile:'📱',blockchain:'⛓️',misc:'📦'};
                    lsOut += (icons[c]||'📁') + '  ' + c + '/\t(' + n + ' 题)\n';
                });
                lsOut += '\n共 53 道题目 | 8 大类别';
                this.print(lsOut);
                break;
                
            case 'cat':
                if (args.length < 2) { this.print('用法: cat [name]  (例如: cat sqli)', 'error-output'); break; }
                const targets = {
                    sqli: 'SQL注入 - 经典SQL注入漏洞绕过登录', xss: 'XSS跨站脚本 - 发现XSS漏洞窃取Cookie',
                    upload: '文件上传 - 绕过限制上传恶意脚本', rce: '命令注入 - 系统命令执行漏洞',
                    idor: 'IDOR越权访问 - 修改参数访问敏感信息', info_leak: '敏感信息泄露 - 查找注释中的Flag',
                    weak_pass: '弱口令 - 尝试常见弱密码', ssrf: 'SSRF - 读取内部文件',
                    lfi: '文件包含 - 路径遍历漏洞', csrf: 'CSRF - 跨站请求伪造',
                    jwt: 'JWT令牌伪造 - none算法攻击', xxe: 'XXE注入 - 外部实体注入',
                    ssti: 'SSTI模板注入 - {{7*7}}', regex: '正则绕过 - 特殊字符绕过过滤',
                    robots: 'Robots信息泄露', http_headers: 'HTTP头攻击 - X-Forwarded-For绕过',
                    cors: 'CORS跨域漏洞', open_redirect: '开放重定向',
                    php_deserialize: 'PHP反序列化', log4j_sim: 'Log4j漏洞模拟',
                    caesar: '凯撒密码 - ROT3', base64: 'Base64编码',
                    hex: 'Hex编码', url: 'URL编码', morse: 'Morse码',
                    rsa: 'RSA加密', hash: '哈希破解', php_type: 'PHP类型混淆',
                    qrcode: '社工题目', stego: '图片LSB隐写', metadata: '图片元数据',
                    audio_stego: '音频隐写', file_check: '文件类型识别', gif_stego: 'GIF帧隐写',
                    stegsolve: 'StegSolve通道分析', disk_forensics: '磁盘取证',
                    log_analysis: '日志分析', memory_dump: '内存取证', file_carving: '文件雕刻',
                    pcap: '流量分析', forensics: '数字取证', zip: 'ZIP密码破解',
                    crackme: 'CrackMe逆向', js_reverse: 'JS逆向',
                    pwn_challenge: 'PWN栈溢出', buffer_overflow: 'Ret2Win溢出',
                    fmtstr: '格式化字符串', mobile_apk: 'APK反编译',
                    mobile_root: 'Root检测绕过', mobile_deeplink: '深度链接劫持',
                    blockchain_solidity: 'Solidity合约漏洞', blockchain_reentrancy: '重入攻击',
                    blockchain_privatekey: '私钥泄露'
                };
                const search = args.slice(1).join(' ');
                const matched = Object.entries(targets).filter(([k,v]) => k.includes(search) || v.toLowerCase().includes(search));
                if (matched.length === 0) this.print('cat: ' + search + ': 未找到匹配的题目', 'error-output');
                else matched.forEach(([k,v]) => this.print(k + ': ' + v));
                break;
                
            case 'progress':
                const progData = getCompleted();
                const all = getAllChallenges();
                const done = Object.keys(progData).length;
                const pct = Math.round(done / Object.keys(all).length * 100) || 0;
                const bar = '█'.repeat(Math.floor(pct/5)) + '░'.repeat(20 - Math.floor(pct/5));
                this.print('📊 完成进度: [' + bar + '] ' + pct + '%');
                this.print('已完成 ' + done + ' / ' + Object.keys(all).length + ' 题');
                break;
                
            case 'rank':
                showRankModal();
                this.print('🏆 排行榜已打开');
                break;
                
            case 'clear': this.clear(); break;
                
            case 'date':
                this.print(new Date().toLocaleString('zh-CN', {timeZone:'Asia/Shanghai'}));
                break;
                
            case 'whoami': this.print(window.CTF_USERNAME || 'guest'); break;
                
            case 'pwd':
                this.print('/root/HiveYarnZinc'); break;
                
            case 'echo':
                this.print(args.slice(1).join(' ')); break;
                
            case 'neofetch':
                this.print('╔══════════════════════════════════╗');
                this.print('║   🔥 HiveYarnZinc CTF Platform   ║');
                this.print('╠══════════════════════════════════╣');
                this.print('║ OS:        HiveOS v3.14          ║');
                this.print('║ Kernel:    6.6.0-ctf             ║');
                this.print('║ Shell:     bash 5.2.26           ║');
                this.print('║ Terminal:  HiveTerminal          ║');
                this.print('║ CPU:       Intel Core i9 (8核)  ║');
                this.print('║ Memory:    16GB RAM              ║');
                this.print('║ Challenges: 53                   ║');
                this.print('║ Resolution: 1920x1080            ║');
                this.print('║ Sponsor:   Shackles,Rick gen5,   ║');
                this.print('║            向阳甘三,玉米大王      ║');
                this.print('╚══════════════════════════════════╝');
                break;
                
            case 'matrix':
                this.print('🌐 矩阵雨已启动，查看页面顶部！');
                break;
                
            case 'banner':
                this.print('╔══════════════════════════════════════╗');
                this.print('║      🌽 HiveYarnZinc CTF            ║');
                this.print('║      53道题目 | 8大类别              ║');
                this.print('║      支持进度追踪 | 动态Flag         ║');
                this.print('║                                      ║');
                this.print('║  冠名赞助: Shackles · Rick gen5      ║');
                this.print('║            向阳甘三 · 玉米大王       ║');
                this.print('╚══════════════════════════════════════╝');
                break;
                
            default:
                this.print(command + ': 未找到命令，输入 help 查看可用命令', 'error-output');
        }
    },
    
    init() {
        const input = this.getInput();
        const body = document.getElementById('terminalBody');
        
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                this.process(input.value);
                input.value = '';
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (this.historyIndex > 0) {
                    this.historyIndex--;
                    input.value = this.history[this.historyIndex];
                }
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (this.historyIndex < this.history.length - 1) {
                    this.historyIndex++;
                    input.value = this.history[this.historyIndex];
                } else {
                    this.historyIndex = this.history.length;
                    input.value = '';
                }
            }
        });
        
        body.addEventListener('click', () => input.focus());
    }
};

// ===== 为每个题目卡片添加"标记完成"按钮 =====
function addMarkButtons() {
    document.querySelectorAll('.challenge-card').forEach(card => {
        const id = card.getAttribute('data-challenge');
        if (!id) return;
        
        // 避免重复添加
        if (card.querySelector('.mark-btn')) return;
        
        // 创建按钮
        const btn = document.createElement('button');
        btn.className = 'mark-btn';
        btn.innerHTML = '☆ 标记';
        btn.title = '标记此题为已完成';
        btn.style.cssText = 'position:absolute;top:8px;right:8px;background:rgba(0,255,0,0.15);border:1px solid #00ff00;color:#00ff00;padding:3px 8px;cursor:pointer;font-size:0.75em;border-radius:3px;font-family:inherit;transition:all 0.3s;';
        
        btn.onmouseenter = function() { this.style.background='rgba(0,255,0,0.3)'; };
        btn.onmouseleave = function() { 
            if (!__progressCache[id]) this.style.background='rgba(0,255,0,0.15)';
        };
        
        btn.onclick = function(e) {
            e.stopPropagation(); // 阻止卡片的点击事件（跳转）
            toggleChallenge(id);
        };
        
        card.style.position = 'relative';
        card.appendChild(btn);
    });
}

// 更新标记按钮状态
function updateMarkButtons() {
    document.querySelectorAll('.challenge-card').forEach(card => {
        const id = card.getAttribute('data-challenge');
        const btn = card.querySelector('.mark-btn');
        if (!id || !btn) return;
        
        if (__progressCache[id]) {
            btn.innerHTML = '✅ 已完成';
            btn.style.background = 'rgba(0,255,0,0.3)';
            btn.style.borderColor = '#00ff00';
        } else {
            btn.innerHTML = '☆ 标记';
            btn.style.background = 'rgba(0,255,0,0.15)';
            btn.style.borderColor = '#00ff00';
        }
    });
}

// ===== 登录/注册/登出系统 =====

// 切换登录/注册 Tab
function switchAuthTab(tab) {
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
    document.querySelector('.auth-tab.' + tab).classList.add('active');
    document.getElementById('authForm' + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.add('active');
    hideAuthMsg();
}

// 显示认证消息
function showAuthMsg(msg, type) {
    const el = document.getElementById('authMessage');
    el.textContent = msg;
    el.className = 'auth-message ' + type;
}

// 隐藏认证消息
function hideAuthMsg() {
    const el = document.getElementById('authMessage');
    el.className = 'auth-message';
    el.textContent = '';
}

// 登录
async function doLogin() {
    const username = document.getElementById('loginUsername').value.trim();
    const password = document.getElementById('loginPassword').value;
    if (!username || !password) { showAuthMsg('请填写用户名和密码', 'error'); return; }
    
    try {
        const resp = await fetch('/auth/login.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({username, password})
        });
        const result = await resp.json();
        if (result.success) {
            window.CTF_USERNAME = username;
            document.getElementById('loginOverlay').remove();
            initUserBar();
            // 重新加载进度
            loadProgressFromServer();
        } else {
            showAuthMsg(result.message, 'error');
        }
    } catch(e) {
        showAuthMsg('登录失败: ' + e.message, 'error');
    }
}

// 注册
async function doRegister() {
    const username = document.getElementById('registerUsername').value.trim();
    const password = document.getElementById('registerPassword').value;
    const confirm = document.getElementById('registerConfirm').value;
    if (!username || !password) { showAuthMsg('请填写用户名和密码', 'error'); return; }
    if (password !== confirm) { showAuthMsg('两次密码输入不一致', 'error'); return; }
    
    try {
        const resp = await fetch('/auth/register.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({username, password})
        });
        const result = await resp.json();
        if (result.success) {
            window.CTF_USERNAME = username;
            document.getElementById('loginOverlay').remove();
            initUserBar();
            loadProgressFromServer();
        } else {
            showAuthMsg(result.message, 'error');
        }
    } catch(e) {
        showAuthMsg('注册失败: ' + e.message, 'error');
    }
}

// 支持回车键提交
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        const overlay = document.getElementById('loginOverlay');
        if (!overlay) return;
        const activeTab = document.querySelector('.auth-tab.active');
        if (activeTab && activeTab.classList.contains('login')) {
            doLogin();
        } else {
            doRegister();
        }
    }
});

// 登出
async function doLogout() {
    try {
        await fetch('/auth/logout.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
    } catch(e) { /* ignore */ }
    window.CTF_USERNAME = '';
    const bar = document.getElementById('userBar');
    if (bar) bar.remove();
    __progressCache = {};
    updateProgress();
    updateCardStyles();
    location.reload();
}

// 初始化用户状态栏
function initUserBar() {
    if (!window.CTF_USERNAME) return;
    const existing = document.getElementById('userBar');
    if (existing) existing.remove();
    
    const bar = document.createElement('div');
    bar.id = 'userBar';
    bar.className = 'user-bar';
    bar.innerHTML = '<span class="user-avatar"></span>'
        + '<span class="user-name">' + window.CTF_USERNAME + '</span>'
        + '<button class="rank-btn" onclick="showRankModal()">[ 排行榜 ]</button>'
        + '<button class="logout-btn" onclick="doLogout()">[ 退出 ]</button>';
    document.body.appendChild(bar);
}

// ===== 排行榜系统 =====

// 显示排行榜弹窗
async function showRankModal() {
    let overlay = document.getElementById('rankOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'rankOverlay';
        overlay.className = 'rank-overlay';
        overlay.innerHTML = '<div class="rank-modal">'
            + '<h2>&#x1F3C6; 实时排行榜</h2>'
            + '<p class="rank-subtitle">按解题数量排序</p>'
            + '<div id="rankContent"></div>'
            + '<button class="rank-close-btn" onclick="hideRankModal()">[ 关闭 ]</button>'
            + '</div>';
        document.body.appendChild(overlay);
        // 点击遮罩关闭
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) hideRankModal();
        });
    }
    overlay.classList.add('active');
    
    const content = document.getElementById('rankContent');
    content.innerHTML = '<div class="rank-empty">加载中...</div>';
    
    try {
        const resp = await fetch(window.PROGRESS_API_BASE.replace('/api/progress', '/api/rank'));
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const data = await resp.json();
        renderRankTable(data.ranking || []);
    } catch(e) {
        content.innerHTML = '<div class="rank-empty">加载失败: ' + e.message + '</div>';
    }
}

// 隐藏排行榜弹窗
function hideRankModal() {
    const overlay = document.getElementById('rankOverlay');
    if (overlay) overlay.classList.remove('active');
}

// 渲染排行榜表格
function renderRankTable(ranking) {
    const content = document.getElementById('rankContent');
    if (ranking.length === 0) {
        content.innerHTML = '<div class="rank-empty">暂无数据，快来成为第一个解题者吧！</div>';
        return;
    }
    
    let html = '<table class="rank-table">'
        + '<thead><tr><th>排名</th><th>用户</th><th>进度</th></tr></thead><tbody>';
    
    for (const item of ranking) {
        const rankClass = item.rank === 1 ? 'rank-1' : item.rank === 2 ? 'rank-2' : item.rank === 3 ? 'rank-3' : 'rank-other';
        const nameClass = item.username === window.CTF_USERNAME ? 'rank-name me' : 'rank-name';
        const pct = Math.round((item.solvedCount / item.total) * 100);
        
        html += '<tr>'
            + '<td class="rank-num ' + rankClass + '">#' + item.rank + '</td>'
            + '<td class="' + nameClass + '">' + item.username + (item.username === window.CTF_USERNAME ? ' (你)' : '') + '</td>'
            + '<td class="rank-progress">'
            + '<span class="rank-bar"><span class="rank-bar-fill" style="width:' + pct + '%"></span></span>'
            + item.solvedCount + '/' + item.total + ' (' + pct + '%)'
            + '</td>'
            + '</tr>';
    }
    
    html += '</tbody></table>';
    content.innerHTML = html;
}

// ===== 初始化 =====
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.challenge-card').forEach(card => {
        const onclick = card.getAttribute('onclick');
        if (onclick) {
            const match = onclick.match(/location\.href='([^']+)'/);
            if (match) {
                card.setAttribute('data-href', match[1]);
                if (!card.getAttribute('data-challenge')) {
                    const challengeId = match[1].replace(/\/$/, '').replace(/\//g, '_');
                    card.setAttribute('data-challenge', challengeId);
                }
            }
            card.removeAttribute('onclick');
            card.addEventListener('click', function() { const h = this.getAttribute('data-href'); if(h) window.location.href=h; });
        }
        card.addEventListener('contextmenu', function(e) { e.preventDefault(); const id=this.getAttribute('data-challenge'); if(id) toggleChallenge(id); });
    });
    
    // 添加标记完成按钮
    addMarkButtons();
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active'); filterChallenges();
        });
    });
    let searchTimer;
    document.getElementById('searchInput')?.addEventListener('input', function() { clearTimeout(searchTimer); searchTimer = setTimeout(filterChallenges, 300); });
    
    // 初始化用户状态栏（已登录用户直接显示）
    initUserBar();
    
    // 从服务端加载进度数据，加载完后渲染界面
    loadProgressFromServer();
    
    terminal.init();
});
