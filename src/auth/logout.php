<?php
/**
 * 登出接口
 * POST /auth/logout.php
 * Return: { "success": true }
 */
header('Content-Type: application/json; charset=utf-8');

session_start();
$_SESSION['ctf_username'] = null;
session_destroy();

echo json_encode(['success' => true, 'message' => '已登出']);
