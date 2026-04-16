<?php
// 检查PHP环境和cURL支持
echo "========== PHP环境检查 ==========\n\n";

echo "PHP版本: " . phpversion() . "\n";
echo "cURL扩展: " . (extension_loaded('curl') ? '✅ 已加载' : '❌ 未加载') . "\n";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? '✅ 开启' : '❌ 关闭') . "\n";
echo "OpenSSL扩展: " . (extension_loaded('openssl') ? '✅ 已加载' : '❌ 未加载') . "\n\n";

if (extension_loaded('curl')) {
    $curl_version = curl_version();
    echo "cURL版本: " . $curl_version['version'] . "\n";
    echo "SSL版本: " . $curl_version['ssl_version'] . "\n\n";
}

// 测试几个不同类型的链接
$test_urls = [
    'https://www.baidu.com',
    'https://www.google.com',
    'http://galiji.ysepan.com/',
    'https://next.itellyou.cn/Identity/Account/Login?ReturnUrl=%2FOriginal%2FIndex'
];

echo "========== 链接测试 ==========\n\n";

foreach ($test_urls as $url) {
    echo "测试: $url\n";
    
    // 方法1: cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_NOBODY => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_HEADER => true,
        CURLOPT_HTTPGET => true
    ]);
    
    curl_exec($ch);
    $errno = curl_errno($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  cURL结果: HTTP $httpCode | 错误码: " . ($errno === 0 ? '无' : $errno) . " | ";
    echo (($httpCode >= 200 && $httpCode < 400) ? '✅ 有效' : '❌ 失效') . "\n";
    
    // 方法2: get_headers（对比）
    stream_context_set_default([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $headers = @get_headers($url, 1);
    if ($headers && isset($headers[0])) {
        preg_match('/\d{3}/', $headers[0], $matches);
        $getCode = isset($matches[0]) ? intval($matches[0]) : 0;
        echo "  get_headers结果: HTTP $getCode | " . (($getCode >= 200 && $getCode < 400) ? '✅ 有效' : '❌ 失效') . "\n";
    } else {
        echo "  get_headers结果: ❌ 失败\n";
    }
    
    echo "\n";
}
?>
