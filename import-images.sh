#!/bin/bash

echo "=========================================="
echo "  HiveYarnZinc 镜像导入脚本"
echo "=========================================="
echo ""

# 检查Docker
if ! command -v docker &> /dev/null; then
    echo "错误: 未安装Docker"
    exit 1
fi

# 检查镜像文件
MISSING=0
for img in ctf-platform.tar ctf-progress.tar mysql-8.0.tar; do
    if [ ! -f "images/$img" ]; then
        echo "  ❌ 缺少 images/$img"
        MISSING=1
    fi
done

if [ $MISSING -eq 1 ]; then
    echo ""
    echo "请确保 images/ 目录下有以下文件:"
    echo "  - ctf-platform.tar"
    echo "  - ctf-progress.tar"
    echo "  - mysql-8.0.tar"
    echo ""
    echo "或者使用压缩包解压:"
    echo "  tar -xzf images/hiveyarnzinc-images.tar.gz -C images/"
    exit 1
fi

echo "[1/3] 导入 ctf-platform 镜像..."
docker load -i images/ctf-platform.tar

echo "[2/3] 导入 ctf-progress 镜像..."
docker load -i images/ctf-progress.tar

echo "[3/3] 导入 mysql:8.0 镜像..."
docker load -i images/mysql-8.0.tar

# 恢复运行时数据（如果存在备份）
if [ -d "images/progress_data_backup" ]; then
    echo ""
    echo "[恢复] 检测到进度数据备份，正在恢复..."
    rm -rf progress_data
    cp -r images/progress_data_backup progress_data
    echo "  ✅ 已恢复 progress_data/"
fi
if [ -d "images/flags_backup" ]; then
    echo "[恢复] 检测到用户数据备份，正在恢复..."
    rm -rf flags_data
    cp -r images/flags_backup flags_data
    echo "  ✅ 已恢复 flags_data/ (用户账号 + 动态Flag)"
fi

echo ""
echo "=========================================="
echo "  ✅ 镜像导入完成!"
echo "=========================================="
echo ""
echo "  启动服务: docker-compose up -d"
echo "  Web题目: http://localhost:8080"
echo "  MySQL:  localhost:3307"
echo ""
echo "  ⚠️  首次启动后，所有用户需要重新注册"
echo "  如需保留原有用户数据，请确保导入了 flags_backup/"
echo ""
