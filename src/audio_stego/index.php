<?php
require_once('../flag_helper.php');

$challengeName = 'audio_stego';
$flagManager = getFlagManager();
$flag = $flagManager->getFlag($challengeName);
$message = "";

// 动态生成音频文件（带隐写）
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    // 安全清除所有输出缓冲区
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // 生成一个简单的WAV文件，带有隐写数据
    $sampleRate = 8000; // 降低采样率到8kHz（人耳仍可听）
    $duration = 2; // 2秒（减少处理时间）
    $numSamples = $sampleRate * $duration;
    
    // 设置HTTP头（必须在任何输出之前）
    header('Content-Type: audio/wav');
    header('Content-Disposition: attachment; filename="mystery_audio.wav"');
    
    // WAV文件头
    echo 'RIFF';
    echo pack('V', 36 + $numSamples * 2); // 文件大小-8
    echo 'WAVE';
    echo 'fmt ';
    echo pack('V', 16); // fmt chunk大小
    echo pack('v', 1); // PCM格式
    echo pack('v', 1); // 单声道
    echo pack('V', $sampleRate); // 采样率
    echo pack('V', $sampleRate * 2); // 字节率
    echo pack('v', 2); // 块对齐
    echo pack('v', 16); // 位深
    echo 'data';
    echo pack('V', $numSamples * 2); // 数据大小
    
    // 将flag转换为二进制
    $flagBinary = '';
    for ($i = 0; $i < strlen($flag); $i++) {
        $flagBinary .= str_pad(decbin(ord($flag[$i])), 8, '0', STR_PAD_LEFT);
    }
    $flagBinary .= '00000000'; // 结束标记
    
    $bitIndex = 0;
    $flagLen = strlen($flagBinary);
    
    // 生成音频数据并直接输出（避免内存溢出）
    for ($i = 0; $i < $numSamples; $i++) {
        // 生成基础正弦波 (440Hz A调)
        $t = $i / $sampleRate;
        $sampleInt = intval(sin(2 * M_PI * 440 * $t) * 16000);
        
        // 在前N个采样点嵌入flag (LSB隐写)
        if ($bitIndex < $flagLen && $i < $numSamples / 10) {
            $bit = (int)$flagBinary[$bitIndex];
            $sampleInt = ($sampleInt & 0xFFFE) | $bit;
            $bitIndex++;
        }
        
        // 添加一些随机噪声
        $sampleInt += rand(-1000, 1000);
        
        // 限制范围
        $sampleInt = max(-32768, min(32767, $sampleInt));
        
        echo pack('v', $sampleInt);
    }
    exit();
}

// 处理提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answer = trim($_POST['answer'] ?? '');
    if ($flagManager->verifyFlag($challengeName, $answer)) {
        $message = "success";
    } else {
        $message = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>音频隐写 - HiveYarnZinc</title>
        <?php require_once '../progress_helper.php'; ?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: #0a0a0f;
            color: #00ff00;
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; padding: 30px; border-bottom: 2px solid #00ff00; }
        .header h1 { font-size: 2.5em; color: #00ff00; text-shadow: 0 0 20px #00ff00; }
        .back-link { color: #00ffff; text-decoration: none; font-size: 1.1em; }
        
        .info-box {
            background: rgba(0, 255, 0, 0.05);
            border: 1px solid #00ff00;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .info-box h3 { color: #00ff00; margin-bottom: 15px; }
        .info-box p { color: #aaa; line-height: 1.8; }
        .info-box code { background: #000; padding: 2px 8px; border-radius: 3px; color: #ff0; }
        
        .download-section {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #00ff00;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
        }
        .download-btn {
            display: inline-block;
            padding: 20px 50px;
            background: linear-gradient(135deg, #00ff00, #00aa00);
            color: #000;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.3em;
            font-weight: bold;
            transition: all 0.3s;
            font-family: 'Courier New', monospace;
        }
        .download-btn:hover {
            background: linear-gradient(135deg, #00ffff, #00ff00);
            box-shadow: 0 0 30px rgba(0, 255, 255, 0.5);
        }
        
        audio { width: 100%; margin-top: 20px; }
        
        .tools-box {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #00ff00;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .tools-box h3 { color: #00ff00; margin-bottom: 20px; }
        .tool-item {
            background: rgba(0, 255, 0, 0.05);
            border: 1px solid #00ff00;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .tool-item strong { color: #ff0; }
        .tool-item code { color: #0ff; background: #000; padding: 3px 6px; border-radius: 3px; display: block; margin-top: 5px; }
        
        .result-box {
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        .result-box.success {
            background: rgba(0, 255, 0, 0.1);
            border: 2px solid #00ff00;
            color: #00ff00;
        }
        .result-box.error {
            background: rgba(255, 0, 0, 0.1);
            border: 2px solid #ff0000;
            color: #ff0000;
        }
        
        .submit-box {
            background: rgba(0, 0, 0, 0.8);
            border: 2px dashed #00ff00;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
        }
        .submit-box input {
            width: 100%;
            max-width: 400px;
            padding: 15px;
            background: #000;
            border: 1px solid #00ff00;
            color: #00ff00;
            font-size: 1.2em;
            border-radius: 5px;
            text-align: center;
            font-family: 'Courier New', monospace;
        }
        .btn {
            padding: 15px 40px;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #ff0, #ff8800);
            color: #000;
            margin-top: 20px;
        }
        
        .hint-box {
            background: rgba(0, 255, 0, 0.1);
            border: 2px solid #00ff00;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .hint-box h3 { color: #00ff00; margin-bottom: 15px; }
        .hint-box pre {
            background: #000;
            padding: 15px;
            border-radius: 5px;
            color: #ff0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎵 音频隐写</h1>
            <a href="../index.php" class="back-link">[ ← 返回首页 ]</a>
        </div>
        
        <div class="info-box">
            <h3>💡 题目描述</h3>
            <p>这段音频听起来像是普通的音乐/音效，但其中可能隐藏着一些信息。<br>
            音频隐写是一种将数据嵌入到音频波形中的技术。<br><br>
            <strong>目标:</strong> 分析音频文件，提取隐藏在其中的Flag<br>
            <span style="color:#ff0;">提示:</span> 音频数据中可能使用了LSB隐写技术</p>
        </div>
        
        <div class="download-section">
            <h3 style="color:#00ff00; margin-bottom:20px;">📥 下载音频文件</h3>
            <p style="color:#aaa; margin-bottom:25px;">下载这段神秘音频并分析其隐藏数据</p>
            <a href="?action=download" class="download-btn">[ 🔊 下载 mystery_audio.wav ]</a>
            <p style="color:#666; margin-top:15px; font-size:0.9em;">格式: WAV | 采样率: 44100Hz | 单声道</p>
        </div>
        
        <div class="tools-box">
            <h3>🛠️ 音频分析工具</h3>
            <div class="tool-item">
                <strong>audacity</strong>
                <code>使用Audacity打开并查看波形图</code>
                <span>可视化音频波形</span>
            </div>
            <div class="tool-item">
                <strong>hexdump / xxd</strong>
                <code>xxd mystery_audio.wav | head -50</code>
                <span>查看音频文件原始数据</span>
            </div>
            <div class="tool-item">
                <strong>sox</strong>
                <code>sox mystery_audio.wav -n stat</code>
                <span>显示音频统计信息</span>
            </div>
            <div class="tool-item">
                <strong>Python 脚本</strong>
                <code>使用wave模块读取并提取LSB</code>
                <span>自定义提取隐写数据</span>
            </div>
        </div>
        
        <div class="hint-box">
            <h3>💡 Python LSB 提取脚本参考</h3>
            <pre>import wave

with wave.open('mystery_audio.wav', 'rb') as wav:
    frames = wav.readframes(wav.getnframes())
    
    # 提取每个采样点的最低位
    binary = ""
    for i in range(0, min(len(frames), 88200)):  # 前1秒数据
        if i % 2 == 1:  # 16位采样，提取低字节
            sample = frames[i]
            binary += str(ord(sample) & 1)
    
    # 转换为字符串
    flag = ""
    for i in range(0, len(binary), 8):
        byte = binary[i:i+8]
        if byte == '00000000':
            break
        flag += chr(int(byte, 2))
    
    print("Hidden Flag:", flag)</pre>
        </div>
        
        <?php if ($message === 'success'): ?>
        <div class="result-box success">
            <h2>🎉 恭喜！</h2>
            <p style="font-size:1.3em; margin:15px 0;">Flag正确！你成功从音频中提取了隐藏信息。</p>
        </div>
        <?php elseif ($message === 'error'): ?>
        <div class="result-box error">
            <h2>❌ 错误</h2>
            <p>Flag不正确，请继续分析音频！</p>
        </div>
        <?php endif; ?>
        
        <div class="submit-box">
            <h3 style="color:#00ff00; margin-bottom:20px;">📝 提交Flag</h3>
            <form method="POST">
                <input type="text" name="answer" placeholder="HiveYarnZinc{...}">
                <button type="submit" class="btn">提交</button>
            </form>
        </div>
    </div>
</body>
</html>
