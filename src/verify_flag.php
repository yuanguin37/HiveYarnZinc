<?php
/**
 * Flag 验证接口（支持动态 + 静态 Flag）
 * 接收题目ID和flag，验证是否正确
 * 
 * POST 参数：
 * - challenge: 题目ID（如 sqli, xss）
 * - flag: 用户提交的flag
 * 
 * 返回 JSON：
 * - success: bool
 * - message: string（错误信息）
 * - challengeId: string
 */

header('Content-Type: application/json; charset=utf-8');

// 允许跨域（进度API和Web服务在不同端口）
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 启动会话，确保 flag_helper 能获取登录用户
session_start();

// ========== 静态Flag定义（适用于不使用flag_helper.php的题目） ==========
$STATIC_FLAGS = [
    // Web - SQL注入（静态）
    'sqli' => 'HiveYarnZinc{sql_injection_123}',
    
    // Web - XSS（静态）
    'xss' => 'HiveYarnZinc{xss_stored_cookie}',
    
    // Web - 文件上传（静态）
    'upload' => 'HiveYarnZinc{upload_pwn_shell}',
    
    // Web - RCE（静态）
    'rce' => 'HiveYarnZinc{rce_cat_flag}',
    
    // Web - 信息泄露（静态）
    'info_leak' => 'HiveYarnZinc{info_leak_admin}',
    
    // 密码学 - Base64（静态）
    'base64' => 'HiveYarnZinc{base64_easy_flag}',
    
    // 密码学 - Caesar（静态）
    'caesar' => 'HiveYarnZinc{caesar_cipher_broken}',
    
    // 密码学 - XOR（静态）
    'xor' => 'HiveYarnZinc{xor_cipher_ftw}',
    
    // 杂项 - 弱密码（静态）
    'weak_pass' => 'HiveYarnZinc{weak_password}',
    
    // 杂项 - IDOR（静态）
    'idor' => 'HiveYarnZinc{idor_user_2_flag}',
];

// ========== 动态Flag题目列表（使用 flag_helper.php） ==========
// 这些题目在各自的 index.php 中 require_once('../flag_helper.php')
$dynamicChallenges = [
    'audio_stego', 'blockchain_privatekey', 'blockchain_reentrancy',
    'blockchain_solidity', 'buffer_overflow', 'cors', 'csrf',
    'disk_forensics', 'file_carving', 'file_check', 'fmtstr',
    'forensics', 'gif_stego', 'hash', 'hex', 'http_headers',
    'jwt', 'lfi', 'log_analysis', 'log4j_sim', 'js_reverse',
    'memory_dump', 'metadata', 'mobile_apk', 'mobile_deeplink',
    'mobile_root', 'morse', 'open_redirect', 'pcap',
    'php_deserialize', 'php_type', 'pwn_challenge', 'qrcode',
    'regex', 'robots', 'rsa', 'ssrf', 'ssti', 'stego',
    'stegsolve', 'url', 'xxe', 'zip'
];

// ========== 验证逻辑 ==========

$challengeId = $_POST['challenge'] ?? '';
$flag = $_POST['flag'] ?? '';

// 检查参数
if (empty($challengeId) || empty($flag)) {
    echo json_encode([
        'success' => false,
        'message' => '题目ID和Flag都不能为空'
    ]);
    exit;
}

// 判断是动态Flag还是静态Flag
if (in_array($challengeId, $dynamicChallenges)) {
    // ===== 动态Flag验证 =====
    // 引入 flag_helper.php
    $flagHelperPath = __DIR__ . '/flag_helper.php';
    if (!file_exists($flagHelperPath)) {
        echo json_encode([
            'success' => false,
            'message' => '动态Flag系统未找到'
        ]);
        exit;
    }
    
    require_once $flagHelperPath;
    
    // 调用 verifyFlag() 函数（定义在 flag_helper.php 末尾）
    $result = verifyFlag($challengeId, $flag);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => '🎉 Flag正确！进度已更新',
            'challengeId' => $challengeId
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '❌ Flag错误，请重试'
        ]);
    }
    
} else if (isset($STATIC_FLAGS[$challengeId])) {
    // ===== 静态Flag验证 =====
    if (hash_equals($STATIC_FLAGS[$challengeId], $flag)) {
        echo json_encode([
            'success' => true,
            'message' => '🎉 Flag正确！进度已更新',
            'challengeId' => $challengeId
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '❌ Flag错误，请重试'
        ]);
    }
    
} else {
    // 题目未定义
    echo json_encode([
        'success' => false,
        'message' => '题目不存在或Flag未配置'
    ]);
}
