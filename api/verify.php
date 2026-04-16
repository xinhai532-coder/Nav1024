<?php
// api/verify.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 引入配置
require_once __DIR__ . '/../config.php';

// 获取请求体
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 验证密码
if (isset($data['password']) && $data['password'] === ADMIN_PASSWORD) {
    // 生成临时 token
    $_SESSION['admin_verified'] = true;
    $_SESSION['verify_time'] = time();
    
    echo json_encode([
        'status' => 'success',
        'message' => '验证成功'
    ]);
} else {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => '密码错误'
    ]);
}
?>
