# HiveYarnZinc 靶场架构设计文档

> 本文档面向希望理解靶场设计思路、进行二次开发或教学演示的读者。

---

## 一、项目定位

HiveYarnZinc 是一个面向网络安全初学者的 **轻量级 CTF 靶场**，核心目标是：

1. **低门槛部署** — 一台机器、一条命令即可启动，无需手动配置 LAMP/LEMP。
2. **贴近实战** — 题目环境尽量还原真实渗透场景（如 SQL 注入使用真实 MySQL 而非 SQLite）。
3. **防作弊机制** — 动态 Flag 确保不同学员、不同时间打开题目得到不同答案。
4. **进度可视化** — 实时追踪做题进度，支持分类筛选、终端交互式首页与实时排行榜。

---

## 二、总体架构

```
┌─────────────────────────────────────────────────────────────┐
│                         宿主机 (Host)                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  ctf-mysql   │  │ ctf-platform │  │ ctf-progress │      │
│  │   MySQL 8.0  │  │ PHP + Apache │  │ Flask 服务   │      │
│  │   端口 3307  │  │  端口 8080   │  │  端口 3001   │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
│         │                 │                 │              │
│         └─────────────────┴─────────────────┘              │
│                    Docker Compose 编排                      │
└─────────────────────────────────────────────────────────────┘
```

### 2.1 为什么选择 Docker Compose？

| 考量点 | 说明 |
|--------|------|
| **环境一致性** | 开发、测试、生产环境完全一致，避免 "在我机器上能跑"。 |
| **一键部署** | `docker-compose up -d` 启动全部服务，降低使用门槛。 |
| **服务隔离** | Web、数据库、进度服务各自独立，便于单独升级或调试。 |
| **离线部署** | `docker save` / `docker load` 可将完整环境打包到无网络环境。 |

---

## 三、服务拆分与职责

### 3.1 `ctf-platform` — Web 题目入口

**技术栈：** `php:apache`（PHP 8.x + Apache 2.4）

**职责：**
- 承载 53 道 CTF 题目（PHP 单文件形式）。
- 提供首页终端交互 UI、题目分类筛选、进度展示。
- 动态生成 Flag（优先基于登录用户名，回退到浏览器指纹）。
- 部分题目需要生成动态资源（图片隐写、音频隐写等）。

**关键设计决策：**

| 决策 | 理由 |
|------|------|
| **PHP 单文件题目** | 每道题一个 `index.php`，独立目录，便于单独调试和增删题目。 |
| **用户名 + 浏览器指纹双重标识** | 登录用户基于用户名生成稳定标识，Flag 跨设备一致；未登录时回退到浏览器指纹（向后兼容）。 |
| **GD/GMP 扩展** | 图片处理（隐写题生成）和 RSA 大数运算所需。 |
| **内联 CSS/JS** | `index.php` 通过 `file_get_contents` 加载 `assets/style.css` 和 `script.js`，减少 HTTP 请求，提升加载速度。 |

### 3.2 `ctf-mysql` — SQL 注入专用数据库

**技术栈：** `mysql:8.0` 官方镜像

**职责：**
- 为 SQL 注入题目提供 **真实 MySQL 环境**。
- 通过 `init.sql` 在容器首次启动时自动建表、插数据。
- 数据持久化到 Docker 命名卷 `mysql_data`，重启不丢失。

**关键设计决策：**

| 决策 | 理由 |
|------|------|
| **独立 MySQL 容器** | 原方案使用 SQLite3 内存数据库，学员无法观察底层数据；独立 MySQL 后可用 Navicat/DBeaver 直连，加深理解。 |
| **宿主机端口 3307** | 避免与宿主机已有的 MySQL（默认 3306）冲突。 |
| **Healthcheck + depends_on** | PHP 服务等待 MySQL 就绪后才启动，避免连接失败。 |
| **环境变量配置** | `src/sqli/index.php` 通过 `getenv()` 读取连接信息，便于在不修改代码的情况下切换数据库地址。 |

### 3.3 `ctf-progress` — 进度追踪服务

**技术栈：** `python:3.11-slim` + Flask

**职责：**
- 提供 RESTful API：`GET/POST /api/progress` 查询/更新做题进度。
- 数据持久化到 JSON 文件（通过 Docker 卷挂载到宿主机 `progress_data/`）。
- 支持跨域（CORS），允许前端页面 `localhost:8080` 直接调用。

**关键设计决策：**

| 决策 | 理由 |
|------|------|
| **独立服务而非 PHP 内置** | 将状态存储与题目逻辑解耦，便于后续扩展（如接入数据库、用户系统）。 |
| **JSON 文件存储** | 53 题、单人使用场景下，JSON 足够轻量，无需引入 Redis/MySQL 等重型存储。 |
| **线程锁 (threading.Lock)** | 防止并发写入导致 JSON 损坏。 |
| **CORS 开放** | 前端纯静态页面通过 JS `fetch()` 调用，必须允许跨域。 |

### 3.4 用户认证系统

**技术栈：** PHP Session + JSON 文件

从 v2.0 开始，靶场引入了多用户支持，每位用户必须登录后才能做题。

**职责：**
- 提供用户注册/登录/登出接口（`src/auth/login.php`, `register.php`, `logout.php`, `status.php`）。
- 用户数据存储在 `src/flags/users.json`（密码使用 `password_hash()` 加密）。
- 登录状态通过 PHP Session 保持，`$_SESSION['ctf_username']` 全局可访问。

**关键设计决策：**

| 决策 | 理由 |
|------|------|
| **JSON 文件存储用户** | 无需额外数据库，轻量级部署，适合 100 人以下使用场景。 |
| **PHP Session 保持登录** | 无需 JWT Token，减少前端复杂度，PHP 原生支持。 |
| **密码哈希存储** | `password_hash(PASSWORD_DEFAULT)` 自动选择 bcrypt，安全可靠。 |
| **用户名 + 密码登录** | 简单直观，适合课堂教学场景。 |

**与进度系统的集成：**

所有进度 API 调用都需要附带 `user` 参数，Flask 服务根据用户名隔离数据存储：

```python
# 数据格式
{
    "张三": { "sqli": true, "xss": true },
    "李四": { "caesar": true, "morse": true }
}
```

**与动态 Flag 的集成：**

`flag_helper.php` 的 `getUserKey()` 方法优先使用登录用户名生成 Flag 种子：

```php
if (isset($_SESSION['ctf_username'])) {
    return 'user_' . md5($username);  // 同一用户 Flag 始终一致
}
// 回退到浏览器指纹
```

**访问控制：**

所有 53 道题目页面都进行了登录检查：
- 使用 `flag_helper.php` 的题目 → 在构造函数中自动检查
- 使用 `progress_helper.php` 的题目 → 在文件头部自动检查
- 其余题目 → 引入 `auth_check.php` 进行检查

---

## 四、动态 Flag 机制

### 4.1 为什么需要动态 Flag？

传统静态 Flag（如 `flag{sql_injection_123}`）存在两个问题：
1. **作弊** — 学员可以直接复制他人答案提交。
2. ** writeup 泄露** — 网上公开的解题报告会暴露 Flag。

### 4.2 实现原理（v2.0 起）

```php
// flag_helper.php
class FlagManager {
    public function generateFlag($challenge, $seed = null) {
        $userKey = $this->getUserKey();  // 优先使用登录用户名
        $key = "{$challenge}_{$userKey}";
        
        $existing = $this->loadFlag($key);
        if ($existing !== null) return $existing;
        
        $hash = substr(md5($seed), 0, 8);
        $flag = "HiveYarnZinc{{$challenge}_{$hash}}";
        $this->saveFlag($key, $flag);
        return $flag;
    }
}
```

**用户标识生成策略（优先级顺序）：**

| 优先级 | 方式 | 适用场景 |
|:------:|------|----------|
| 1 | 登录用户名 → `'user_' . md5($username)` | 已登录用户，跨设备保持 Flag 一致 |
| 2 | 浏览器指纹 → `md5(UA + IP + Accept + 语言)` | 未登录用户（向后兼容 v1.x） |

```php
private function getUserKey() {
    // 优先使用登录用户名
    if (isset($_SESSION['ctf_username'])) {
        return 'user_' . md5($_SESSION['ctf_username']);
    }
    // 回退到浏览器指纹
    $fingerprint = $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'];
    return md5($fingerprint);
}
```

**效果：** 同一用户登录后，无论使用哪台电脑，Flag 始终一致；不同用户查看同一题目得到不同 Flag，彻底杜绝作弊。

---

## 五、题目组织方式

### 5.1 目录结构

```
src/
├── index.php              # 首页（终端交互 + 题目列表 + 登录弹窗）
├── flag_helper.php        # 动态 Flag 生成器（含登录检测）
├── verify_flag.php        # Flag 验证接口
├── progress_helper.php    # 进度查询封装（含登录检测）
├── auth_check.php         # 简易登录检查（给不含 helper 的页面用）
├── auth/                  # 用户认证模块
│   ├── users.php          #   用户数据管理（JSON 读写、密码哈希）
│   ├── login.php          #   登录 API
│   ├── register.php       #   注册 API
│   ├── logout.php         #   登出 API
│   └── status.php         #   登录状态查询 API
├── assets/                # 公共静态资源（CSS、JS、Logo）
│   ├── style.css
│   ├── script.js
│   └── logo.png
├── sqli/                  # SQL注入 🐬
│   └── index.php
├── xss/                   # XSS
│   └── index.php
├── upload/                # 文件上传
│   └── index.php
├── ... (53个题目目录)
└── flags/                 # 运行时生成的 Flag 缓存文件 + 用户数据
    └── users.json         # 用户账号信息
```

### 5.2 新增题目的成本

新增一道题只需：
1. 在 `src/` 下新建目录（如 `new_challenge/`）。
2. 放入 `index.php`（题目逻辑 + 前端展示）。
3. 在文件顶部添加登录检查：`require_once '../auth_check.php';`（或 `../progress_helper.php` / `../flag_helper.php`）。
4. 在 `src/index.php` 的题目数组中注册（约 5 行代码）。
5. 如需动态 Flag，调用 `FlagManager::generateFlag('new_challenge')`。

无需修改 Dockerfile、docker-compose.yml 或重启其他服务。

---

## 六、部署与分发策略

### 6.1 在线部署（有网络）

```bash
docker-compose up -d
```

- 自动拉取 `mysql:8.0` 和 `python:3.11-slim`。
- 自动构建 `ctf-platform` 和 `ctf-progress`。

### 6.2 离线部署（无网络）

```bash
# 在有网络的环境执行
./export-images.sh        # 导出 3 个镜像 tar 包

# 将 images/ 目录复制到目标机器
./import-images.sh        # 导入镜像
./setup.sh                # 一键启动
```

**导出内容：**

| 文件 | 大小 | 说明 |
|------|------|------|
| `ctf-platform.tar` | ~500MB | 包含 `php:apache` + 扩展 + 题目源码 |
| `ctf-progress.tar` | ~150MB | 包含 `python:3.11-slim` + Flask |
| `mysql-8.0.tar` | ~600MB | 完整 MySQL 8.0 镜像 |

> `docker save` 会自动包含基础镜像的所有层，因此 `ctf-platform.tar` 已内含 `php:apache`，无需单独导出基础镜像。

### 6.3 跨平台支持

| 平台 | 脚本 |
|------|------|
| Linux/macOS | `setup.sh`、`export-images.sh`、`import-images.sh` |
| Windows | `setup.bat`、`import-images.bat` |

---

## 七、安全与隔离考量

### 7.1 容器层面

- 各服务运行在独立容器中，MySQL 不对外暴露 root 密码（仅 `ctf_user` 可访问 `ctf_sqli` 库）。
- PHP 容器以 `www-data` 用户运行，降低权限。
- `restart: unless-stopped` 保证服务异常退出后自动恢复。

### 7.2 题目层面

- SQL 注入题目虽然存在漏洞，但数据库用户 `ctf_user` 仅有 `ctf_sqli` 库的读写权限，无法跨库操作。
- 文件上传题目限制 MIME 类型和扩展名，上传目录无执行权限。
- 命令注入题目使用 `escapeshellarg` 或白名单过滤危险字符。

### 7.3 数据持久化

| 数据 | 存储位置 | 持久化方式 |
|------|----------|------------|
| 做题进度 | `progress_data/progress.json` | 宿主机目录挂载 |
| MySQL 数据 | Docker 卷 `mysql_data` | 命名卷 |
| 动态 Flag | `src/flags/`（容器内） | 容器文件系统（重启保留） |
| 用户账号 | `src/flags/users.json`（容器内） | 容器文件系统（重启保留） |

---

## 八、扩展性预留

### 8.1 已预留的扩展点

| 扩展点 | 当前状态 | 未来方向 |
|--------|----------|----------|
| **LLM API 接入** | 本地硬编码回复 | 预留 API 服务层、环境变量配置、流式输出 |
| **用户系统** | 用户名+密码登录 ✅ | 可接入 OAuth / 校园统一认证 |
| **实时排行榜** | Flask 聚合全量进度数据 ✅ | 可接入 Redis 排序集合优化大数据量 |
| **数据库后端** | JSON 文件 | 可替换为 SQLite / PostgreSQL |
| **题目类型** | PHP 单文件 | 可接入 Docker-in-Docker 运行独立容器题 |
| **日志审计** | 无 | 可接入 ELK 或独立日志服务 |

### 8.2 微服务化路径

若题目数量增长到 200+，当前架构可按以下路径演进：

```
当前: 单 PHP 容器承载全部题目
        ↓
阶段1: 按类别拆分为多个 PHP 容器（Web、Crypto、Pwn 各一个）
        ↓
阶段2: 每道题独立容器（Docker-in-Docker 或 Kubernetes Pod）
        ↓
阶段3: 接入统一网关（Nginx / Traefik）+ 服务注册发现
```

---

### 8.3 排行榜实现原理

排行榜基于 `progress_service` 已有的全量进度数据实时计算：

```python
@app.route('/api/rank', methods=['GET'])
def get_rank():
    all_data = load_all_progress()  # { "张三": {"sqli": true, ...}, ... }
    rank_list = []
    for username, solved in all_data.items():
        rank_list.append({
            'username': username,
            'solvedCount': len(solved),
            'rank': ...
        })
    rank_list.sort(key=lambda x: (-x['solvedCount'], x['username']))
    return jsonify({'ranking': rank_list})
```

**前端展示：** 赛博朋克风格弹窗表格，前三名使用金银铜配色，当前用户高亮显示，附带进度条可视化。

**终端集成：** 首页终端输入 `rank` 命令可直接打开排行榜弹窗。

---

## 九、总结

HiveYarnZinc 的架构设计遵循 **"简单优先、逐步演进"** 的原则：

1. **Docker Compose 三容器架构** 在简单性与隔离性之间取得平衡。
2. **PHP Session 用户认证** 以零外部依赖实现多用户进度与 Flag 隔离。
3. **动态 Flag + 用户名/浏览器指纹** 以最小成本实现防作弊，跨设备兼容。
4. **实时排行榜** 基于 Flask 全量数据聚合，无需额外数据库。
5. **独立 MySQL 容器** 让 SQL 注入题目从 "玩具级" 升级为 "实战级"。
6. **Flask 进度服务** 将状态管理解耦，为后续扩展留足空间。
7. **镜像导出/导入机制** 让离线部署和内网分发成为可能。

这套架构支撑 53 道题目、多用户并发访问绰绰有余；当需求增长时，每个服务都可以独立扩展或替换，无需推倒重来。
