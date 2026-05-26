<?php
/**
 * 登录检查（针对不包含 flag_helper 或 progress_helper 的页面）
 * 未登录用户重定向到首页
 * 在页面最顶部引入：<?php require_once '../auth_check.php'; ?>
 */
session_start();
if (empty($_SESSION['ctf_username'])) {
    header('Location: /');
    exit;
}
