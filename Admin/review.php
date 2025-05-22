<?php
require_once __DIR__ . '/config.php';
session_start();
global $conn;
require_admin();

// 分页设置
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// 获取待审核内容（优化版）
$query = "
    SELECT 
        u.upload_id,
        u.title,
        u.content,
        u.file_path,
        u.created_at,
        usr.username AS uploader,
        usr.institution AS institution
    FROM uploads u
    JOIN users usr ON u.user_id = usr.id
    WHERE u.status = 'pending'
    ORDER BY u.created_at DESC
    LIMIT ?, ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $offset, $per_page);
$stmt->execute();
$result = $stmt->get_result();
$uploads = $result->fetch_all(MYSQLI_ASSOC) ?: [];

// 获取总数量
$count_query = "SELECT COUNT(*) FROM uploads WHERE status = 'pending'";
$count_result = $conn->query($count_query);
$total = $count_result->fetch_row()[0];
$total_pages = ceil($total / $per_page);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>内容审核</title>
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
        .title-link {
            color: #1a73e8;
            text-decoration: none;
            transition: color 0.3s;
        }

        .title-link:hover {
            color: #1557b0;
            text-decoration: underline;
        }
        .file-preview {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .file-preview img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .tag {
            display: inline-block;
            padding: 2px 8px;
            background: #e9ecef;
            border-radius: 12px;
            font-size: 0.9em;
            margin-right: 5px;
        }
        .review-form {
            margin-top: 15px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .review-form input[type="text"] {
            width: 300px;
            padding: 6px 12px;
            margin-left: 10px;
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
            </ul>
        </li>
        <li class="has-submenu">
            <a href="javascript:void(0);" class="menu-toggle"><i class="fas fa-dna"></i>数据上传</a>
            <ul class="submenu">
                <li><a href="gene_upload.php">Genomics</a></li>
                <li><a href="microbiomics_upload.php">Microbiomics</a></li>
            </ul>
        </li>
        <li><a href="settings.php"><i class="fas fa-cog"></i>系统设置</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h2>待审核内容（共 <?= number_format($total) ?> 条）</h2>
        <div>
            <span class="tag"><?= $per_page ?>条/页</span>
            <a href="reviewed.php" class="btn" style="background:#28a745;color:white;padding:8px 15px;border-radius:4px;">
                已审核内容
            </a>
        </div>
    </div>
    <?php foreach ($uploads as $upload): ?>
        <div class="content-card">
            <div style="display: flex; justify-content: space-between;">
                <h3>
                    <a href="detail.php?upload_id=<?= $upload['upload_id'] ?>"
                       class="title-link">
                        <?= htmlspecialchars($upload['title']) ?>
                    </a>
                </h3>
                <div>
                    <span class="tag"><?= htmlspecialchars($upload['institution']) ?></span>
                    <span class="tag">ID: <?= $upload['upload_id'] ?></span>
                </div>
            </div>

            <p class="meta-info">
                <i class="fas fa-user"></i> <?= htmlspecialchars($upload['uploader']) ?>
                <i class="fas fa-clock" style="margin-left:15px;"></i> <?= date('Y-m-d H:i', strtotime($upload['created_at'])) ?>
            </p>

            <?php if (!empty($upload['file_path'])): ?>
                <div class="file-preview">
                    <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $upload['file_path'])): ?>
                        <img src="<?= htmlspecialchars($upload['file_path']) ?>"
                             alt="附件预览"
                             onclick="window.open(this.src)">
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($upload['file_path']) ?>"
                           target="_blank">
                            <i class="fas fa-paperclip"></i> 查看附件
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="content-text">
                <?= nl2br(htmlspecialchars(mb_substr($upload['content'], 0, 300))) ?>
                <?php if (mb_strlen($upload['content']) > 300): ?>
                    <span class="text-muted">[...]</span>
                <?php endif; ?>
            </div>

            <div class="review-form">
                <form action="process_review.php" method="POST">
                    <input type="hidden" name="upload_id" value="<?= $upload['upload_id'] ?>">
                    <button type="submit" name="action" value="approve"
                            class="approve-btn"
                            style="background:#28a745;color:white;padding:6px 12px;">
                        <i class="fas fa-check"></i> 通过
                    </button>
                    <button type="submit" name="action" value="reject"
                            class="reject-btn"
                            style="background:#dc3545;color:white;padding:6px 12px;margin-left:10px;">
                        <i class="fas fa-times"></i> 拒绝
                    </button>
                    <input type="text" name="reason"
                           placeholder="请输入拒绝原因（必填）"
                           required
                           style="width:300px;padding:6px 12px;">
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="pagination">
        <?php if ($total_pages > 1): ?>
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page-1 ?>"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            for ($i = $start; $i <= $end; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page+1 ?>"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        <?php endif; ?>
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
