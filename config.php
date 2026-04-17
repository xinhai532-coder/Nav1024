<?php
// config.php
// 设置时区
date_default_timezone_set('Asia/Shanghai');

// --- 安全设置 ---
// 修改为你自己的强密码，用于验证编辑权限
define('ADMIN_PASSWORD', 'admin123'); 

// --- 编辑模式触发设置 ---
// 在搜索框输入此字符串可触发编辑模式（建议设置为不易碰到的字符串）
define('EDIT_TRIGGER_KEY', 'edit');

// --- 网站基本设置 ---
// 网站标题
define('SITE_TITLE', '导航1024');
// 网站Logo路径（相对于网站根目录）
define('SITE_LOGO', 'image/logo.png');
// 版权信息
define('COPYRIGHT_TEXT', '© 2026 Nav1024. All Rights Reserved.');
// Banner 图片路径（相对于网站根目录，留空则使用必应每日壁纸）
define('BANNER_IMAGE', '');
// Banner 标题
define('BANNER_TITLE', '欢迎使用导航1024');
// Banner 副标题
define('BANNER_SUBTITLE', '这里精选了一些优秀的网站');
// 是否显示最近访问区域（true=显示，false=隐藏）
define('SHOW_RECENT_VISITS', true);

// --- 友情链接 ---
// 格式：数组，每个元素包含 name（名称）和 url（链接）
define('FRIEND_LINKS', [
    ['name' => 'GitHub', 'url' => 'https://github.com']
]);

// --- 路径设置 ---
// 数据文件绝对路径
define('DATA_PATH', __DIR__ . '/data/data.json');
?>
