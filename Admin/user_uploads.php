<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
global $conn;
require_once __DIR__ . '/config.php';
require_admin();

// 安全获取用户ID
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// 验证用户存在性
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if (!$user_result) {
    die("用户查询失败：" . $conn->error);
}

if ($user_result->num_rows === 0) {
    die("<div class='alert'>⚠️ 指定用户不存在</div>");
}

$user = $user_result->fetch_assoc();

// 获取上传记录
$uploads_query = "SELECT * FROM uploads WHERE user_id = ? ORDER BY created_at DESC";
$stmt_uploads = $conn->prepare($uploads_query);
$stmt_uploads->bind_param("i", $user_id);
$stmt_uploads->execute();
$uploads_result = $stmt_uploads->get_result();
$uploads = $uploads_result->fetch_all(MYSQLI_ASSOC) ?: [];
?>


<!DOCTYPE html>
<html>
<head>
    <title><?= safe_echo(safe_get($user, 'username', '未知用户')) ?>的上传记录</title>
    <link rel="stylesheet" href="./assets/css/admin.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
        }
        .sidebar-header {
            padding: 10px 20px;
            border-bottom: 1px solid #4b545c;
            margin-bottom: 20px;
        }
        .sidebar-menu {
            list-style: none;
        }
        .sidebar-menu li a {
            display: block;
            padding: 10px 20px;
            color: #adb5bd;
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            color: white;
            background-color: #495057;
        }
        .sidebar-menu li a i {
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #6c757d;
        }

        .empty-state img {
            width: 150px;
            opacity: 0.7;
            margin-bottom: 20px;
        }

        .badge {
            background: #e9ecef;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.8em;
        }
        .back-button {
            display: inline-block;
            padding: 8px 15px;
            background: #6c757d;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            margin-bottom: 20px;
            transition: background 0.3s;
        }

        .back-button:hover {
            background: #5a6268;
            color: white;
            text-decoration: none;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>后台管理系统</h2>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>数据概括</a></li>
        <li><a href="users.php" class="active"><i class="fas fa-users"></i>用户管理</a></li>
        <li><a href="review.php"><i class="fas fa-box"></i>上传管理</a></li>
        <li class="has-submenu">
            <a href="javascript:void(0);" class="menu-toggle"><i class="fas fa-dna"></i>数据管理</a>
            <ul class="submenu">
                <li><a href="genomics_content.php">Genomics</a></li>
            </ul>
        </li>
        <li class="has-submenu">
            <a href="javascript:void(0);" class="menu-toggle"><i class="fas fa-dna"></i>数据上传</a>
            <ul class="submenu">
                <li><a href="gene_upload.php">Genomics</a></li>
            </ul>
        </li>
        <li><a href="settings.php"><i class="fas fa-cog"></i>系统设置</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header-actions">
        <?php
        $source_id = isset($_GET['source_id']) ? (int)$_GET['source_id'] : 0;
        $from = $_GET['from'] ?? '';
        ?>

        <div class="header-actions">
            <?php if ($from === 'detail' && $source_id > 0): ?>
                <a href="detail.php?upload_id=<?= $source_id ?>"
                   class="back-button">
                    返回内容详情
                </a>
            <?php endif; ?>
        </div>
    </div>
    <h2>
        <?= safe_echo(safe_get($user, 'username', '已删除用户')) ?>的上传记录
        <span class="badge"><?= count($uploads) ?>条</span>
    </h2>

    <?php if (empty($uploads)): ?>
        <div class="empty-state">
            <img src="/images/empty.png" alt="空状态">
            <p>该用户暂未上传任何内容</p>
        </div>
    <?php else: ?>
        <div class="user-uploads content-card">
            <?php foreach ($uploads as $upload): ?>
                <div class="upload-item">
                    <h3>
                        <a href="detail.php?upload_id=<?= (int)safe_get($upload, 'upload_id') ?>">
                            <?= safe_echo(safe_get($upload, 'title', '无标题')) ?>
                        </a>
                    </h3>
                    <div class="meta">
                        <span><?= safe_echo(safe_get($upload, 'created_at')) ?></span>
                        <span class="status-badge"><?= get_status_badge(safe_get($upload, 'status', 'pending')) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 处理菜单切换
        document.querySelectorAll('.menu-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const parent = this.closest('.has-submenu');

                // 切换当前菜单状态
                parent.classList.toggle('active');

                // 关闭其他子菜单
                document.querySelectorAll('.has-submenu').forEach(other => {
                    if (other !== parent) {
                        other.classList.remove('active');
                    }
                });
            });
        });

        // 点击页面其他区域关闭菜单
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.has-submenu')) {
                document.querySelectorAll('.has-submenu').forEach(menu => {
                    menu.classList.remove('active');
                });
            }
        });

        // 阻止子菜单点击冒泡
        document.querySelectorAll('.submenu a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    });
</script>
</body>
</html>