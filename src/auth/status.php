<?php
/**
 * 登录状态查询接口
 * GET /auth/status.php
 * Return: { "loggedIn": bool, "username": "..." }
 */
header('Content-Type: application/json; charset=utf-8');

session_start();
$username = $_SESSION['ctf_username'] ?? null;

if ($username) {
    echo json_encode(['loggedIn' => true, 'username' => $username]);
} else {
    echo json_encode(['loggedIn' => false, 'username' => null]);
}
