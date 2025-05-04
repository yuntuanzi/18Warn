<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: -1');

// 设置PHP版本要求
if (version_compare(PHP_VERSION, '8.1.0') < 0) {
    die('嘿，你需要安装PHP 8.1或更高版本来运行');
}

// 检查访问权限
if (!isset($_COOKIE['age_verified']) || $_COOKIE['age_verified'] !== 'true') {
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>访问受限</title>
        <style>
            body {
                font-family: 'PingFang SC', 'Helvetica Neue', Arial, sans-serif;
                background-color: #1e272e;
                color: #f1f2f6;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                text-align: center;
                line-height: 1.6;
            }
            .access-denied {
                background-color: #2f3640;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
                max-width: 600px;
                width: 90%;
                border: 1px solid #353b48;
            }
            h1 {
                color: #ff4757;
                margin-bottom: 20px;
            }
            .progress-container {
                width: 100%;
                height: 10px;
                background: #353b48;
                border-radius: 5px;
                margin: 30px 0;
                overflow: hidden;
            }
            .progress-bar {
                height: 100%;
                width: 0;
                background: linear-gradient(90deg, #ff4757, #e84118);
                border-radius: 5px;
                animation: progress 1.5s linear forwards;
            }
            @keyframes progress {
                to { width: 100%; }
            }
            .icon {
                font-size: 3rem;
                color: #ff4757;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="access-denied">
            <div class="icon">⛔</div>
            <h1>访问受限</h1>
            <p>您没有访问此页面的权限，正在重定向到年龄验证页面...</p>
            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>
        </div>
        <script>
            setTimeout(() => {
                window.location.href = "index.html";
            }, 1500);
        </script>
    </body>
    </html>
    <?php
    exit;
}

// 定义文件路径
$counterFile = __DIR__ . '/counter.txt';
$rankingsFile = __DIR__ . '/rankings.json';
$messagesFile = __DIR__ . '/messages.json';
$errorLog = __DIR__ . '/error.log';

// 初始化计数器
if (!file_exists($counterFile)) {
    if (!file_put_contents($counterFile, '0')) {
        error_log("无法创建 counter.txt: " . date('Y-m-d H:i:s'), 3, $errorLog);
    }
}

// 增加计数器
$countData = file_get_contents($counterFile);
if ($countData === false) {
    error_log("无法读取 counter.txt: " . date('Y-m-d H:i:s'), 3, $errorLog);
    $count = 0;
} else {
    $count = (int)$countData;
}
$count++;
if (!file_put_contents($counterFile, (string)$count)) {
    error_log("无法写入 counter.txt: " . date('Y-m-d H:i:s'), 3, $errorLog);
}

// 获取客户端信息
$ip = $_SERVER['REMOTE_ADDR'];
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip .= ' (代理: ' . $_SERVER['HTTP_X_FORWARDED_FOR'] . ')';
}
$browser = $_SERVER['HTTP_USER_AGENT'] ?? '未知';
$date = date('Y-m-d H:i:s');

// 获取城市信息
$city = '未知';
$cityMap = [
    'Taipei' => '台北',
    'Los Angeles' => '洛杉矶',
    'New York' => '纽约',
    'London' => '伦敦',
    'Tokyo' => '东京',
    'Beijing' => '北京',
    'Shanghai' => '上海'
];
try {
    $geoApiUrl = "http://ip-api.com/json/{$_SERVER['REMOTE_ADDR']}?fields=city";
    $geoData = @file_get_contents($geoApiUrl);
    if ($geoData) {
        $geoJson = json_decode($geoData, true);
        $city = $geoJson['city'] ?? '未知';
        $city = $cityMap[$city] ?? $city;
    }
} catch (Exception $e) {
    error_log("GeoIP API 错误: " . $e->getMessage() . " - " . date('Y-m-d H:i:s'), 3, $errorLog);
}

// 更新排行榜
$rankings = [];
if (file_exists($rankingsFile)) {
    $rankings = json_decode(file_get_contents($rankingsFile), true) ?? [];
}
$rankings[$city] = ($rankings[$city] ?? 0) + 1;
file_put_contents($rankingsFile, json_encode($rankings, JSON_PRETTY_PRINT), LOCK_EX);

// 按访问次数排序并取前3
arsort($rankings);
$topRankings = array_slice($rankings, 0, 3);

// 读取留言
$messages = [];
if (file_exists($messagesFile)) {
    $messages = json_decode(file_get_contents($messagesFile), true) ?? [];
}
$messages = array_slice($messages, 0, 5);

// 分享链接（用于复制到剪贴板）
$shareUrl = 'https://me.bbb-lsy07.sbs/fun-18-APP/?t=' . time();
// QR码固定URL
$qrCodeUrl = 'https://me.bbb-lsy07.sbs/fun-18-APP';
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="一个有趣的年龄验证恶作剧网站，留下你的被骗感言，与好友分享！">
    <meta name="keywords" content="年龄验证, 恶作剧, 留言板, 趣味网站">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>恭喜被骗 - 趣味18 APP</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>
    <style>
        :root {
            --primary: #6e45e2;
            --secondary: #88d3ce;
            --dark: #1a1a2e;
            --darker: #16213e;
            --light: #f1f1f1;
            --danger: #e94560;
            --success: #2ecc71;
        }
        [data-theme="dark"] {
            --bg-color: var(--dark);
            --text-color: var(--light);
            --card-bg: var(--darker);
            --highlight: var(--primary);
        }
        [data-theme="light"] {
            --bg-color: #f5f5f5;
            --text-color: #333;
            --card-bg: #fff;
            --highlight: #4a6bff;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'PingFang SC', 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            width: 100%;
        }
        header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }
        h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            position: relative;
            display: inline-block;
            font-weight: bold;
        }
        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 2px;
        }
        .counter-card {
            background-color: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .counter-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }
        .counter-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        .counter-number {
            font-size: 5rem;
            font-weight: 700;
            text-align: center;
            margin: 1rem 0;
            color: var(--highlight);
        }
        .debug-info {
            font-size: 0.9rem;
            color: var(--secondary);
            text-align: center;
            margin-top: 1rem;
        }
        .info-card, .leaderboard, .message-board {
            background-color: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .info-item {
            margin-bottom: 1rem;
        }
        .info-label {
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 0.5rem;
            display: block;
        }
        .info-value {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 0.8rem;
            border-radius: 8px;
            word-break: break-all;
            font-family: monospace;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem 1.5rem;
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin: 0 0.5rem;
            min-width: 160px;
        }
        .btn-download {
            background: linear-gradient(135deg, #6e45e2, #88d3ce);
        }
        .btn-download:hover {
            box-shadow: 0 6px 20px rgba(110, 69, 226, 0.6);
        }
        .btn-share {
            background: linear-gradient(135deg, #ff6b6b, #ffa3a3);
        }
        .btn-share:hover {
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.6);
        }
        .btn svg {
            margin-right: 8px;
            width: 1.2rem;
            height: 1.2rem;
        }
        .btn-container {
            text-align: center;
            margin-top: 2rem;
        }
        .theme-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 100;
            border: none;
        }
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        .particle {
            position: absolute;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            opacity: 0.5;
            animation: float 15s infinite linear;
            will-change: transform, opacity;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            position: relative;
        }
        .modal-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--highlight);
        }
        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .modal-btn {
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 140px;
            color: white;
            border: none;
        }
        .modal-btn svg {
            margin-right: 8px;
            width: 1.2rem;
            height: 1.2rem;
        }
        .modal-btn-wechat {
            background: linear-gradient(135deg, #07C160, #09ae85);
        }
        .modal-btn-qq {
            background: linear-gradient(135deg, #12B7F5, #0081ff);
        }
        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-color);
        }
        .qrcode-container {
            margin: 1rem auto;
            padding: 10px;
            background: white;
            border-radius: 8px;
            display: inline-block;
        }
        .leaderboard h3, .message-board h3 {
            color: var(--secondary);
            margin-bottom: 1rem;
        }
        .leaderboard ul, .message-board ul {
            list-style: none;
            padding: 0;
        }
        .leaderboard li {
            padding: 0.5rem 0;
            font-size: 1.1rem;
        }
        .message-board .message-card {
            display: flex;
            align-items: flex-start;
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            animation: fadeIn 0.5s ease-in;
        }
        .message-board .avatar {
            width: 40px;
            height: 40px;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        .message-board .message-content {
            flex: 1;
        }
        .message-board .message-timestamp {
            color: var(--secondary);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .message-board .message-text {
            word-break: break-word;
        }
        .message-board form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1rem;
        }
        .message-board textarea {
            resize: none;
            padding: 0.8rem;
            border-radius: 8px;
            border: 1px solid var(--secondary);
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-color);
            font-family: inherit;
            transition: border-color 0.3s ease;
        }
        .message-board textarea:focus {
            border-color: var(--highlight);
            outline: none;
        }
        .message-board button {
            background: linear-gradient(135deg, #6e45e2, #88d3ce);
            padding: 0.8rem;
            border: none;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .message-board button:hover {
            box-shadow: 0 6px 20px rgba(110, 69, 226, 0.6);
        }
        .message-board .error, .message-board .success {
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        .message-board .error {
            color: var(--danger);
        }
        .message-board .success {
            color: var(--success);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); opacity: 0.5; }
            50% { opacity: 0.8; }
            100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; }
        }
        @media (max-width: 768px) {
            h1 { font-size: 2rem; }
            .counter-number { font-size: 3rem; }
            .container { padding: 1rem; }
            .btn { margin: 0.5rem 0; display: block; width: 100%; }
            .btn-container { display: flex; flex-direction: column; }
            .modal-buttons { flex-direction: column; }
            .modal-btn { width: 100%; margin-bottom: 0.5rem; }
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-number {
            animation: slideIn 1s ease-out forwards;
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    <div class="container">
        <header>
            <h1>恭喜被骗</h1>
        </header>
        <div class="counter-card">
            <h2>您是第 <span id="counter" class="counter-number"><?php echo (int)$count; ?></span> 位访问者</h2>
            <p>反思一下你为什么点进来了？回答我！Look in my eyes！</p>
        </div>
        <div class="leaderboard">
            <h3>被骗排行榜</h3>
            <ul>
                <?php if (empty($topRankings)): ?>
                    <li>暂无数据</li>
                <?php else: ?>
                    <?php foreach ($topRankings as $city => $count): ?>
                        <li><?= htmlspecialchars($city) ?>: <?= $count ?> 次</li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        <div class="info-grid">
            <div class="info-card">
                <h3>客户端信息</h3>
                <div class="info-item">
                    <span class="info-label">IP 地址</span>
                    <div class="info-value"><?= htmlspecialchars($ip) ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label">浏览器信息</span>
                    <div class="info-value"><?= htmlspecialchars($browser) ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label">访问时间</span>
                    <div class="info-value"><?= $date ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label">所在城市</span>
                    <div class="info-value"><?= htmlspecialchars($city) ?></div>
                </div>
            </div>
            <div class="info-card">
                <h3>服务器信息</h3>
                <div class="info-item">
                    <span class="info-label">服务器软件</span>
                    <div class="info-value"><?= $_SERVER['SERVER_SOFTWARE'] ?? '未知' ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label">PHP 版本</span>
                    <div class="info-value"><?= phpversion() ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label">服务器时间</span>
                    <div class="info-value"><?= date('Y-m-d H:i:s') ?></div>
                </div>
            </div>
        </div>
        <div class="message-board">
            <h3>留言板</h3>
            <ul>
                <?php if (empty($messages)): ?>
                    <li>暂无留言，快来留下你的被骗感言吧！</li>
                <?php else: ?>
                    <?php
                    $avatars = [
                        '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 2c-3.33 0-10 1.67-10 5v2h20v-2c0-3.33-6.67-5-10-5z"/></svg>',
                        '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 14c-2.67 0-8 1.33-8 4v1h16v-1c0-2.67-5.33-4-8-4zm0-10a4 4 0 110 8 4 4 0 010-8z"/></svg>',
                        '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 14c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"/></svg>',
                        '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-10 10c0 4.42 2.87 8.17 6.84 ONEY.49.5.09.66-.22.66-.48v-1.7c-2.78.61-3.37-1.34-3.37-1.34-.46-1.16-1.11-1.47-1.11-1.47-.91-.62.07-.61.07-.61 1 .07 1.53 1.03 1.53 1.03.89 1.52 2.34 1.08 2.91.83.09-.65.35-1.08.63-1.33-2.22-.25-4.55-1.11-4.55-4.94 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.65 0 0 .84-.27 2.75 1.02A9.56 9.56 0 0112 6.8c.85.004 1.71.11 2.52.33 1.91-1.29 2.75-1.02 2.75-1.02.55 1.38.2 2.4.1 2.65.64.7 1.03 1.59 1.03 2.68 0 3.84-2.34 4.68-4.57 4.93.36.31.68.92.68 1.85v2.74c0 .27.16.58.67.48A10.01 10.01 0 0022 12c0-5.52-4.48-10-10-10z"/></svg>',
                        '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm0-4c-.55 0-1-.45-1-1V8c0-.55.45-1 1-1s1 .45 1 1v4c0 .55-.45 1-1 1zm4 4c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm0-4c-.55 0-1-.45-1-1V8c0-.55.45-1 1-1s1 .45 1 1v4c0 .55-.45 1-1 1z"/></svg>'
                    ];
                    ?>
                    <?php foreach ($messages as $index => $msg): ?>
                        <li class="message-card">
                            <div class="avatar"><?= $avatars[$index % count($avatars)] ?></div>
                            <div class="message-content">
                                <div class="message-timestamp">[<?= htmlspecialchars($msg['timestamp']) ?>]</div>
                                <div class="message-text"><?= htmlspecialchars($msg['message']) ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <form id="messageForm">
                <textarea id="messageInput" maxlength="200" placeholder="留下你的被骗感言（最多200字）" required aria-label="留言输入框"></textarea>
                <button type="submit" aria-label="提交留言">提交留言</button>
            </form>
            <div id="messageFeedback"></div>
        </div>
        <div class="btn-container">
            <a href="https://github.com/yuntuanzi/18Warn/" class="btn btn-download" target="_blank" aria-label="下载源码">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12c0 4.42 2.87 8.17 6.84 9.49.5.09.66-.22.66-.48v-1.7c-2.78.61-3.37-1.34-3.37-1.34-.46-1.16-1.11-1.47-1.11-1.47-.91-.62.07-.61.07-.61 1 .07 1.53 1.03 1.53 1.03.89 1.52 2.34 1.08 2.91.83.09-.65.35-1.08.63-1.33-2.22-.25-4.55-1.11-4.55-4.94 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.65 0 0 .84-.27 2.75 1.02A9.56 9.56 0 0112 6.8c.85.004 1.71.11 2.52.33 1.91-1.29 2.75-1.02 2.75-1.02.55 1.38.2 2.4.1 2.65.64.7 1.03 1.59 1.03 2.68 0 3.84-2.34 4.68-4.57 4.93.36.31.68.92.68 1.85v2.74c0 .27.16.58.67.48A10.01 10.01 0 0022 12c0-5.52-4.48-10-10-10z"/>
                </svg>
                下载源码
            </a>
            <button type="button" class="btn btn-share" id="shareBtn" aria-label="分享给好友">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.13-4.14c.52.47 1.2.77 1.96.77 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.14c-.05.23-.09.46-.09.7 0 1.66 1.34 3 3 3s3-1.34 3-3-1.34-3-3-3z"/>
                </svg>
                去骗好友
            </button>
        </div>
    </div>
    <div class="modal" id="copyModal">
        <div class="modal-content">
            <span class="close-modal" id="closeCopyModal" aria-label="关闭分享弹窗">×</span>
            <h3 class="modal-title">复制成功！</h3>
            <p>链接已复制到剪贴板，快去分享给你的好友吧！</p>
            <div class="qrcode-container">
                <canvas id="qrcode"></canvas>
            </div>
            <p>扫描二维码分享</p>
            <div class="modal-buttons">
                <button type="button" class="modal-btn modal-btn-wechat" id="openWechat" aria-label="打开微信分享">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9.5 6.5c0-.552.448-1 1-1s1 .448 1 1-1 1-1 1-1-.448-1-1zm5 0c0-.552.448-1 1-1s1 .448 1 1-1 1-1 1-1-.448-1-1zm-2 5c0-.552.448-1 1-1s1 .448 1 1-1 1-1 1-1-.448-1-1zm-5 0c0-.552.448-1 1-1s1 .448 1 1-1 1-1 1-1-.448-1-1zm4.5 5c0-.552.448-1 1-1s1 .448 1 1-1 1-1 1-1-.448-1-1zm-8-10.5C2.672 6 0 7.672 0 10c0 1.656 1.056 3.056 2.528 3.584-.528.672-.848 1.456-.848 2.416 0 .528.416.96 1.008.96.464 0 .848-.304 1.008-.736.464-1.184 1.632-2.064 3.008-2.064h.304c1.376 0 2.544.88 3.008 2.064.16.432.544.736 1.008.736.592 0 1.008-.432.848-1.008 0-.96-.320-1.744-.848-2.416C14.944 13.056 16 11.656 16 10c0-2.328-2.672-4-6-4zm14 2c-3.328 0-6 2.672-6 6 0 2.328 1.672 4.328 4 5.328-.528.672-.848 1.456-.848 2.416 0 .528.416.96 1.008.96.464 0 .848-.304 1.008-.736.464-1.184 1.632-2.064 3.008-2.064h.304c1.376 0 2.544.88 3.008 2.064.16.432.544.736 1.008.736.592 0 1.008-.432.1.008-.96 0-.96-.320-1.744-.848-2.416C22.328 17.328 24 15.328 24 13c0-3.328-2.672-6-6-6z"/>
                    </svg>
                    打开微信
                </button>
                <button type="button" class="modal-btn modal-btn-qq" id="openQQ" aria-label="打开QQ分享">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0C5.373 0 0 5.373 0 12c0 5.296 3.438 9.798 8.205 11.387.6.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.776.418-1.305.762-1.605-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.536-1.523.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.241 2.874.118 3.176.77.84 1.236 2.01 1.236 3.221 0 4.61-2.807 5.625-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.295 24 12c0-6.627-5.373-12-12-12z"/>
                    </svg>
                    打开QQ
                </button>
            </div>
        </div>
    </div>
    <button type="button" class="theme-toggle" id="themeToggle" aria-label="切换主题">🌓</button>
    <script>
        function animateValue(id, start, end, duration) {
            console.log('animateValue called with end =', end);
            const obj = document.getElementById(id);
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                obj.innerHTML = Math.floor(progress * (end - start) + start);
                obj.classList.add('animate-number');
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }
        function initParticles() {
            const container = document.getElementById('particles');
            const fragment = document.createDocumentFragment();
            const particleCount = Math.floor(window.innerWidth / 10);
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                const size = Math.random() * 5 + 2;
                const posX = Math.random() * window.innerWidth;
                const delay = Math.random() * 15;
                const duration = Math.random() * 15 + 10;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${posX}px`;
                particle.style.bottom = `-10px`;
                particle.style.animationDelay = `${delay}s`;
                particle.style.animationDuration = `${duration}s`;
                fragment.appendChild(particle);
            }
            container.appendChild(fragment);
        }
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }
        function copyToClipboard(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                return true;
            } catch (e) {
                return false;
            } finally {
                document.body.removeChild(textarea);
            }
        }
        function generateQRCode(canvas, text) {
            QRCode.toCanvas(canvas, text, { width: 128, height: 128 }, function (error) {
                if (error) console.error('QR Code generation error:', error);
            });
        }
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
            console.log('Initial counter value:', <?php echo (int)$count; ?>);
            initParticles();
            generateQRCode(document.getElementById('qrcode'), '<?php echo $qrCodeUrl; ?>');
            document.getElementById('themeToggle').addEventListener('click', toggleTheme);
            document.getElementById('shareBtn').addEventListener('click', () => {
                if (copyToClipboard('<?php echo $shareUrl; ?>')) {
                    document.getElementById('copyModal').style.display = 'flex';
                } else {
                    alert('复制失败，请手动复制链接：<?php echo $shareUrl; ?>');
                }
            });
            document.getElementById('closeCopyModal').addEventListener('click', () => {
                document.getElementById('copyModal').style.display = 'none';
            });
            document.getElementById('openWechat').addEventListener('click', () => {
                window.location.href = 'weixin://';
                document.getElementById('copyModal').style.display = 'none';
            });
            document.getElementById('openQQ').addEventListener('click', () => {
                window.location.href = 'https://qm.qq.com/cgi-bin/qm/qr?k=6ww_haorKSc-F1QWF4JdrErhmFzxeNbo&jump_from=webapi&authKey=7py1srP3pqE94lCPjoh02aQhVPjZuTlYhm+q+yZ4NpTP0WEd46kTWKRau+P5r9ey';
                document.getElementById('copyModal').style.display = 'none';
            });
            window.addEventListener('click', (e) => {
                if (e.target === document.getElementById('copyModal')) {
                    document.getElementById('copyModal').style.display = 'none';
                }
            });
            document.getElementById('messageForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const input = document.getElementById('messageInput');
                const feedback = document.getElementById('messageFeedback');
                const message = input.value.trim();
                if (message.length === 0) {
                    feedback.textContent = '留言不能为空！';
                    feedback.className = 'error';
                    return;
                }
                if (message.length > 200) {
                    feedback.textContent = '留言不能超过200字！';
                    feedback.className = 'error';
                    return;
                }
                try {
                    const response = await fetch('save_message.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `message=${encodeURIComponent(message)}`
                    });
                    const result = await response.json();
                    if (result.success) {
                        feedback.textContent = '留言提交成功！';
                        feedback.className = 'success';
                        input.value = '';
                        const ul = document.querySelector('.message-board ul');
                        const li = document.createElement('li');
                        li.className = 'message-card';
                        li.innerHTML = `
                            <div class="avatar">${
                                document.querySelectorAll('.message-card .avatar')[0]?.innerHTML ||
                                '<?php echo $avatars[0]; ?>'
                            }</div>
                            <div class="message-content">
                                <div class="message-timestamp">[${result.timestamp}]</div>
                                <div class="message-text">${result.message}</div>
                            </div>
                        `;
                        ul.prepend(li);
                        if (ul.children.length > 5) {
                            ul.removeChild(ul.lastChild);
                        }
                    } else {
                        feedback.textContent = result.error || '提交失败，请稍后重试！';
                        feedback.className = 'error';
                    }
                } catch (e) {
                    feedback.textContent = '网络错误，请稍后重试！';
                    feedback.className = 'error';
                }
            });
        });
    </script>
</body>
</html>