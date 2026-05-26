@echo off
echo ==========================================
echo   HiveYarnZinc 镜像导入脚本
echo ==========================================
echo.

REM 检查Docker
docker --version >nul 2>&1
if errorlevel 1 (
    echo 错误: 未安装Docker
    pause
    exit /b 1
)

REM 检查镜像文件
set MISSING=0
if not exist "images\ctf-platform.tar" (
    echo   ❌ 缺少 images\ctf-platform.tar
    set MISSING=1
)
if not exist "images\ctf-progress.tar" (
    echo   ❌ 缺少 images\ctf-progress.tar
    set MISSING=1
)
if not exist "images\mysql-8.0.tar" (
    echo   ❌ 缺少 images\mysql-8.0.tar
    set MISSING=1
)
if %MISSING%==1 (
    echo.
    echo 请确保 images\ 目录下有以下文件:
    echo   - ctf-platform.tar
    echo   - ctf-progress.tar
    echo   - mysql-8.0.tar
    echo.
    echo 或者使用压缩包解压:
    echo   tar -xzf images\hiveyarnzinc-images.tar.gz -C images\
    pause
    exit /b 1
)

echo [1/3] 导入 ctf-platform 镜像...
docker load -i images\ctf-platform.tar

echo [2/3] 导入 ctf-progress 镜像...
docker load -i images\ctf-progress.tar

echo [3/3] 导入 mysql:8.0 镜像...
docker load -i images\mysql-8.0.tar

REM 恢复运行时数据（如果存在备份）
if exist "images\progress_data_backup" (
    echo.
    echo [恢复] 检测到进度数据备份，正在恢复...
    if exist "progress_data" rmdir /s /q progress_data
    xcopy /s /i /q "images\progress_data_backup" "progress_data" >nul
    echo   ✅ 已恢复 progress_data\
)
if exist "images\flags_backup" (
    echo [恢复] 检测到用户数据备份，正在恢复...
    if exist "flags_data" rmdir /s /q flags_data
    xcopy /s /i /q "images\flags_backup" "flags_data" >nul
    echo   ✅ 已恢复 flags_data\ (用户账号 + 动态Flag)
)

echo.
echo ==========================================
echo   ✅ 镜像导入完成!
echo ==========================================
echo.
echo   启动服务: docker-compose up -d
echo   Web题目: http://localhost:8080
echo   MySQL:  localhost:3307
echo.
echo   ⚠️  首次启动后，所有用户需要重新注册
echo   如需保留原有用户数据，请确保导入了 flags_backup\
echo.
pause
