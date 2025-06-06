<?php
session_start();
global $conn;
require_once __DIR__ . '/config.php';
require_admin();

// 分页设置
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// 获取已审核内容
$query = "
    SELECT 
        u.upload_id,
        u.title,
        u.content,
        u.status,
        u.reviewed_at,
        u.review_reason,
        usr.username as uploader,
        DATE_FORMAT(u.created_at, '%Y-%m-%d %H:%i') as created_at_formatted
    FROM uploads u
    JOIN users usr ON u.user_id = usr.id
    WHERE u.status IN ('approved', 'rejected')
    ORDER BY u.reviewed_at DESC
    LIMIT ? OFFSET ?
";

// 使用 MySQLi 准备和执行查询
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$uploads = $result->fetch_all(MYSQLI_ASSOC);

// 获取总数量
$count_query = "SELECT COUNT(*) FROM uploads WHERE status IN ('approved', 'rejected')";
$count_result = $conn->query($count_query);
$total = $count_result->fetch_row()[0];
$total_pages = ceil($total / $per_page);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>已审核内容</title>
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
        .header {
            background-color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .content-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .pagination {
            margin-top: 20px;
        }

        .reason-box {
            margin-top: 10px;
            padding: 10px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 3px;
        }

        .review-time {
            font-size: 0.9em;
            color: #6c757d;
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
        <li><a href="users.php"><i class="fas fa-users"></i>用户管理</a></li>
        <li><a href="review.php" class="active"><i class="fas fa-box"></i>上传管理</a></li>
        <li class="has-submenu">
            <a href="javascript:void(0);" class="menu-toggle"><i class="fas fa-dna"></i>数据管理</a>
            <ul class="submenu">
                <li><a href="genomics_content.php">Genomics</a></li>
                <li><a href="microbiomics_content.php">Microbiomics</a></li>
                <li><a href="phenotype_content.php">Phenotype</a></li>
            </ul>
        </li>
        <li class="has-submenu">
            <a href="javascript:void(0);" class="menu-toggle"><i class="fas fa-dna"></i>数据上传</a>
            <ul class="submenu">
                <li><a href="gene_upload.php">Genomics</a></li>
                <li><a href="microbiomics_upload.php">Microbiomics</a></li>
                <li><a href="phenotype_upload.php">Phenotype</a></li>
            </ul>
        </li>
        <li><a href="settings.php"><i class="fas fa-cog"></i>系统设置</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h2>已审核内容（共 <?= $total ?> 条）</h2>
        <a href="review.php" class="btn" style="background:#007bff;color:white;padding:8px 15px;border-radius:4px;">
            返回待审内容
        </a>
    </div>

    <?php foreach ($uploads as $upload): ?>
        <div class="content-card">
            <h3>
                <a href="detail.php?upload_id=<?= $upload['upload_id'] ?>">
                    <?= htmlspecialchars($upload['title']) ?>
                </a>
            </h3>

            <!-- 基础信息 -->
            <div class="meta">
                <span>👤 上传者：<?= htmlspecialchars($upload['uploader']) ?></span>
                <span>⏰ 上传时间：<?= $upload['created_at_formatted'] ?></span>
            </div>

            <!-- 审核信息 -->
            <div class="review-info">
                <p class="status-<?= $upload['status'] ?>">
                    <?= $upload['status'] === 'approved' ? '✅ 已通过' : '❌ 已拒绝' ?>
                    <span class="review-time">
                （审核时间：<?= format_db_time($upload['reviewed_at']) ?>）
            </span>
                </p>

                <?php if ($upload['status'] === 'rejected' && !empty($upload['review_reason'])): ?>
                    <div class="reason-box">
                        <strong>拒绝理由：</strong>
                        <p><?= nl2br(htmlspecialchars($upload['review_reason'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- 分页 -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" <?= $i == $page ? 'class="active"' : '' ?>><?= $i ?></a>
        <?php endfor; ?>
    </div>
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