<?php
header('Content-Type: application/json; charset=utf-8');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['url'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '缺少URL参数']);
    exit;
}

$url = $data['url'];

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'URL格式不正确']);
    exit;
}

try {
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'follow_location' => true,
            'max_redirects' => 5
        ]
    ]);
    
    $html = @file_get_contents($url, false, $context);
    
    if ($html === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => '无法访问该网站']);
        exit;
    }
    
    $title = '';
    if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
        $title = trim($matches[1]);
        $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
    }
    
    $description = '';
    if (preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\'](.*?)["\']/i', $html, $matches)) {
        $description = trim($matches[1]);
    } elseif (preg_match('/<meta[^>]*content=["\'](.*?)["\'][^>]*name=["\']description["\']/i', $html, $matches)) {
        $description = trim($matches[1]);
    }
    $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
    
    $favicon = '';
    $parsed_url = parse_url($url);
    $domain = $parsed_url['scheme'] . '://' . $parsed_url['host'];
    
    if (preg_match('/<link[^>]*rel=["\'](?:shortcut icon|icon)["\'][^>]*href=["\'](.*?)["\']/i', $html, $matches)) {
        $favicon_href = $matches[1];
        if (strpos($favicon_href, 'http') === 0) {
            $favicon = $favicon_href;
        } elseif (strpos($favicon_href, '//') === 0) {
            $favicon = $parsed_url['scheme'] . ':' . $favicon_href;
        } else {
            $favicon = $domain . '/' . ltrim($favicon_href, '/');
        }
    } else {
        $favicon = $domain . '/favicon.ico';
    }
    
    if (empty($title)) {
        $title = $parsed_url['host'];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'title' => $title,
            'desc' => $description,
            'favicon' => $favicon,
            'url' => $url
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => '获取网站信息失败: ' . $e->getMessage()]);
}
?>
