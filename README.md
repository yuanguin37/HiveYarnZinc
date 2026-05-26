# HiveYarnZinc CTF靶场

一个轻量级的CTF靶场平台，基于Docker部署，包含 **53道** 入门与进阶题目，覆盖 **8大类别**。
支持 **动态Flag生成**、**进度追踪** 和 **分类筛选**。

> 冠名赞助: Shackles · Rick gen5 · 向阳甘三 · 玉米大王

---

## 📦 题目概览

| 分类 | 数量 | 题目清单 |
|:----:|:----:|----------|
| 🌐 **Web** | 20 | SQL注入、XSS、文件上传、命令注入、IDOR、信息泄露、弱口令、SSRF、LFI、CSRF、JWT、XXE、SSTI、正则绕过、Robots、HTTP头攻击、CORS、开放重定向、PHP反序列化、Log4j模拟 |
| 🔐 **Crypto** | 8 | 凯撒密码、Base64、Hex、URL编码、Morse码、RSA、哈希破解、PHP类型混淆 |
| 💥 **PWN** | 3 | PWN栈溢出、Ret2Win溢出、格式化字符串 |
| 🔧 **Reverse** | 2 | CrackMe逆向、JS逆向 |
| 📱 **Mobile** | 3 | APK反编译、Root检测绕过、深度链接劫持 |
| ⛓️ **Blockchain** | 3 | Solidity合约漏洞、重入攻击、私钥泄露 |
| 🎨 **隐写/取证** | 10 | 图片LSB隐写、图片元数据、音频隐写、文件类型识别、GIF帧隐写、StegSolve通道分析、磁盘取证、日志分析、内存取证、文件雕刻 |
| 📦 **Misc** | 4 | 流量分析、数字取证、ZIP密码破解、社工题目 |

**共计: 53题 | 8大类别 | 43题动态Flag**

---

## ✨ 核心特性

- **用户系统** — 支持注册/登录，多用户做题进度完全隔离
- **实时排行榜** — 按解题数量实时排名，支持终端 `rank` 命令和弹窗查看
- **动态Flag** — 43道题使用 `flag_helper.php` 动态生成，每人每题独立Flag
- **分类筛选** — 按 Web/Pwn/Crypto/Reverse/Misc/Mobile/Blockchain 过滤
- **进度追踪** — 输入正确 Flag 后标记完成（防作弊），支持进度条可视化
- **终端交互** — 首页 `root@ctf:~#` 支持 help/ls/cat/progress/rank 等命令
- **动态资源** — 图片隐写、音频隐写等题目可实时生成不同内容
- **Flag验证** — 标记完成时需输入正确 Flag，确保真正掌握题目解法
- **MySQL实战** — SQL注入题目使用独立 **MySQL 8.0** 数据库（端口 3307），更贴近真实渗透场景

---

## 🐳 Docker 部署（推荐）

本项目推荐使用 Docker 部署，一键构建，无需手动安装 PHP/Apache 等环境。

**服务架构：**

| 服务 | 容器名 | 端口映射 | 说明 |
|:----:|:------:|:--------:|:----:|
| `ctf-platform` | PHP+Apache | 8080 → 80 | Web 题目入口 |
| `ctf-mysql` | MySQL 8.0 | 3307 → 3306 | SQL注入专用数据库 |
| `ctf-progress` | Flask 进度服务 | 3001 → 3001 | 进度追踪 API |

### 🚀 方式一：使用预构建镜像（推荐，最快）

> 适合大多数用户，无需等待构建，直接导入镜像启动即可。

#### Linux (bash)

```bash
# 1. 下载项目
git clone https://github.com/xxx/HiveYarnZinc
cd HiveYarnZinc

# 2. 导入镜像（确保 images/ 目录下有镜像文件）
chmod +x import-images.sh
./import-images.sh

# 3. 修改 docker-compose.yml，使用预构建镜像（而非本地构建）
#    已默认配置，直接启动即可

# 4. 启动靶场
docker-compose up -d

# 5. 访问 http://localhost:8080
```

#### Windows (PowerShell)

```powershell
# 1. 下载项目（或使用 Git）
# 2. 导入镜像
.\import-images.bat

# 3. 启动靶场
docker-compose up -d

# 4. 访问 http://localhost:8080
```

#### 获取镜像

- **方式A**：从发布页面下载 `images/hiveyarnzinc-images.tar.gz`
- **方式B**：联系作者获取镜像文件
- **方式C**：自己构建一次，然后导出镜像（见下方"方式二"）

#### 管理命令（通用）

```bash
docker-compose down              # 停止
docker-compose up -d            # 启动
docker-compose logs -f          # 查看日志
docker-compose restart          # 重启
docker-compose ps               # 查看状态
```

#### 修改端口

编辑 `docker-compose.yml`，修改端口映射：
```yaml
ports:
  - "8888:80"   # 将 8080 改为你想要的端口
  # MySQL 端口可在 ctf-mysql 服务的 ports 中修改（默认 3307->3306）
```

---

## 🎯 使用方法

### 1. 访问靶场
- Web 题目：`http://localhost:8080`
- 进度 API：`http://localhost:3001/health`
- MySQL 数据库：`localhost:3307`（可用 Navicat/DBeaver 等工具直连）

#### MySQL 连接信息（SQL注入题目）
```yaml
Host:     127.0.0.1
Port:     3307
Database: ctf_sqli
User:     ctf_user
Password: ctf_password
```

> 连接 MySQL 后可直接查看所有数据，有助于理解 SQL 注入原理。

### 2. 完成题目并标记进度

#### 方式 A：首页标记（推荐）
1. 在首页找到想要挑战的题目
2. 点击题目卡片进入题目页面
3. 解题获取 Flag（格式：`HiveYarnZinc{...}`）
4. **返回首页**，点击题目卡片右上角的 **"☆ 标记"** 按钮
5. 在弹出框中输入正确的 Flag
6. 验证通过后，进度自动更新 ✅

#### 方式 B：题目页面自动标记
- 部分题目（如 SQL 注入）在成功获取 Flag 后，**自动标记完成**
- 无需手动操作，进度自动更新

### 3. 查看进度
- 首页顶部显示：**已完成 X/53 题（XX%）**
- 各分类进度条实时更新
- 已完成的题目卡片会显示 ✅ 标记

### 4. 重置进度
- 点击首页右上角的 **[ 重置进度 ]** 按钮
- 确认后清空所有进度

---

### 🔧 方式二：本地构建（自定义）

适合需要修改代码或自定义功能的用户。

#### 1. 安装 Docker

**Kali / Debian / Ubuntu：**
```bash
# 方式一（推荐）：Docker 官方一键脚本
curl -fsSL https://get.docker.com | sh

# 方式二（备用）：apt 直接安装
# apt update && apt install -y docker.io docker-compose
```

**CentOS 7：**
```bash
# 添加 Docker CE 源
sudo yum install -y yum-utils
sudo yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo

# 安装 Docker CE（注意：CentOS 7 不提供 docker-compose-plugin）
sudo yum install -y docker-ce docker-ce-cli containerd.io

# 手动安装 docker-compose 二进制
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

> CentOS 7 不支持 `docker compose` 插件命令，后续操作请始终使用 `docker-compose`（带横线版）。

**CentOS 8+ / RHEL 8+：**
```bash
sudo dnf install -y yum-utils
sudo yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
sudo dnf install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
```

**Windows / macOS：**
下载安装 [Docker Desktop](https://www.docker.com/products/docker-desktop/)

### 2. 启动 Docker 服务

```bash
sudo systemctl enable --now docker    # Linux
```

### 3. 宿主机安装题目工具（做题必备）

以下工具用于在宿主机上解题（隐写分析、逆向、PWN、取证等），在启动靶场前装好。

#### Kali / Debian / Ubuntu

```bash
sudo apt update
sudo apt install -y steghide libimage-exiftool-perl binwalk sqlite3 \
    gdb python3 python3-pip binutils
```

#### CentOS 7

```bash
# EPEL 源提供了 perl-Image-ExifTool 等工具
sudo yum install -y epel-release
sudo yum install -y perl-Image-ExifTool sqlite3 gdb python3 python3-pip binutils
sudo pip3 install binwalk
```

> `steghide` 在 CentOS 7 仓库中不可用，隐写类题目建议在 Kali 虚拟机中完成。

#### 4. 构建并启动靶场

```bash
# 克隆项目（如已下载则跳过）
# git clone https://github.com/xxx/HiveYarnZinc
# cd HiveYarnZinc

# 拉取 php 镜像（docker-compose up 也会自动拉取）
docker pull php:apache

# 拉取 MySQL 镜像（docker-compose up 也会自动拉取）
docker pull mysql:8.0

# 构建镜像（Dockerfile 已自动安装好 PHP 扩展和服务端依赖）
docker-compose build --no-cache 	#kali
docker compose build --no-cache		#CentOS7

# 启动容器
docker-compose up -d		#kali
docker compose up -d		#CentOS7

# 访问 http://localhost:8080
```

> CentOS 7 用户请使用 `docker-compose` 替代 `docker compose`（下同）。
>
> **MySQL 数据库** 在 `docker-compose up` 时会自动初始化，无需额外操作。
> 首次启动 MySQL 可能会稍慢（初始化数据表），PHP 服务会等待 MySQL 就绪后才开始接受请求。

#### 5. 导出镜像（可选）

构建完成后，可以导出镜像分发给其他用户：

```bash
chmod +x export-images.sh
./export-images.sh
```

导出的镜像文件位于 `images/` 目录，可以分发给其他用户使用 `import-images.sh` 导入。

---

## 🔧 Docker 镜像加速

如果拉取镜像失败，配置国内镜像源：

编辑 `/etc/docker/daemon.json`（Linux）：
```json
{
   "registry-mirrors": [
       "https://docker.m.daocloud.io",
       "https://docker.1ms.run",
       "https://ccr.ccs.tencentyun.com",
       "https://hub.xdark.top",
       "https://docker-0.unsee.tech",
       "https://docker.tbedu.top",
       "https://docker.hlmirror.com"
  ]
}
```

重启 Docker：
```bash
sudo systemctl daemon-reload
sudo systemctl restart docker
```

---

## 📡 配置国内 APT/YUM 源（加速依赖安装）

> 如果在安装 Docker 或系统依赖时下载速度慢，可以配置国内源加速。

### Debian / Ubuntu / Kali（APT 源）

```bash
# 1. 备份原 sources.list
cp /etc/apt/sources.list /etc/apt/sources.list.bak

# 2. 编辑源配置
vim /etc/apt/sources.list

# 3. 写入以下国内源地址（以中科大源为例）：

# Kali（最新稳定版）
deb https://mirrors.ustc.edu.cn/kali kali-last-snapshot main non-free non-free-firmware contrib
deb-src https://mirrors.ustc.edu.cn/kali kali-last-snapshot main non-free non-free-firmware contrib

# Debian / Ubuntu 请使用对应版本的阿里云源：
# Debian 12:   deb https://mirrors.aliyun.com/debian bookworm main contrib non-free non-free-firmware
# Ubuntu 24.04: deb https://mirrors.aliyun.com/ubuntu noble main restricted universe multiverse
# Ubuntu 22.04: deb https://mirrors.aliyun.com/ubuntu jammy main restricted universe multiverse

# 4. 更新签名密钥（Kali 如遇签名错误）
wget https://archive.kali.org/archive-keyring.gpg -O /usr/share/keyrings/kali-archive-keyring.gpg

# 5. 更新软件包列表
apt update
```

> 其他国内源（阿里云、清华、华为云等），选择其一即可。

### CentOS / RHEL 7+（YUM 源）

```bash
# 1. 备份原 YUM 源
mv /etc/yum.repos.d/CentOS-Base.repo /opt/

# 2. 下载阿里云在线源
curl -o /etc/yum.repos.d/CentOS-Base.repo https://mirrors.aliyun.com/repo/Centos-7.repo

# 注：CentOS 8/9 使用对应版本：Centos-8.repo 或 Centos-Stream-9.repo

# 3. 移除阿里云内部地址（仅保留公共地址）
sed -i -e '/mirrors.cloud.aliyuncs.com/d' -e '/mirrors.aliyuncs.com/d' /etc/yum.repos.d/CentOS-Base.repo

# 4. 生成新的 YUM 缓存
yum makecache
```

> CentOS 7 已于 2024 年 6 月 EOL（停止维护），源可能不可用。建议升级到 CentOS Stream 9 或改用 Rocky Linux / AlmaLinux。

---

## ⚠️ 不推荐：纯手动部署（仅限无 Docker 环境）

> 手动部署仅用于无 Docker 环境的特殊情况。靶场部分功能依赖 PHP 扩展 `gmp`（用于 RSA 加密），部分系统可能缺少此扩展，导致相应题目不可用。

### 环境要求

| 组件 | 说明 |
|------|------|
| PHP | >= 7.4（需 gd、gmp、fileinfo、mbstring、xml 扩展） |
| Apache / Nginx | 推荐 Apache（Nginx 需额外配置 PHP-FPM） |

### Kali Linux

Kali 基于 Debian 测试版，软件包较新，Docker 部署也是最推荐的方式：

```bash
# 先配好国内源（见上方 APT 源配置），然后：
sudo apt update
sudo apt install -y apache2 php php-gd php-gmp php-xml php-mbstring \
    gdb python3 steghide libimage-exiftool-perl sqlite3 binutils
sudo pip3 install binwalk

# 部署
sudo cp -r src/ /var/www/html/
sudo chown -R www-data:www-data /var/www/html/src
sudo systemctl enable --now apache2
```

**访问:** `http://localhost/src/`

### Debian / Ubuntu

```bash
sudo apt update
sudo apt install -y apache2 php php-gd php-gmp php-xml php-mbstring \
    gdb python3 steghide libimage-exiftool-perl sqlite3 binutils
sudo pip3 install binwalk

# 部署
sudo cp -r src/ /var/www/html/
sudo chown -R www-data:www-data /var/www/html/src
sudo systemctl enable --now apache2
```

**访问:** `http://localhost/src/`

### CentOS 7

> CentOS 7 已于 2024 年 6 月 EOL，系统 PHP 版本为 5.4，**必须**通过第三方源升级 PHP。

```bash
# 1. 安装 EPEL 和 Remi 源
sudo yum install -y epel-release
sudo yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm

# 2. 启用 Remi PHP 7.4 源
sudo yum install -y yum-utils
sudo yum-config-manager --enable remi-php74

# 3. 安装 Apache + PHP 7.4 及扩展
sudo yum install -y httpd php php-gd php-gmp php-xml php-mbstring php-fileinfo \
    gdb python3 perl-Image-ExifTool sqlite3
sudo pip3 install binwalk

# ⚠️ steghide 在 CentOS 7 仓库中不可用，隐写类题目需在容器内完成

# 4. 关闭防火墙和 SELinux（测试环境）
sudo systemctl disable --now firewalld
sudo sed -i 's/SELINUX=enforcing/SELINUX=disabled/g' /etc/selinux/config
sudo setenforce 0

# 5. 部署
sudo cp -r src/ /var/www/html/
sudo chown -R apache:apache /var/www/html/src
sudo systemctl enable --now httpd
```

**访问:** `http://localhost/src/`

### CentOS / RHEL 8+

```bash
# 安装 EPEL 和 Remi 源（PHP 8.x）
sudo dnf install -y epel-release
sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-$(rpm -E %rhel).rpm
sudo dnf module reset php -y
sudo dnf module enable php:remi-8.2 -y

# 安装依赖
sudo dnf install -y httpd php php-gd php-gmp php-xml php-mbstring \
    gdb python3 perl-Image-ExifTool sqlite3 binutils
sudo pip3 install binwalk

# SELinux 配置（如启用）
sudo setsebool -P httpd_unified 1
sudo chcon -R -t httpd_sys_content_t /var/www/html/src/flags/

# 部署
sudo cp -r src/ /var/www/html/
sudo chown -R apache:apache /var/www/html/src
sudo systemctl enable --now httpd
```

> **注意：** `steghide` 在 CentOS/RHEL 仓库中不可用，隐写类题目需在容器内完成。

**访问:** `http://localhost/src/`

### Windows（仅 XAMPP）

```powershell
# 1. 下载安装 XAMPP: https://www.apachefriends.org/
#    安装时勾选 Apache + PHP

# 2. 启用 PHP 扩展（编辑 C:\xampp\php\php.ini）
#    取消以下行的注释：
#     extension=gd
#     extension=fileinfo
#     extension=mbstring
#     ⚠️ gmp 扩展在 XAMPP 中不可用，RSA 相关题目无法正常做题

# 3. 将整个 src/ 文件夹复制到 C:\xampp\htdocs\
# 4. 启动 XAMPP Control Panel -> 点击 Apache Start
```

> **强烈建议在 Windows 上使用 Docker Desktop 而非 XAMPP。**

---

## 📁 全部题目目录

```
src/
├── sqli/              # SQL注入 🐬
├── xss/               # XSS跨站脚本
├── upload/            # 文件上传
├── rce/               # 命令注入
├── idor/              # IDOR越权访问
├── info_leak/         # 敏感信息泄露
├── weak_pass/         # 弱口令
├── ssrf/              # SSRF服务端请求伪造
├── lfi/               # 本地文件包含
├── csrf/              # CSRF跨站请求伪造
├── jwt/               # JWT令牌伪造
├── xxe/               # XXE外部实体注入
├── ssti/              # SSTI模板注入
├── regex/             # 正则表达式绕过
├── robots/            # Robots信息泄露
├── http_headers/      # HTTP请求头攻击
├── cors/              # CORS跨域漏洞
├── open_redirect/     # 开放重定向
├── php_deserialize/   # PHP反序列化
├── log4j_sim/         # Log4j漏洞模拟
├── caesar/            # ⚡ 凯撒密码
├── base64/            # ⚡ Base64编码
├── hex/               # 🛜 Hex编码 (动态Flag)
├── url/               # 🛜 URL编码 (动态Flag)
├── morse/             # 🛜 Morse码 (动态Flag)
├── rsa/               # 🛜 RSA加密 (动态Flag)
├── hash/              # 🛜 哈希破解 (动态Flag)
├── php_type/          # 🛜 PHP类型混淆 (动态Flag)
├── pwn_challenge/     # 🛜 PWN栈溢出 (动态Flag)
├── buffer_overflow/   # 🛜 Ret2Win溢出 (动态Flag)
├── fmtstr/            # 🛜 格式化字符串 (动态Flag)
├── crackme/           # CrackMe逆向
├── js_reverse/        # 🛜 JS逆向 (动态Flag)
├── mobile_apk/        # 🛜 APK反编译 (动态Flag)
├── mobile_root/       # 🛜 Root检测绕过 (动态Flag)
├── mobile_deeplink/   # 🛜 深度链接劫持 (动态Flag)
├── blockchain_solidity/      # 🛜 Solidity合约漏洞 (动态Flag)
├── blockchain_reentrancy/    # 🛜 重入攻击 (动态Flag)
├── blockchain_privatekey/    # 🛜 私钥泄露 (动态Flag)
├── stego/             # 🛜🧩 图片LSB隐写 (动态图片+Flag)
├── metadata/          # 🛜🧩 图片元数据 (动态Flag)
├── audio_stego/       # 🛜🧩 音频隐写 (动态音频+Flag)
├── file_check/        # 🛜🧩 文件类型识别 (动态Flag)
├── gif_stego/         # 🛜🧩 GIF帧隐写 (动态Flag)
├── stegsolve/         # 🛜 StegSolve通道分析 (动态Flag)
├── disk_forensics/    # 🛜 磁盘取证 (动态Flag)
├── log_analysis/      # 🛜 日志分析 (动态Flag)
├── memory_dump/       # 🛜 内存取证 (动态Flag)
├── file_carving/      # 🛜 文件雕刻 (动态Flag)
├── pcap/              # 🛜 流量分析 (动态Flag)
├── forensics/         # 🛜 数字取证 (动态Flag)
├── zip/               # 🛜 ZIP密码破解 (动态Flag)
├── qrcode/            # 🛜 社工题目 (动态Flag)
├── xor/               # (预留)
├── buffer/            # (预留)
├── pwn/               # (预留)
├── flag_helper.php    # 动态Flag管理器
└── index.php          # 首页入口
```

> 🛜 = 动态Flag | 🧩 = 动态生成资源 | 🐬 = 独立 MySQL 数据库

---

## 🖥️ 首页命令

首页终端支持以下命令：

| 命令 | 说明 |
|------|------|
| `help` | 显示帮助 |
| `ls` | 列出分类 |
| `cat [name]` | 搜索题目 |
| `progress` | 显示进度 |
| `clear` | 清屏 |
| `neofetch` | 系统信息 |
| `banner` | 显示Banner |

---

## 🐬 MySQL 数据库说明

SQL 注入题目使用独立的 **MySQL 8.0** 容器，宿主机映射端口 **3307**。

### 数据库结构（`ctf_sqli`）

```sql
-- 用户表
CREATE TABLE users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role     VARCHAR(32) NOT NULL DEFAULT 'user'
);

-- 内部数据表（注入后可继续查询）
CREATE TABLE secrets (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    name  VARCHAR(64) NOT NULL,
    value TEXT NOT NULL
);
```

### 初始化数据

| 表 | 字段 | 数据 |
|----|------|------|
| `users` | admin / CTF{w3lc0me_t0_sqli} / admin | 管理员账号 |
| `users` | guest / guest123 / user | 普通用户 |
| `secrets` | api_key / HiveYarnZinc{1nn3r_s3cr3t} | 隐藏密钥 |
| `secrets` | db_pass / ctf_mysql_s3cr3t_p@ss | 数据库凭证 |

### 使用外部客户端连接

```bash
mysql -h 127.0.0.1 -P 3307 -u ctf_user -pctf_password ctf_sqli
```

也可使用 DBeaver、Navicat 等 GUI 工具连接，方便在解题过程中直观查看数据。

### 数据持久化

MySQL 数据存储在 Docker 命名卷 `mysql_data` 中，`docker-compose down` 不会丢失数据。
如需完全重置数据库：

```bash
docker-compose down -v              # 删除数据卷
docker-compose up -d                # 重新创建
```

---

## 📄 License

MIT License
