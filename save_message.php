<?php
header('Content-Type: application/json');

// 限制提交频率（每IP每分钟1次）
$ip = $_SERVER['REMOTE_ADDR'];
$rateLimitFile = 'rate_limit.json';
$errorLog = 'error.log';
$rateLimit = [];
if (file_exists($rateLimitFile)) {
    $rateLimit = json_decode(file_get_contents($rateLimitFile), true) ?? [];
}

// 检查是否超限
$now = time();
if (isset($rateLimit[$ip]) && ($now - $rateLimit[$ip]) < 60) {
    echo json_encode(['success' => false, 'error' => '提交过于频繁，请1分钟后再试！']);
    exit;
}

// 更新频率限制
$rateLimit[$ip] = $now;
if (!file_put_contents($rateLimitFile, json_encode($rateLimit, JSON_PRETTY_PRINT), LOCK_EX)) {
    error_log("无法写入 rate_limit.json: " . date('Y-m-d H:i:s'), 3, $errorLog);
}

// 获取留言
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
if (empty($message) || strlen($message) > 200) {
    echo json_encode(['success' => false, 'error' => '留言无效或过长！']);
    exit;
}

// 保存留言
$messagesFile = 'messages.json';
$messages = [];
if (file_exists($messagesFile)) {
    $messages = json_decode(file_get_contents($messagesFile), true) ?? [];
}

$timestamp = date('Y-m-d H:i:s');
$messages = array_merge([[
    'message' => $message,
    'timestamp' => $timestamp
]], $messages);

if (!file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT), LOCK_EX)) {
    error_log("无法写入 messages.json: " . date('Y-m-d H:i:s'), 3, $errorLog);
    echo json_encode(['success' => false, 'error' => '保存留言失败，请稍后重试！']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => htmlspecialchars($message),
    'timestamp' => $timestamp
]);
?>