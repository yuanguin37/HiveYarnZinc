<?php
/**
 * 登录接口
 * POST /auth/login.php
 * Body: { "username": "...", "password": "..." }
 * Return: { "success": bool, "message": "..." }
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/users.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '仅支持 POST 请求']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$username = trim($body['username'] ?? '');
$password = $body['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => '用户名和密码不能为空']);
    exit;
}

if (verifyUser($username, $password)) {
    session_start();
    $_SESSION['ctf_username'] = $username;
    echo json_encode(['success' => true, 'message' => '登录成功']);
} else {
    echo json_encode(['success' => false, 'message' => '用户名或密码错误']);
}
