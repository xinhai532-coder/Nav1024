<?php
// check-data.php - 数据诊断工具
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 导航站数据诊断</h1>";
echo "<hr>";

// 1. 检查data目录
$dataDir = __DIR__ . '/../data';
echo "<h2>1. 数据目录检查</h2>";
if (is_dir($dataDir)) {
    echo "✅ 目录存在: $dataDir<br>";
    if (is_writable($dataDir)) {
        echo "✅ 目录可写<br>";
    } else {
        echo "❌ 目录不可写! 请设置权限为 755 或 777<br>";
    }
} else {
    echo "❌ 目录不存在!<br>";
}

// 2. 检查data.json文件
$dataFile = $dataDir . '/data.json';
echo "<h2>2. 数据文件检查</h2>";
if (file_exists($dataFile)) {
    echo "✅ 文件存在: $dataFile<br>";
    echo "文件大小: " . filesize($dataFile) . " 字节<br>";
    
    // 读取并验证JSON
    $content = file_get_contents($dataFile);
    $data = json_decode($content, true);
    
    if ($data === null) {
        echo "❌ JSON解析失败!<br>";
        echo "错误信息: " . json_last_error_msg() . "<br>";
    } else {
        echo "✅ JSON格式正确<br>";
        echo "记录数量: " . count($data) . " 条<br>";
        
        // 检查每条记录的格式
        $errors = [];
        foreach ($data as $index => $item) {
            if (!isset($item['id'])) {
                $errors[] = "第" . ($index + 1) . "条: 缺少id字段";
            }
            if (!isset($item['title'])) {
                $errors[] = "第" . ($index + 1) . "条: 缺少title字段";
            }
            if (!isset($item['url'])) {
                $errors[] = "第" . ($index + 1) . "条: 缺少url字段";
            }
            if (!isset($item['category'])) {
                $errors[] = "第" . ($index + 1) . "条: 缺少category字段";
            }
        }
        
        if (empty($errors)) {
            echo "✅ 所有记录格式正确<br>";
        } else {
            echo "❌ 发现 " . count($errors) . " 个错误:<br>";
            echo "<ul>";
            foreach (array_slice($errors, 0, 10) as $error) {
                echo "<li>$error</li>";
            }
            if (count($errors) > 10) {
                echo "<li>...还有 " . (count($errors) - 10) . " 个错误</li>";
            }
            echo "</ul>";
        }
        
        // 显示前5条数据示例
        echo "<h3>数据示例(前5条):</h3>";
        echo "<pre>";
        echo htmlspecialchars(json_encode(array_slice($data, 0, 5), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "</pre>";
    }
} else {
    echo "❌ 文件不存在!<br>";
}

// 3. 检查Session
echo "<h2>3. Session检查</h2>";
session_start();
if (isset($_SESSION['admin_verified']) && $_SESSION['admin_verified'] === true) {
    echo "✅ 已登录<br>";
    if (isset($_SESSION['verify_time'])) {
        $elapsed = time() - $_SESSION['verify_time'];
        echo "登录时间: " . date('Y-m-d H:i:s', $_SESSION['verify_time']) . "<br>";
        echo "已过时间: {$elapsed} 秒<br>";
        if ($elapsed < 1800) {
            echo "✅ Session未过期<br>";
        } else {
            echo "❌ Session已过期(超过30分钟)<br>";
        }
    }
} else {
    echo "❌ 未登录,请先访问 edit.php 验证密码<br>";
}

// 4. PHP配置检查
echo "<h2>4. PHP配置检查</h2>";
echo "PHP版本: " . phpversion() . "<br>";
echo "JSON扩展: " . (extension_loaded('json') ? '✅ 已加载' : '❌ 未加载') . "<br>";
echo "最大上传大小: " . ini_get('upload_max_filesize') . "<br>";
echo "POST最大大小: " . ini_get('post_max_size') . "<br>";

echo "<hr>";
echo "<p><a href='edit.php'>返回编辑页面</a></p>";
?>
