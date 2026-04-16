<?php
// api/bing_wallpaper.php - 获取必应每日壁纸
header('Content-Type: application/json; charset=utf-8');

try {
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'Mozilla/5.0'
        ]
    ]);
    
    $api_url = 'https://cn.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1';
    $response = @file_get_contents($api_url, false, $context);
    
    if ($response === false) {
        throw new Exception('无法获取必应壁纸');
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['images'][0])) {
        $image = $data['images'][0];
        $url = 'https://cn.bing.com' . $image['url'];
        
        echo json_encode([
            'status' => 'success',
            'url' => $url,
            'copyright' => $image['copyright'] ?? ''
        ]);
    } else {
        throw new Exception('未找到壁纸数据');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
