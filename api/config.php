<?php
// api/config.php
header('Content-Type: application/json; charset=utf-8');

// 引入配置
require_once __DIR__ . '/../config.php';

// 返回配置信息（只返回前端需要的非敏感配置）
echo json_encode([
    'status' => 'success',
    'config' => [
        'edit_trigger' => EDIT_TRIGGER_KEY,
        'site_title' => SITE_TITLE,
        'site_logo' => SITE_LOGO,
        'copyright' => COPYRIGHT_TEXT,
        'friend_links' => FRIEND_LINKS,
        'banner_image' => BANNER_IMAGE,
        'banner_title' => BANNER_TITLE,
        'banner_subtitle' => BANNER_SUBTITLE
    ]
]);
?>
