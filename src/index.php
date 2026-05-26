<?php
session_start();
$loggedIn = isset($_SESSION['ctf_username']);
$username = $_SESSION['ctf_username'] ?? '';
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HiveYarnZinc - CTF靶场</title>
    <style><?php echo file_get_contents(__DIR__ . '/assets/style.css'); ?></style>
</head>
<body>
    <?php if (!$loggedIn): ?>
    <!-- 登录遮罩 -->
    <div class="login-overlay" id="loginOverlay">
        <div class="login-modal">
            <h2>&#x1F3F0; HiveYarnZinc</h2>
            <p class="login-subtitle">登录以开始你的 CTF 之旅</p>
            
            <div class="auth-tabs">
                <button class="auth-tab login active" onclick="switchAuthTab('login')">登 录</button>
                <button class="auth-tab register" onclick="switchAuthTab('register')">注 册</button>
            </div>
            
            <!-- 登录表单 -->
            <div class="auth-form active" id="authFormLogin">
                <div class="form-group">
                    <label>&gt; 用户名</label>
                    <input type="text" id="loginUsername" placeholder="输入用户名..." autocomplete="username">
                </div>
                <div class="form-group">
                    <label>&gt; 密码</label>
                    <input type="password" id="loginPassword" placeholder="输入密码..." autocomplete="current-password">
                </div>
                <button class="auth-btn" onclick="doLogin()">[ 登录 ]</button>
            </div>
            
            <!-- 注册表单 -->
            <div class="auth-form" id="authFormRegister">
                <div class="form-group">
                    <label>&gt; 用户名</label>
                    <input type="text" id="registerUsername" placeholder="学号+姓名，如 2409070108王钰廷" autocomplete="off">
                </div>
                <div class="form-group">
                    <label>&gt; 密码</label>
                    <input type="password" id="registerPassword" placeholder="至少4位密码" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label>&gt; 确认密码</label>
                    <input type="password" id="registerConfirm" placeholder="再次输入密码" autocomplete="new-password">
                </div>
                <button class="auth-btn" onclick="doRegister()">[ 注册并登录 ]</button>
            </div>
            
            <div class="auth-message" id="authMessage"></div>
        </div>
    </div>
    <?php endif; ?>
    
    <canvas id="matrix-bg"></canvas>
    <div class="scanlines"></div>
    
    <div class="header">
        <!-- Logo + 校名 -->
        <div class="header-top">
            <img src="assets/logo.png" alt="Logo" class="school-logo" onerror="this.style.display='none'">
            <div class="header-title-area">
                <h1 class="epic-title">
                    <span class="title-line" data-text="HiveYarnZinc">HiveYarnZinc</span>
                    <span class="title-subtitle">CTF CHALLENGE PLATFORM</span>
                </h1>
            </div>
        </div>
        <p class="subtitle">> 深圳信息职业技术大学 · 网络安全靶场系统_</p>
    </div>
    
    <div class="terminal" id="terminal">
        <div class="terminal-header">
            <div class="terminal-btn red"></div>
            <div class="terminal-btn yellow"></div>
            <div class="terminal-btn green"></div>
        </div>
        <div class="terminal-body" id="terminalBody">
            <p><span class="prompt">root@ctf:~#</span> <span class="command">cat welcome.txt</span></p>
            <p class="output">欢迎来到 HiveYarnZinc CTF靶场</p>
            <p class="output">53道题目 | 8大类别 | 支持进度追踪</p>
            <p class="output">输入 <span class="command">help</span> 查看可用命令</p>
            <div id="terminalOutput"></div>
            <p><span class="prompt">root@ctf:~#</span> <input type="text" id="terminalInput" class="terminal-input" autofocus spellcheck="false" autocomplete="off"></p>
        </div>
    </div>
    
    <div class="stats-container" id="statsContainer">
        <div class="stat-box">
            <div class="stat-number" id="totalChallenges">53</div>
            <div class="stat-label">题目总数</div>
        </div>
        <div class="stat-box">
            <div class="stat-number" id="completedCount">0</div>
            <div class="stat-label">已完成</div>
        </div>
        <div class="stat-box">
            <div class="stat-number" id="progressPercent">0%</div>
            <div class="stat-label">完成进度</div>
        </div>
        <?php if ($loggedIn): ?>
        <div class="stat-box" style="cursor:pointer;" onclick="showRankModal()">
            <div class="stat-number" style="font-size:1.8em;">&#x1F3C6;</div>
            <div class="stat-label">排行榜</div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="filter-bar" id="filterBar">
        <button class="filter-btn active" data-filter="all">全部</button>
        <button class="filter-btn" data-filter="web">Web</button>
        <button class="filter-btn" data-filter="pwn">Pwn</button>
        <button class="filter-btn" data-filter="crypto">Crypto</button>
        <button class="filter-btn" data-filter="reverse">Reverse</button>
        <button class="filter-btn" data-filter="misc">Misc</button>
        <button class="filter-btn" data-filter="mobile">Mobile</button>
        <button class="filter-btn" data-filter="blockchain">Blockchain</button>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="搜索题目..." oninput="filterChallenges()">
        </div>
        <button class="reset-progress-btn" onclick="resetProgress()">↺ 重置</button>
    </div>
    
    <div class="categories">
        <!-- Web安全 -->
        <h2 class="category-title category-header">🌐 Web安全 <span class="category-count">20题</span>
            <div class="progress-bar-container"><span class="progress-text">0/20</span><div class="progress-track"><div class="progress-fill" data-cat="web"></div></div></div>
        </h2>
        <div class="challenges-grid" data-category="web">
            <div class="challenge-card" data-category="web" data-challenge="sqli" onclick="location.href='sqli/'">
                <h3>SQL注入</h3>
                <span class="difficulty easy">Easy</span>
                <p>经典SQL注入漏洞，绕过登录获取管理员权限。</p>
                <div class="tags"><span class="tag">SQL</span><span class="tag">注入</span></div>
            </div>
            <div class="challenge-card" data-category="web" data-challenge="xss" onclick="location.href='xss/'">
                <h3>XSS跨站脚本</h3>
                <span class="difficulty easy">Easy</span>
                <p>发现网页中的XSS漏洞，窃取用户Cookie。</p>
                <div class="tags"><span class="tag">XSS</span><span class="tag">前端</span></div>
            </div>
            <div class="challenge-card" data-category="web" data-challenge="upload" onclick="location.href='upload/'">
                <h3>文件上传</h3>
                <span class="difficulty easy">Easy</span>
                <p>绕过文件上传限制，上传恶意脚本获取权限。</p>
                <div class="tags"><span class="tag">上传</span><span class="tag">绕过</span></div>
            </div>
            <div class="challenge-card" data-category="web" data-challenge="rce" onclick="location.href='rce/'">
                <h3>命令注入</h3>
                <span class="difficulty easy">Easy</span>
                <p>利用系统命令执行漏洞，执行任意命令。</p>
                <div class="tags"><span class="tag">RCE</span><span class="tag">命令执行</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='idor/'">
                <h3>IDOR越权访问</h3>
                <span class="difficulty easy">Easy</span>
                <p>修改参数越权访问其他用户的敏感信息。</p>
                <div class="tags"><span class="tag">IDOR</span><span class="tag">越权</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='info_leak/'">
                <h3>敏感信息泄露</h3>
                <span class="difficulty easy">Easy</span>
                <p>在源代码和注释中寻找泄露的敏感信息。</p>
                <div class="tags"><span class="tag">源码</span><span class="tag">注释</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='weak_pass/'">
                <h3>弱口令</h3>
                <span class="difficulty easy">Easy</span>
                <p>使用常见弱密码尝试登录系统。</p>
                <div class="tags"><span class="tag">密码</span><span class="tag">暴力</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='ssrf/'">
                <h3>SSRF服务端请求伪造</h3>
                <span class="difficulty medium">Medium</span>
                <p>利用服务器端请求伪造，读取内部文件flag.txt。</p>
                <div class="tags"><span class="tag">SSRF</span><span class="tag">内网</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='lfi/'">
                <h3>本地文件包含</h3>
                <span class="difficulty easy">Easy</span>
                <p>利用路径遍历漏洞读取服务器上的文件。</p>
                <div class="tags"><span class="tag">LFI</span><span class="tag">路径遍历</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='csrf/'">
                <h3>CSRF跨站请求伪造</h3>
                <span class="difficulty medium">Medium</span>
                <p>构造恶意链接触发转账操作，利用CSRF漏洞。</p>
                <div class="tags"><span class="tag">CSRF</span><span class="tag">转账</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='jwt/'">
                <h3>JWT令牌伪造</h3>
                <span class="difficulty medium">Medium</span>
                <p>利用JWT none算法漏洞伪造管理员令牌。</p>
                <div class="tags"><span class="tag">JWT</span><span class="tag">令牌</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='xxe/'">
                <h3>XXE外部实体注入</h3>
                <span class="difficulty medium">Medium</span>
                <p>利用XML外部实体注入读取服务器文件。</p>
                <div class="tags"><span class="tag">XXE</span><span class="tag">XML</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='ssti/'">
                <h3>SSTI模板注入</h3>
                <span class="difficulty medium">Medium</span>
                <p>服务端模板引擎渲染用户输入，存在SSTI漏洞。</p>
                <div class="tags"><span class="tag">SSTI</span><span class="tag">模板</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='regex/'">
                <h3>正则表达式绕过</h3>
                <span class="difficulty easy">Easy</span>
                <p>使用特殊字符绕过正则过滤检测。</p>
                <div class="tags"><span class="tag">正则</span><span class="tag">绕过</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='robots/'">
                <h3>Robots信息泄露</h3>
                <span class="difficulty easy">Easy</span>
                <p>通过robots.txt发现隐藏目录获取敏感文件。</p>
                <div class="tags"><span class="tag">信息收集</span><span class="tag">robots</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='http_headers/'">
                <h3>HTTP请求头攻击</h3>
                <span class="difficulty medium">Medium</span>
                <p>伪造X-Forwarded-For和X-Admin-Token绕过验证。</p>
                <div class="tags"><span class="tag">请求头</span><span class="tag">绕过</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='cors/'">
                <h3>CORS跨域漏洞</h3>
                <span class="difficulty medium">Medium</span>
                <p>利用不安全的CORS策略读取跨域API敏感数据。</p>
                <div class="tags"><span class="tag">CORS</span><span class="tag">跨域</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='open_redirect/'">
                <h3>开放重定向</h3>
                <span class="difficulty easy">Easy</span>
                <p>利用未验证的重定向功能跳转到恶意站点。</p>
                <div class="tags"><span class="tag">重定向</span><span class="tag">钓鱼</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='php_deserialize/'">
                <h3>PHP反序列化</h3>
                <span class="difficulty hard">Hard</span>
                <p>构造反序列化Payload提权为管理员获取Flag。</p>
                <div class="tags"><span class="tag">反序列化</span><span class="tag">RCE</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='log4j_sim/'">
                <h3>Log4j漏洞模拟</h3>
                <span class="difficulty medium">Medium</span>
                <p>模拟CVE-2021-44228 JNDI注入漏洞。</p>
                <div class="tags"><span class="tag">Log4j</span><span class="tag">JNDI</span></div>
            </div>
        </div>
        
        <!-- 密码学 -->
        <h2 class="category-title category-header">🔐 密码学 <span class="category-count">8题</span>
            <div class="progress-bar-container"><span class="progress-text">0/8</span><div class="progress-track"><div class="progress-fill" data-cat="crypto"></div></div></div>
        </h2>
        <div class="challenges-grid" data-category="crypto">
            <div class="challenge-card" onclick="location.href='caesar/'">
                <h3>凯撒密码</h3>
                <span class="difficulty easy">Easy</span>
                <p>学习古老的替换密码，通过暴力破解获取明文。</p>
                <div class="tags"><span class="tag">凯撒</span><span class="tag">替换</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='base64/'">
                <h3>Base64编码</h3>
                <span class="difficulty easy">Easy</span>
                <p>识别并解码Base64编码的数据，发现隐藏信息。</p>
                <div class="tags"><span class="tag">Base64</span><span class="tag">编码</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='hex/'">
                <h3>Hex编码</h3>
                <span class="difficulty easy">Easy</span>
                <p>十六进制编码，将数据转换为可读格式。</p>
                <div class="tags"><span class="tag">Hex</span><span class="tag">进制</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='url/'">
                <h3>URL编码</h3>
                <span class="difficulty easy">Easy</span>
                <p>URL编码转换，解码特殊字符。</p>
                <div class="tags"><span class="tag">URL</span><span class="tag">编码</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='morse/'">
                <h3>Morse码</h3>
                <span class="difficulty easy">Easy</span>
                <p>学习莫尔斯电码，破解加密信息。</p>
                <div class="tags"><span class="tag">Morse</span><span class="tag">电报</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='rsa/'">
                <h3>RSA加密</h3>
                <span class="difficulty medium">Medium</span>
                <p>分析RSA公钥，寻找漏洞获取私钥解密。</p>
                <div class="tags"><span class="tag">RSA</span><span class="tag">非对称</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='hash/'">
                <h3>哈希破解</h3>
                <span class="difficulty medium">Medium</span>
                <p>使用彩虹表或在线服务破解MD5哈希值。</p>
                <div class="tags"><span class="tag">MD5</span><span class="tag">彩虹表</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='php_type/'">
                <h3>PHP类型混淆</h3>
                <span class="difficulty medium">Medium</span>
                <p>利用PHP弱类型比较和MD5碰撞绕过验证。</p>
                <div class="tags"><span class="tag">类型</span><span class="tag">MD5碰撞</span></div>
            </div>
        </div>
        
        <!-- 社会工程学 -->
        <h2 class="category-title category-header">🎭 社会工程学 <span class="category-count">1题</span>
            <div class="progress-bar-container"><span class="progress-text">0/1</span><div class="progress-track"><div class="progress-fill" data-cat="misc"></div></div></div>
        </h2>
        <div class="challenges-grid" data-category="misc">
            <div class="challenge-card" onclick="location.href='qrcode/'">
                <h3>社工题目</h3>
                <span class="difficulty easy">Easy</span>
                <p>从个人信息中推断密码，结合多线索分析。</p>
                <div class="tags"><span class="tag">社工</span><span class="tag">密码</span></div>
            </div>
        </div>
        
        <!-- 隐写术 -->
        <h2 class="category-title category-header">🎨 隐写术 <span class="category-count">5题</span>
            <div class="progress-bar-container"><span class="progress-text">0/5</span><div class="progress-track"><div class="progress-fill" data-cat="misc"></div></div></div>
        </h2>
        <div class="challenges-grid" data-category="misc">
            <div class="challenge-card" onclick="location.href='stego/'">
                <h3>图片LSB隐写</h3>
                <span class="difficulty easy">Easy</span>
                <p>动态生成图片，Flag隐藏在像素LSB中。</p>
                <div class="tags"><span class="tag">LSB</span><span class="tag">动态</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='metadata/'">
                <h3>图片元数据</h3>
                <span class="difficulty easy">Easy</span>
                <p>分析图片元数据，隐藏在EXIF和注释中。</p>
                <div class="tags"><span class="tag">EXIF</span><span class="tag">元数据</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='audio_stego/'">
                <h3>音频隐写</h3>
                <span class="difficulty medium">Medium</span>
                <p>音频波形中隐藏信息，学习音频LSB隐写。</p>
                <div class="tags"><span class="tag">WAV</span><span class="tag">音频</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='file_check/'">
                <h3>文件类型识别</h3>
                <span class="difficulty easy">Easy</span>
                <p>分析神秘文件的真实内容，识别隐藏数据。</p>
                <div class="tags"><span class="tag">文件</span><span class="tag">取证</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='gif_stego/'">
                <h3>GIF帧隐写</h3>
                <span class="difficulty medium">Medium</span>
                <p>多帧PNG中有一帧隐藏Flag，比较帧间差异。</p>
                <div class="tags"><span class="tag">GIF</span><span class="tag">帧分析</span></div>
            </div>
        </div>
        
        <!-- 逆向工程 -->
        <h2 class="category-title category-header">🔧 逆向工程 <span class="category-count">2题</span>
            <div class="progress-bar-container"><span class="progress-text">0/2</span><div class="progress-track"><div class="progress-fill" data-cat="reverse"></div></div></div>
        </h2>
        <div class="challenges-grid" data-category="reverse">
            <div class="challenge-card" onclick="location.href='crackme/'">
                <h3>CrackMe逆向</h3>
                <span class="difficulty medium">Medium</span>
                <p>逆向分析程序，找到正确的注册码。</p>
                <div class="tags"><span class="tag">逆向</span><span class="tag">调试</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='js_reverse/'">
                <h3>JavaScript逆向</h3>
                <span class="difficulty easy">Easy</span>
                <p>分析混淆的JavaScript代码找出隐藏Flag。</p>
                <div class="tags"><span class="tag">JS</span><span class="tag">混淆</span></div>
            </div>
        </div>
        
        <!-- PWN -->
        <h2 class="category-title category-header">💥 PWN <span class="category-count">3题</span>
            <div class="progress-bar-container"><span class="progress-text">0/3</span><div class="progress-track"><div class="progress-fill" data-cat="pwn"></div></div></div>
        </h2>
        <div class="challenges-grid" data-category="pwn">
            <div class="challenge-card" onclick="location.href='pwn_challenge/'">
                <h3>PWN栈溢出</h3>
                <span class="difficulty hard">Hard</span>
                <p>模拟缓冲区溢出漏洞，覆盖返回地址获取权限。</p>
                <div class="tags"><span class="tag">栈溢出</span><span class="tag">PWN</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='buffer_overflow/'">
                <h3>Ret2Win溢出</h3>
                <span class="difficulty hard">Hard</span>
                <p>缓冲区溢出跳转到win()函数获取Flag。</p>
                <div class="tags"><span class="tag">ROP</span><span class="tag">溢出</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='fmtstr/'">
                <h3>格式化字符串</h3>
                <span class="difficulty medium">Medium</span>
                <p>利用printf格式化字符串漏洞泄露内存。</p>
                <div class="tags"><span class="tag">FmtStr</span><span class="tag">泄露</span></div>
            </div>
        </div>
        
        <!-- Mobile -->
        <h2 class="category-title category-header">📱 Mobile <span class="category-count">3题</span>
            <div class="progress-bar-container"><span class="progress-text">0/3</span><div class="progress-track"><div class="progress-fill" data-cat="mobile"></div></div></div>
        </h2>
        <div class="challenges-grid" data-category="mobile">
            <div class="challenge-card" onclick="location.href='mobile_apk/'">
                <h3>APK反编译</h3>
                <span class="difficulty medium">Medium</span>
                <p>反编译APK文件，在资源文件中找到隐藏Flag。</p>
                <div class="tags"><span class="tag">APK</span><span class="tag">Jadx</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='mobile_root/'">
                <h3>Root检测绕过</h3>
                <span class="difficulty medium">Medium</span>
                <p>使用Frida框架绕过Android Root检测机制。</p>
                <div class="tags"><span class="tag">Frida</span><span class="tag">Root</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='mobile_deeplink/'">
                <h3>深度链接劫持</h3>
                <span class="difficulty medium">Medium</span>
                <p>劫持Android深度链接，触发恶意操作获取Flag。</p>
                <div class="tags"><span class="tag">DeepLink</span><span class="tag">劫持</span></div>
            </div>
        </div>
        
        <!-- Blockchain -->
        <h2 class="category-title category-header">⛓️ Blockchain <span class="category-count">3题</span>
            <div class="progress-bar-container"><span class="progress-text">0/3</span><div class="progress-track"><div class="progress-fill" data-cat="blockchain"></div></div></div>
        </h2>
        <div class="challenges-grid" data-category="blockchain">
            <div class="challenge-card" onclick="location.href='blockchain_solidity/'">
                <h3>Solidity合约漏洞</h3>
                <span class="difficulty hard">Hard</span>
                <p>智能合约private变量可被读取，获取存储中的密码。</p>
                <div class="tags"><span class="tag">Solidity</span><span class="tag">存储</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='blockchain_reentrancy/'">
                <h3>重入攻击</h3>
                <span class="difficulty hard">Hard</span>
                <p>利用fallback函数发起重入攻击窃取合约资金。</p>
                <div class="tags"><span class="tag">重入</span><span class="tag">DAO</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='blockchain_privatekey/'">
                <h3>私钥泄露</h3>
                <span class="difficulty medium">Medium</span>
                <p>从交易数据中恢复泄露的以太坊私钥。</p>
                <div class="tags"><span class="tag">私钥</span><span class="tag">以太坊</span></div>
            </div>
        </div>
        
        <!-- 取证分析 -->
        <h2 class="category-title category-header">🔍 取证分析 <span class="category-count">5题</span>
            <div class="progress-bar-container"><span class="progress-text">0/5</span><div class="progress-track"><div class="progress-fill" data-cat="misc"></div></div></div>
        </h2>
        <div class="challenges-grid" data-category="misc">
            <div class="challenge-card" onclick="location.href='stegsolve/'">
                <h3>StegSolve通道分析</h3>
                <span class="difficulty medium">Medium</span>
                <p>分析图片RGB颜色通道，Flag隐藏在红色通道LSB中。</p>
                <div class="tags"><span class="tag">通道</span><span class="tag">StegSolve</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='disk_forensics/'">
                <h3>磁盘取证</h3>
                <span class="difficulty medium">Medium</span>
                <p>分析磁盘镜像，恢复被隐藏的敏感数据。</p>
                <div class="tags"><span class="tag">磁盘</span><span class="tag">恢复</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='log_analysis/'">
                <h3>日志分析</h3>
                <span class="difficulty easy">Easy</span>
                <p>从系统SSH日志中追踪攻击者获取Flag的路径。</p>
                <div class="tags"><span class="tag">日志</span><span class="tag">溯源</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='memory_dump/'">
                <h3>内存取证</h3>
                <span class="difficulty medium">Medium</span>
                <p>分析内存转储文件，找出恶意进程和隐藏Flag。</p>
                <div class="tags"><span class="tag">内存</span><span class="tag">Volatility</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='file_carving/'">
                <h3>文件雕刻</h3>
                <span class="difficulty medium">Medium</span>
                <p>从损坏文件中雕刻恢复嵌入的文件碎片。</p>
                <div class="tags"><span class="tag">雕刻</span><span class="tag">恢复</span></div>
            </div>
        </div>
        
        <!-- 杂项 -->
        <h2 class="category-title category-header">📦 杂项 <span class="category-count">3题</span>
            <div class="progress-bar-container"><span class="progress-text">0/3</span><div class="progress-track"><div class="progress-fill" data-cat="misc"></div></div></div>
        </h2>
        <div class="challenges-grid" data-category="misc">
            <div class="challenge-card" onclick="location.href='pcap/'">
                <h3>流量分析</h3>
                <span class="difficulty easy">Easy</span>
                <p>分析网络流量包，找出可疑通信和隐藏flag。</p>
                <div class="tags"><span class="tag">Wireshark</span><span class="tag">流量</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='forensics/'">
                <h3>数字取证</h3>
                <span class="difficulty easy">Easy</span>
                <p>从损坏或隐藏的文件系统中恢复证据。</p>
                <div class="tags"><span class="tag">取证</span><span class="tag">恢复</span></div>
            </div>
            <div class="challenge-card" onclick="location.href='zip/'">
                <h3>ZIP密码破解</h3>
                <span class="difficulty easy">Easy</span>
                <p>使用暴力破解获取加密ZIP文件内容。</p>
                <div class="tags"><span class="tag">ZIP</span><span class="tag">暴力</span></div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p class="footer-text">
            HiveYarnZinc CTF靶场 | 启动命令: <code>docker-compose up -d</code>
        </p>
        <p class="hack-command">> 访问地址: <a href="http://localhost:8080">http://localhost:8080</a></p>
        <div class="signature">— Tiwing —</div>
        <p class="hack-command" style="margin-top:15px;color:#888;font-size:0.9em;">
            冠名赞助: Shackles · Rick gen5 · 向阳甘三 · 玉米大王
        </p>
    </div>
    
    <script>window.CTF_USERNAME = '<?= htmlspecialchars($username, ENT_QUOTES) ?>';</script>
    <script><?php echo file_get_contents(__DIR__ . '/assets/script.js'); ?></script>
</body>
</html>