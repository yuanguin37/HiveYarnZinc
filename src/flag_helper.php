<?php
/**
 * 动态Flag生成与管理工具
 * 用于生成和验证CTF题目的动态Flag
 * 
 * 修改：使用浏览器指纹而非session，这样verify_flag.php也能验证
 */

class FlagManager {
    private $flagsDir = '/var/www/html/flags';
    private $flagPrefix = 'HiveYarnZinc{';
    private $flagSuffix = '}';
    
    public function __construct() {
        if (!is_dir($this->flagsDir)) {
            @mkdir($this->flagsDir, 0755, true);
        }
        // 确保session已启动（用于获取用户名生成动态Flag）
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // 注意：不在构造函数中强制重定向，避免API场景返回HTML
        // 未登录时 getUserKey() 会回退到浏览器指纹
    }
    
    /**
     * 为指定题目生成动态Flag
     * @param string $challenge 题目名称
     * @param string $seed 可选的种子值，用于生成确定性flag
     * @return string 生成的flag
     */
    public function generateFlag($challenge, $seed = null) {
        $userKey = $this->getUserKey();
        $key = "{$challenge}_{$userKey}";
        
        // 检查是否已存在flag
        $existingFlag = $this->loadFlag($key);
        if ($existingFlag !== null) {
            return $existingFlag;
        }
        
        // 生成新的flag
        if ($seed === null) {
            $seed = $userKey . $challenge . time();
        }
        
        $hash = substr(md5($seed), 0, 8);
        $flag = $this->flagPrefix . $challenge . '_' . $hash . $this->flagSuffix;
        
        // 保存flag
        $this->saveFlag($key, $flag);
        
        return $flag;
    }
    
    /**
     * 验证flag是否正确
     * @param string $challenge 题目名称
     * @param string $userFlag 用户提交的flag
     * @return bool
     */
    public function verifyFlag($challenge, $userFlag) {
        $userKey = $this->getUserKey();
        $key = "{$challenge}_{$userKey}";
        
        $correctFlag = $this->loadFlag($key);
        
        if ($correctFlag === null) {
            // 如果flag不存在，生成一个
            $correctFlag = $this->generateFlag($challenge);
        }
        
        return hash_equals($correctFlag, $userFlag);
    }
    
    /**
     * 获取当前题目对应的flag（用于嵌入到题目中）
     * @param string $challenge 题目名称
     * @return string
     */
    public function getFlag($challenge) {
        return $this->generateFlag($challenge);
    }
    
    /**
     * 清除指定题目的flag（用于重置）
     * @param string $challenge 题目名称
     */
    public function resetFlag($challenge) {
        $userKey = $this->getUserKey();
        $key = "{$challenge}_{$userKey}";
        $filepath = $this->flagsDir . '/' . md5($key) . '.txt';
        
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
    
    /**
     * 获取用户唯一标识（优先使用登录用户名，回退到浏览器指纹）
     * 登录用户：基于用户名生成稳定标识，不同用户 Flag 不同
     * 未登录：基于浏览器指纹（向后兼容）
     */
    private function getUserKey() {
        // 优先使用登录用户名
        if (session_status() !== PHP_SESSION_NONE && isset($_SESSION['ctf_username'])) {
            $username = $_SESSION['ctf_username'];
            return 'user_' . md5($username);
        }
        
        // 回退：使用session中的旧key
        if (session_status() !== PHP_SESSION_NONE && isset($_SESSION['ctf_user_key'])) {
            return $_SESSION['ctf_user_key'];
        }
        
        // 基于浏览器指纹生成唯一标识（向后兼容）
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $encoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
        $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $fingerprint = $userAgent . $accept . $encoding . $language . $remoteAddr;
        $userKey = md5($fingerprint);
        
        if (session_status() !== PHP_SESSION_NONE) {
            $_SESSION['ctf_user_key'] = $userKey;
        }
        
        return $userKey;
    }
    
    /**
     * 保存flag到文件
     */
    private function saveFlag($key, $flag) {
        $filepath = $this->flagsDir . '/' . md5($key) . '.txt';
        file_put_contents($filepath, $flag);
    }
    
    /**
     * 从文件加载flag
     */
    private function loadFlag($key) {
        $filepath = $this->flagsDir . '/' . md5($key) . '.txt';
        
        if (file_exists($filepath)) {
            return trim(file_get_contents($filepath));
        }
        
        return null;
    }
    
    /**
     * 生成十六进制表示的flag（用于隐写等操作）
     */
    public function getFlagHex($challenge) {
        $flag = $this->getFlag($challenge);
        return bin2hex($flag);
    }
    
    /**
     * 生成二进制表示的flag（用于LSB隐写）
     */
    public function getFlagBinary($challenge) {
        $flag = $this->getFlag($challenge);
        $binary = '';
        for ($i = 0; $i < strlen($flag); $i++) {
            $binary .= str_pad(decbin(ord($flag[$i])), 8, '0', STR_PAD_LEFT);
        }
        return $binary;
    }
}

// 全局函数简化调用
function getFlagManager() {
    static $instance = null;
    if ($instance === null) {
        $instance = new FlagManager();
    }
    return $instance;
}

function generateFlag($challenge) {
    return getFlagManager()->generateFlag($challenge);
}

function verifyFlag($challenge, $userFlag) {
    return getFlagManager()->verifyFlag($challenge, $userFlag);
}
?>
