<?php
// 设置PHP版本要求
if (version_compare(PHP_VERSION, '8.1.0') < 0) {
    die('嘿，你需要安装PHP 8.1或者更高的版本来运行');
}

// 检查访问权限
if (!isset($_COOKIE['age_verified']) || $_COOKIE['age_verified'] !== 'true') {
    // 显示无权限页面
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>访问受限</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
                to {
                    width: 100%;
                }
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

// 定义计数器文件
$counterFile = 'counter.txt';

// 初始化或读取计数器
if (!file_exists($counterFile)) {
    file_put_contents($counterFile, '0');
}

// 增加计数器
$count = (int)file_get_contents($counterFile);
$count++;
file_put_contents($counterFile, (string)$count);

// 获取客户端信息
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$browser = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$date = date('Y-m-d H:i:s');

// 生成分享链接
$shareUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/index.html";
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>恭喜被骗</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6e45e2;
            --secondary: #88d3ce;
            --dark: #1a1a2e;
            --darker: #16213e;
            --light: #f1f1f1;
            --danger: #e94560;
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
            font-family: 'Montserrat', sans-serif;
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
            font-family: 'Orbitron', sans-serif;
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            position: relative;
            display: inline-block;
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
            font-family: 'Orbitron', sans-serif;
            font-size: 5rem;
            font-weight: 700;
            text-align: center;
            margin: 1rem 0;
            color: var(--highlight);
        }

        .info-card {
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

        .btn i {
            margin-right: 8px;
            font-size: 1.2rem;
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
            background: linear-gradient(135deg, var(--primary, #6e45e2), var(--secondary, #88d3ce));
            border-radius: 50%;
            opacity: 0.5;
            animation: float 15s infinite linear;
        }

        /* 弹窗样式 */
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
            margin-bottom: 1.5rem;
            color: var(--highlight);
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
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
        }

        .modal-btn i {
            margin-right: 8px;
        }

        .modal-btn-wechat {
            background: linear-gradient(135deg, #07C160, #09ae85);
            color: white;
            border: none;
        }

        .modal-btn-qq {
            background: linear-gradient(135deg, #12B7F5, #0081ff);
            color: white;
            border: none;
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

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.5;
            }
            50% {
                opacity: 0.8;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
            }
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }
            
            .counter-number {
                font-size: 3rem;
            }
            
            .container {
                padding: 1rem;
            }
            
            .btn {
                margin: 0.5rem 0;
                display: block;
                width: 100%;
            }
            
            .btn-container {
                display: flex;
                flex-direction: column;
            }

            .modal-buttons {
                flex-direction: column;
            }

            .modal-btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }

        /* 数字滚动动画 */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-number {
            animation: slideIn 1s ease-out forwards;
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="container">
        
        <div class="counter-card">
            <h2>您是第 <span id="counter" class="counter-number">0</span> 位访问者</h2>
            <p>反思一下你为什么点进来了？回答我！Look in my eyes！</p>
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
            </div>
            
            <div class="info-card">
                <h3>服务器信息</h3>
                <div class="info-item">
                    <span class="info-label">服务器软件</span>
                    <div class="info-value"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></div>
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
        
        <div class="btn-container">
            <a href="https://github.com/yuntuanzi/18Warn/" class="btn btn-download" target="_blank">
                <i class="fab fa-github"></i>下载源码
            </a>
            <button class="btn btn-share" id="shareBtn">
                <i class="fas fa-share-alt"></i>去骗好友
            </button>
        </div>
    </div>
    
    <!-- 复制成功弹窗 -->
    <div class="modal" id="copyModal">
        <div class="modal-content">
            <span class="close-modal" id="closeCopyModal">&times;</span>
            <h3 class="modal-title">复制成功！</h3>
            <p>链接已复制到剪贴板，快去分享给你的好友吧！</p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-wechat" id="openWechat">
                    <i class="fab fa-weixin"></i>打开微信
                </button>
                <button class="modal-btn modal-btn-qq" id="openQQ">
                    <i class="fab fa-qq"></i>打开QQ
                </button>
            </div>
        </div>
    </div>
    
    <button class="theme-toggle" id="themeToggle">🌓</button>
    
    <script>
        // 数字滚动动画
        function animateValue(id, start, end, duration) {
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
        
        // 初始化粒子效果
        function initParticles() {
            const container = document.getElementById('particles');
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
                
                container.appendChild(particle);
            }
        }
        
        // 主题切换
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }
        
        // 复制链接到剪贴板
        function copyToClipboard(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
        
        // 初始化
        document.addEventListener('DOMContentLoaded', () => {
            // 从本地存储加载主题
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
            
            // 初始化数字动画
            animateValue('counter', 0, <?= $count ?>, 2000);
            
            // 初始化粒子
            initParticles();
            
            // 主题切换按钮
            document.getElementById('themeToggle').addEventListener('click', toggleTheme);
            
            // 分享按钮事件
            document.getElementById('shareBtn').addEventListener('click', () => {
                const shareUrl = '<?= $shareUrl ?>';
                copyToClipboard(shareUrl);
                
                // 显示复制成功弹窗
                document.getElementById('copyModal').style.display = 'flex';
            });
            
            // 关闭弹窗
            document.getElementById('closeCopyModal').addEventListener('click', () => {
                document.getElementById('copyModal').style.display = 'none';
            });
            
            // 打开微信
            document.getElementById('openWechat').addEventListener('click', () => {
                window.location.href = 'weixin://';
                document.getElementById('copyModal').style.display = 'none';
            });
            
            // 打开QQ
            document.getElementById('openQQ').addEventListener('click', () => {
                window.location.href = 'https://qm.qq.com/cgi-bin/qm/qr?k=6ww_haorKSc-F1QWF4JdrErhmFzxeNbo&jump_from=webapi&authKey=7py1srP3pqE94lCPjoh02aQhVPjZuTlYhm+q+yZ4NpTP0WEd46kTWKRau+P5r9ey';
                document.getElementById('copyModal').style.display = 'none';
            });
            
            // 点击弹窗外部关闭
            window.addEventListener('click', (e) => {
                if (e.target === document.getElementById('copyModal')) {
                    document.getElementById('copyModal').style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
