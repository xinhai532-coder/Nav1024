<?php
// api/edit.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 引入配置
require_once __DIR__ . '/../config.php';

// 1. 获取请求体
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 2. 验证 session（检查是否已验证过密码）
if (!isset($_SESSION['admin_verified']) || $_SESSION['admin_verified'] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => '未授权，请先验证密码']);
    exit;
}

// 检查 session 是否过期（30分钟）
if (isset($_SESSION['verify_time']) && (time() - $_SESSION['verify_time']) > 1800) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => '会话已过期，请重新验证']);
    exit;
}

// 3. 数据校验
if ($data === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'JSON解析失败: ' . json_last_error_msg()]);
    exit;
}

if (!isset($data['data'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '缺少data字段']);
    exit;
}

if (!is_array($data['data'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'data字段必须是数组，当前类型: ' . gettype($data['data'])]);
    exit;
}

// 4. 写入文件
// LOCK_EX: 独占锁，防止并发写入冲突
// JSON_UNESCAPED_UNICODE: 中文不转义
// JSON_PRETTY_PRINT: 格式化输出，方便查看
if (file_put_contents(DATA_PATH, json_encode($data['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
    echo json_encode(['status' => 'success', 'message' => '保存成功']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => '写入失败，请检查 data 目录权限']);
}
?>