<?php
/**
 * 用户数据管理
 * 基于 JSON 文件的轻量级用户系统
 * 路径: src/flags/users.json
 */

define('USERS_FILE', __DIR__ . '/../flags/users.json');

function loadUsers() {
    if (!file_exists(USERS_FILE)) {
        $dir = dirname(USERS_FILE);
        if (!is_dir($dir)) {
            $ok = @mkdir($dir, 0755, true);
            if (!$ok) {
                error_log('[users.php] loadUsers: 无法创建目录: ' . $dir);
                return [];
            }
        }
        $ok = @file_put_contents(USERS_FILE, json_encode([], JSON_PRETTY_PRINT));
        if ($ok === false) {
            error_log('[users.php] loadUsers: 无法写入文件: ' . USERS_FILE);
            return [];
        }
        return [];
    }
    $content = @file_get_contents(USERS_FILE);
    if ($content === false) {
        error_log('[users.php] loadUsers: 无法读取文件: ' . USERS_FILE);
        return [];
    }
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

function saveUsers($users) {
    $dir = dirname(USERS_FILE);
    if (!is_dir($dir)) {
        $ok = @mkdir($dir, 0755, true);
        if (!$ok) {
            error_log('[users.php] 无法创建目录: ' . $dir);
            return false;
        }
    }
    $ok = @file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if ($ok === false) {
        error_log('[users.php] 无法写入文件: ' . USERS_FILE);
        return false;
    }
    return true;
}

function userExists($username) {
    $users = loadUsers();
    return isset($users[$username]);
}

function registerUser($username, $password) {
    if (userExists($username)) {
        return ['success' => false, 'message' => '用户名已存在'];
    }
    // 支持学号+姓名格式，如 "2409070108王钰廷"
    // 长度放宽到 40 字符（10位学号 + 最多约15字姓名）
    $len = mb_strlen($username, 'UTF-8');
    if ($len < 2 || $len > 40) {
        return ['success' => false, 'message' => '用户名长度需在 2-40 个字符之间'];
    }
    // 允许：字母、数字、下划线、中文
    if (!preg_match('/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]+$/u', $username)) {
        return ['success' => false, 'message' => '用户名只能包含字母、数字、下划线和中文'];
    }
    if (strlen($password) < 4) {
        return ['success' => false, 'message' => '密码长度不能少于 4 位'];
    }
    $users = loadUsers();
    $users[$username] = [
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'created_at' => date('Y-m-d H:i:s')
    ];
    $ok = saveUsers($users);
    if (!$ok) {
        return ['success' => false, 'message' => '服务器错误：无法保存用户数据，请检查 flags 目录权限'];
    }
    return ['success' => true, 'message' => '注册成功'];
}

function verifyUser($username, $password) {
    $users = loadUsers();
    if (!isset($users[$username])) {
        return false;
    }
    return password_verify($password, $users[$username]['password']);
}
