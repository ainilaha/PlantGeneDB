<?php
// MySQL配置
define('DB', "localhost");
define('DB_PORT', '3306'); // MySQL默认端口
define('DB_NAME', 'QTD');
define('DB_USER', 'root');
define('DB_PASS', '12345678');


// 建立数据库连接
$conn = new mysqli(DB, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 设置字符集
$conn->set_charset("utf8mb4");

function require_admin()
{
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: error.php");
        exit();
    }
}
// 安全返回函数
function get_safe_referer() {
    // 允许的返回路径白名单
    $allowed_paths = [
        '/review.php',
        '/detail.php',
        '/dashboard.php'
    ];

    // 优先检查URL参数中的来源信息
    if (isset($_GET['from']) && $_GET['from'] === 'detail') {
        $source_id = filter_input(INPUT_GET, 'source_id', FILTER_VALIDATE_INT);
        if ($source_id) {
            return "detail.php?upload_id=" . (int)$source_id;
        }
    }

    // 次优先检查HTTP_REFERER
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $parsed = parse_url($referer);

    $is_valid =
        ($parsed['host'] ?? '') === $_SERVER['HTTP_HOST'] &&
        in_array($parsed['path'] ?? '', $allowed_paths);

    return $is_valid ? $referer : 'review.php';
}

// 安全获取数组值
function safe_get($array, $key, $default = '') {
    return $array[$key] ?? $default;
}

// 安全输出
function safe_echo($value) {
    echo htmlspecialchars((string)$value);
}

function get_status_badge($status) {
    $badges = [
        'pending' => '<span style="color: orange;">⏳ 待审核</span>',
        'approved' => '<span style="color: green;">✅ 已通过</span>',
        'rejected' => '<span style="color: red;">❌ 已拒绝</span>'
    ];
    return $badges[$status] ?? '<span>未知状态</span>';
}

function format_db_time($timestamp) {
    if (empty($timestamp)) {
        return '未记录时间';
    }
    try {
        $date = new DateTime($timestamp);
        return $date->format('Y-m-d H:i');
    } catch (Exception $e) {
        return '时间格式错误';
    }
}
?>
