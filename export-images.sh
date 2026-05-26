#!/bin/bash

echo "=========================================="
echo "  HiveYarnZinc 镜像导出脚本"
echo "=========================================="
echo ""

# 检查Docker
if ! command -v docker &> /dev/null; then
    echo "错误: 未安装Docker"
    exit 1
fi

# 创建镜像目录
mkdir -p images

echo "[1/5] 拉取 MySQL 8.0 公共镜像..."
docker pull mysql:8.0

echo "[2/5] 构建自定义镜像..."
docker-compose build --no-cache ctf-platform ctf-progress

echo "[3/5] 导出镜像为tar文件..."
docker save -o images/ctf-platform.tar     hiveyarnzinc/ctf-platform:latest
docker save -o images/ctf-progress.tar      hiveyarnzinc/ctf-progress:latest
docker save -o images/mysql-8.0.tar         mysql:8.0

echo "[4/5] 压缩镜像文件..."
tar -czf images/hiveyarnzinc-images.tar.gz \
    -C images ctf-platform.tar ctf-progress.tar mysql-8.0.tar

echo "[5/5] 备份运行时数据..."
# 用户数据和进度数据存储在宿主机挂载卷中，需要单独备份
if [ -d "progress_data" ]; then
    cp -r progress_data images/progress_data_backup
    echo "  ✅ 已备份 progress_data/"
fi
if [ -d "flags_data" ]; then
    cp -r flags_data images/flags_backup
    echo "  ✅ 已备份 flags_data/ (用户账号 + 动态Flag)"
fi

echo ""
echo "=========================================="
echo "  ✅ 镜像导出完成!"
echo "=========================================="
echo ""
echo "  导出的文件:"
echo "  - images/ctf-platform.tar      (PHP平台 + 题目源码)"
echo "  - images/ctf-progress.tar       (进度服务)"
echo "  - images/mysql-8.0.tar          (MySQL数据库)"
echo "  - images/hiveyarnzinc-images.tar.gz (总压缩包)"
echo ""
echo "  ⚠️  注意: 运行时数据需单独备份"
echo "  - images/progress_data_backup/  (用户做题进度)"
echo "  - images/flags_backup/          (用户账号 + 动态Flag缓存)"
echo ""
echo "  将 images/ 目录分发给用户，使用 import-images.sh 导入"
echo ""
