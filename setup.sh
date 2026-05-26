#!/bin/bash

echo "=========================================="
echo "  HiveYarnZinc CTF靶场部署脚本"
echo "=========================================="
echo ""

# 检查Docker
if ! command -v docker &> /dev/null; then
    echo "错误: 未安装Docker"
    exit 1
fi

# 创建数据目录（确保宿主机目录存在且权限正确）
mkdir -p progress_data flags_data
echo "✅ 数据目录已准备 (progress_data/, flags_data/)"
echo ""

# 智能选择部署方式
if [ -f "images/ctf-platform.tar" ] && [ -f "images/ctf-progress.tar" ] && [ -f "images/mysql-8.0.tar" ]; then
    echo "检测到预构建镜像，使用方式一（快速部署）..."
    echo ""
    
    echo "[1/4] 导入Docker镜像..."
    docker load -i images/ctf-platform.tar
    docker load -i images/ctf-progress.tar
    docker load -i images/mysql-8.0.tar
    
    echo "[2/4] 启动容器..."
    docker-compose up -d
    
    echo "[3/4] 等待服务启动..."
    sleep 3
    
    echo "[4/4] 检查服务状态..."
    docker-compose ps
    
    echo ""
    echo "=========================================="
    echo "  🎉 HiveYarnZinc 已成功启动!"
    echo "=========================================="
    
else
    echo "未检测到预构建镜像，使用方式二（本地构建）..."
    echo "注意: 这可能需要 10-30 分钟，请耐心等待"
    echo ""
    
    echo "[1/5] 拉取 MySQL 8.0 镜像..."
    docker pull mysql:8.0
    
    echo "[2/5] 构建Docker镜像..."
    docker-compose build
    
    echo "[3/5] 启动容器..."
    docker-compose up -d
    
    echo "[4/5] 等待服务启动..."
    sleep 3
    
    echo "[5/5] 检查服务状态..."
    docker-compose ps
    
    echo ""
    echo "=========================================="
    echo "  🎉 HiveYarnZinc 已成功启动!"
    echo "=========================================="
    
fi

echo ""
echo "  Web题目: http://localhost:8080"
echo "  进度API: http://localhost:3001"
echo "  MySQL:   localhost:3307  (ctf_user / ctf_password)"
echo ""
echo "  停止服务: docker-compose down"
echo "  重置MySQL: docker-compose down -v"
echo "  查看日志: docker-compose logs -f"
echo ""
