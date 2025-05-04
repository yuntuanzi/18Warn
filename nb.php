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
    error_log("Geo tán API 错误: " . $e->getMessage() . " - " . date('Y-m-d H:i:s'), 3, $errorLog);
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
            position: relative;
            z-index: 10;
            pointer-events: auto;
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
            background: linear-gradient(135deg, #primary, #secondary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 100;
            border: none;
            pointer-events: auto;
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
            pointer-events: auto;
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
            pointer-events: auto;
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
            pointer-events: auto;
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
        .btn-share.copied {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
        }
        .btn-share.copied::after {
            content: '已复制！';
            position: absolute;
            top: -30px;
            background: var(--success);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            animation: fadeOut 2s ease forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-10px); }
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
                    <?php foreach ($messages as $index => $msg): ?>
                        <li class="message-card">
                            <div class="avatar" data-avatar-index="<?php echo $index % 5; ?>"></div>
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
            <a href="https://github.com/bbb-lsy07/18Warn" class="btn btn-download" target="_blank" aria-label="下载源码">
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
            <h3 class="modal-title" id="modalTitle">分享链接</h3>
            <p id="modalMessage">链接已复制到剪贴板，快去分享给你的好友吧！</p>
            <p>手动复制以下链接分享</p>
            <input type="text" id="shareLink" value="<?php echo $shareUrl; ?>" readonly style="width: 100%; padding: 0.5rem; margin: 0.5rem 0; border-radius: 5px;">
            <div class="modal-buttons">
                <button type="button" class="modal-btn modal-btn-wechat" id="openWechat" aria-label="打开微信分享">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9.5 6.5c0-.55.45-1 1-1s1 .45 1 1-1 1-1 1-1-.45-1-1zm5 0c0-.55.45-1 1-1s1 .45 1 1-1 1-1 1-1-.45-1-1zm-2 5c0-.55.45-1 1-1s1 .45 1 1-1 1-1 1-1-.45-1-1zm-5 0c0-.55.45-1 1-1s1 .45 1 1-1 1-1 1-1-.45-1-1zm4.5 5c0-.55.45-1 1-1s1 .45 1 1-1 1-1 1-1-.45-1-1zm-8-10.5C2.67 6 0 7.67 0 10c0 1.66 1.06 3.06 2.53 3.58-.53.67-.85 1.46-.85 2.42 0 .53.42.96 1.01.96.46 0 .85-.3 1.01-.74.46-1.18 1.63-2.06 3.01-2.06h.3c1.38 0 2.54.88 3.01 2.06.16.43.55.74 1.01.74.59 0 1.01-.43.85-1.01 0-.96-.32-1.74-.85-2.42C14.94 13.06 16 11.66 16 10c0-2.33-2.67-4-6-4zm14 2c-3.33 0-6 2.67-6 6 0 2.33 1.67 4.33 4 5.33-.53.67-.85 1.46-.85 2.42 0 .53.42.96 1.01.96.46 0 .85-.3 1.01-.74.46-1.18 1.63-2.06 3.01-2.06h.3c1.38 0 2.54.88 3.01 2.06.16.43.55.74 1.01.74.59 0 1.01-.43.85-1.01 0-.96-.32-1.74-.85-2.42C22.33 17.33 24 15.33 24 13c0-3.33-2.67-6-6-6z"/>
                    </svg>
                    打开微信
                </button>
                <button type="button" class="modal-btn modal-btn-qq" id="openQQ" aria-label="打开QQ分享">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.44 9.8 8.21 11.39.6.11.79-.26.79-.58v-2.23c-3.34.73-4.03-1.42-4.03-1.42-.55-1.39-1.33-1.76-1.33-1.76-1.09-.75.08-.73.08-.73 1.2.08 1.84 1.24 1.84 1.24 1.07 1.83 2.81 1.3 3.49 1 .11-.78.42-1.3.76-1.61-2.67-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.12-.3-.54-1.52.12-3.18 0 0 1.01-.32 3.3 1.23A11.5 11.5 0 0112 5.8c1.02 0 2.05.14 3.01.4 2.29-1.55 3.3-1.23 3.3-1.23.66 1.66.24 2.88.12 3.18.77.84 1.24 1.91 1.24 3.22 0 4.61-2.81 5.63-5.48 5.92.43.37.82 1.1.82 2.22v3.29c0 .32.19.69.8.58C20.56 21.8 24 17.3 24 12c0-6.63-5.37-12-12-12z"/>
                    </svg>
                    打开QQ
                </button>
            </div>
        </div>
    </div>
    <button type="button" class="theme-toggle" id="themeToggle" aria-label="切换主题">🌓</button>
    <script data-cfasync="false">
        const avatars = [
            '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 2c-3.33 0-10 1.67-10 5v2h20v-2c0-3.33-6.67-5-10-5z"/></svg>',
            '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 14c-2.67 0-8 1.33-8 4v1h16v-1c0-2.67-5.33-4-8-4zm0-10a4 4 0 110 8 4 4 0 010-8z"/></svg>',
            '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 14c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"/></svg>',
            '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12c0 4.42 2.87 8.17 6.84 9.49.5.09.66-.22.66-.48v-1.7c-2.78.61-3.37-1.34-3.37-1.34-.46-1.16-1.11-1.47-1.11-1.47-.91-.62.07-.61.07-.61 1 .07 1.53 1.03 1.53 1.03.89 1.52 2.34 1.08 2.91.83.09-.65.35-1.08.63-1.33-2.22-.25-4.55-1.11-4.55-4.94 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.65 0 0 .84-.27 2.75 1.02A9.56 9.56 0 0112 6.8c.85.004 1.71.11 2.52.33 1.91-1.29 2.75-1.02 2.75-1.02.55 1.38.2 2.4.1 2.65.64.7 1.03 1.59 1.03 2.68 0 3.84-2.34 4.68-4.57 4.93.36.31.68.92.68 1.85v2.74c0 .27.16.58.67.48A10.01 10.01 0 0022 12c0-5.52-4.48-10-10-10z"/></svg>',
            '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm0-4c-.55 0-1-.45-1-1V8c0-.55.45-1 1-1s1 .45 1 1v4c0 .55-.45 1-1 1zm4 4c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm0-4c-.55 0-1-.45-1-1V8c0-.55.45-1 1-1s1 .45 1 1v4c0 .55-.45 1-1 1z"/></svg>'
        ];

        function animateValue(id, start, end, duration) {
            try {
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
            } catch (e) {
                console.error('animateValue error:', e);
            }
        }

        function initParticles() {
            try {
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
            } catch (e) {
                console.error('initParticles error:', e);
            }
        }

        function toggleTheme() {
            try {
                const html = document.documentElement;
                const currentTheme = html.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            } catch (e) {
                console.error('toggleTheme error:', e);
            }
        }

        async function copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                return { success: true, message: '复制成功！' };
            } catch (err) {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand('copy');
                    return { success: true, message: '复制成功！' };
                } catch (e) {
                    return { success: false, message: '复制失败，请手动复制链接：' + text };
                } finally {
                    document.body.removeChild(textarea);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            try {
                const savedTheme = localStorage.getItem('theme') || 'dark';
                document.documentElement.setAttribute('data-theme', savedTheme);
                console.log('Initial counter value:', <?php echo (int)$count; ?>);
                initParticles();

                // 设置留言头像
                document.querySelectorAll('.message-card .avatar').forEach(avatar => {
                    const index = parseInt(avatar.getAttribute('data-avatar-index')) || 0;
                    avatar.innerHTML = avatars[index % avatars.length];
                });

                // 主题切换按钮
                const themeToggle = document.getElementById('themeToggle');
                if (themeToggle) {
                    themeToggle.addEventListener('click', () => {
                        console.log('Theme toggle clicked');
                        toggleTheme();
                    });
                }

                // 分享按钮
                const shareBtn = document.getElementById('shareBtn');
                if (shareBtn) {
                    shareBtn.addEventListener('click', async () => {
                        console.log('Share button clicked');
                        try {
                            const modal = document.getElementById('copyModal');
                            const modalTitle = document.getElementById('modalTitle');
                            const modalMessage = document.getElementById('modalMessage');
                            const result = await copyToClipboard('<?php echo $shareUrl; ?>');
                            modalTitle.textContent = result.success ? '复制成功！' : '复制失败';
                            modalMessage.textContent = result.message;
                            modal.style.display = 'flex';
                            if (result.success) {
                                shareBtn.classList.add('copied');
                                shareBtn.disabled = true;
                                setTimeout(() => {
                                    shareBtn.classList.remove('copied');
                                    shareBtn.disabled = false;
                                }, 2000);
                            }
                        } catch (e) {
                            console.error('Share button error:', e);
                        }
                    });
                }

                // 关闭模态框
                const closeCopyModal = document.getElementById('closeCopyModal');
                if (closeCopyModal) {
                    closeCopyModal.addEventListener('click', () => {
                        console.log('Close modal clicked');
                        document.getElementById('copyModal').style.display = 'none';
                    });
                }

                // 打开微信
                const openWechat = document.getElementById('openWechat');
                if (openWechat) {
                    openWechat.addEventListener('click', () => {
                        console.log('Wechat button clicked');
                        window.location.href = 'weixin://';
                        document.getElementById('copyModal').style.display = 'none';
                    });
                }

                // 打开QQ
                const openQQ = document.getElementById('openQQ');
                if (openQQ) {
                    openQQ.addEventListener('click', () => {
                        console.log('QQ button clicked');
                        window.location.href = 'https://qm.qq.com/cgi-bin/qm/qr?k=6ww_haorKSc-F1QWF4JdrErhmFzxeNbo&jump_from=webapi&authKey=7py1srP3pqE94lCPjoh02aQhVPjZuTlYhm+q+yZ4NpTP0WEd46kTWKRau+P5r9ey';
                        document.getElementById('copyModal').style.display = 'none';
                    });
                }

                // 点击模态框外部关闭
                window.addEventListener('click', (e) => {
                    if (e.target === document.getElementById('copyModal')) {
                        console.log('Modal background clicked');
                        document.getElementById('copyModal').style.display = 'none';
                    }
                });

                // 留言表单提交
                const messageForm = document.getElementById('messageForm');
                if (messageForm) {
                    messageForm.addEventListener('submit', async (e) => {
                        console.log('Message form submitted');
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
                                    <div class="avatar">${avatars[0]}</div>
                                    <div class="message-content">
                                        <div class="message-timestamp">[${result。timestamp}]</div>
                                        <div class="message-text">${result。message}</div>
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
                            console.error('Message form error:', e);
                        }
                    });
                }
            } catch (e) {
                console.error('DOMContentLoaded error:', e);
            }
        });
    </script>
</body>
</html>
