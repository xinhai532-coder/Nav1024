<?php
// api/check_links.php - 检查死链
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin_verified']) || $_SESSION['admin_verified'] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => '未授权']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['urls']) || !is_array($data['urls'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '数据格式错误']);
    exit;
}

$results = [];

foreach ($data['urls'] as $item) {
    $url = $item['url'];
    $id = $item['id'];
    
    try {
        // 使用cURL检测链接（更准确可靠）
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_NOBODY => true,          // 只获取头部，不下载内容
            CURLOPT_FOLLOWLOCATION => true,  // 跟随重定向
            CURLOPT_MAXREDIRS => 5,          // 最多重定向5次
            CURLOPT_TIMEOUT => 20,           // 总超时20秒（增加以支持慢速网站）
            CURLOPT_CONNECTTIMEOUT => 8,     // 连接超时8秒
            CURLOPT_RETURNTRANSFER => true,  // 返回结果
            CURLOPT_SSL_VERIFYPEER => false, // 不验证SSL证书
            CURLOPT_SSL_VERIFYHOST => false, // 不验证SSL主机
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_HEADER => true,          // 包含头部信息
            CURLOPT_HTTPGET => true          // 使用GET请求
        ]);
        
        curl_exec($ch);
        
        // 检查是否有错误
        $errno = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        // cURL错误码不为0表示连接失败
        if ($errno !== 0) {
            $results[] = [
                'id' => $id,
                'status' => 'error',
                'accessible' => false,
                'error' => curl_strerror($errno)
            ];
            continue;
        }
        
        // 2xx和3xx都认为是可访问的
        $accessible = ($httpCode >= 200 && $httpCode < 400);
        
        $results[] = [
            'id' => $id,
            'status' => 'success',
            'accessible' => $accessible,
            'http_code' => $httpCode
        ];
    } catch (Exception $e) {
        $results[] = ['id' => $id, 'status' => 'error', 'accessible' => false];
    }
}

echo json_encode(['status' => 'success', 'results' => $results]);
?>
