<?php
/**
 * 注册接口
 * POST /auth/register.php
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

$result = registerUser($username, $password);

if ($result['success']) {
    // 注册成功后自动登录
    session_start();
    $_SESSION['ctf_username'] = $username;
}

echo json_encode($result);
