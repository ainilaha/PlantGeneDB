<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
global $conn;
require_once __DIR__ . '/config.php';
require_admin();

// 安全获取上传ID
// 获取上传ID
$upload_id = isset($_GET['upload_id']) ? (int)$_GET['upload_id'] : 0;

// 获取上传详情（兼容注册表字段）
try {
    $query = "
        SELECT 
            u.upload_id,
            u.title,
            u.content,
            u.file_path,
            u.status,
            u.created_at,
            u.reviewed_at,
            u.review_reason,
            usr.id as user_id,
            usr.username,
            usr.email,
            usr.role,
            usr.created_at as user_created_at,
            usr.institution,
            usr.country,
            (SELECT COUNT(*) FROM uploads WHERE user_id = usr.id) as total_uploads
        FROM uploads u
        JOIN users usr ON u.user_id = usr.id
        WHERE u.upload_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $upload_id);
    $stmt->execute();
    $upload = $stmt->get_result()->fetch_assoc();

    if (!$upload) {
        throw new Exception("内容不存在或已被删除");
    }

    // 获取物种信息（匹配注册表结构）
    $species_query = "
        SELECT 
            scientific_name,
            common_name,
            genus,
            genome_type,
            genome_size,
            chromosome_number,
            gene_number,
            cds_number,
            species_description
        FROM species
        WHERE upload_id = ?
    ";
    $species_stmt = $conn->prepare($species_query);
    $species_stmt->bind_param("i", $upload_id);
    $species_stmt->execute();
    $species_info = $species_stmt->get_result()->fetch_assoc() ?? [];

    // 获取参考文献（匹配注册表结构）
    $reference_query = "
        SELECT 
            authors,
            publication_year,
            journal_name,
            volume_issue_pages,
            doi
        FROM referenced
        WHERE upload_id = ?
    ";
    $reference_stmt = $conn->prepare($reference_query);
    $reference_stmt->bind_param("i", $upload_id);
    $reference_stmt->execute();
    $reference_info = $reference_stmt->get_result()->fetch_assoc() ?? [];

} catch (Exception $e) {
    die($e->getMessage());
}

?>

    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars($upload['title']) ?> - 详情</title>
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
            .card-container {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .card {
                background-color: white;
                border-radius: 5px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                padding: 20px;
            }
            .card-header {
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
                margin-bottom: 15px;
                font-weight: bold;
            }
            .logout-btn {
                background: none;
                border: none;
                color: #007bff;
                cursor: pointer;
                padding: 5px 10px;
            }
            .logout-btn:hover {
                text-decoration: underline;
            }
            .detail-container {
                max-width: 800px;
                margin: 20px auto;
                padding: 30px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .file-preview {
                margin-top: 20px;
                border-top: 1px solid #eee;
                padding-top: 20px;
            }
            .preview-iframe {
                width: 100%;
                height: 600px;
                border: 1px solid #ddd;
                margin-top: 15px;
            }
            /* 用户信息卡片 */
            .user-profile {
                margin: 25px 0;
                border-top: 1px solid #eee;
                padding-top: 20px;
            }

            .profile-card {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }

            .profile-header {
                display: flex;
                align-items: center;
                margin-bottom: 15px;
            }

            .user-avatar {
                width: 45px;
                height: 45px;
                border-radius: 50%;
                background: #007bff;
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                margin-right: 15px;
            }

            .user-stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 15px;
                margin: 15px 0;
            }

            .stat-item {
                background: white;
                padding: 12px;
                border-radius: 6px;
                text-align: center;
            }

            .stat-item dt {
                color: #6c757d;
                font-size: 0.9em;
                margin-bottom: 5px;
            }

            .stat-item dd {
                font-weight: 500;
                margin: 0;
            }

            .user-actions {
                display: flex;
                gap: 10px;
                margin-top: 15px;
            }

            .action-link {
                color: #007bff;
                text-decoration: none;
                padding: 8px 12px;
                border: 1px solid #007bff;
                border-radius: 20px;
                transition: all 0.3s;
            }

            .action-link:hover {
                background: #007bff;
                color: white;
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

            /* 新增表格样式 */
            .info-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            .info-table th, .info-table td {
                padding: 12px 15px;
                border: 1px solid #ddd;
                text-align: left;
            }
            .info-table th {
                background-color: #f8f9fa;
                font-weight: bold;
            }
            .info-table tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            .info-table tr:hover {
                background-color: #e9ecef;
            }
            /* 拒绝理由显示样式 */
            .rejection-notice {
                background: #fff8f8;
                border-left: 4px solid #dc3545;
                padding: 20px;
                margin: 25px 0;
                border-radius: 4px;
            }

            .rejection-header {
                display: flex;
                align-items: center;
                margin-bottom: 15px;
            }

            .rejection-header i {
                color: #dc3545;
                font-size: 1.5em;
                margin-right: 12px;
            }

            .rejection-header h3 {
                color: #dc3545;
                margin: 0;
            }

            .rejection-content {
                color: #6c757d;
                line-height: 1.6;
                white-space: pre-wrap;
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
            <li><a href="review.php"  class="active"><i class="fas fa-box"></i>上传管理</a></li>
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
            <a href="<?= get_safe_referer() ?>" class="back-button">
                返回上一页
            </a>
        </div>
        <div class="detail-container">
            <h1><?= htmlspecialchars($upload['title']) ?></h1>

            <div class="meta-info">
                <p>🕒 上传时间：<?= $upload['created_at'] ?></p>
                <p>📌 当前状态：<?= get_status_badge($upload['status']) ?></p>
                <p>👤 上传者：<?= htmlspecialchars($upload['username']) ?> (<?= $upload['email'] ?>)</p>
            </div>

            <!-- 用户信息卡片 -->
            <div class="user-profile">
                <h3>上传者信息</h3>
                <div class="profile-card">
                    <div class="profile-header">
                        <span class="user-avatar"><?= strtoupper(mb_substr($upload['username'], 0, 1)) ?></span>
                        <div class="user-meta">
                            <h4><?= htmlspecialchars($upload['username']) ?></h4>
                            <p><?= $upload['email'] ?></p>
                        </div>
                    </div>

                    <dl class="user-stats">
                        <div class="stat-item">
                            <dt>注册时间</dt>
                            <dd><?= date('Y-m-d', strtotime($upload['user_created_at'])) ?></dd>
                        </div>
                        <div class="stat-item">
                            <dt>总上传量</dt>
                            <dd><?= $upload['total_uploads'] ?> 条</dd>
                        </div>
                    </dl>

                    <div class="user-actions">
                        <a href="user_uploads.php?user_id=<?= $upload['user_id'] ?>&from=detail&source_id=<?= $upload['upload_id'] ?>"
                           class="action-link">
                            📚 查看所有上传
                        </a>
                    </div>
                </div>
            </div>
            <!--拒绝理由-->
            <?php if ($upload['status'] === 'rejected' && !empty($upload['review_reason'])): ?>
                <div class="rejection-notice">
                    <div class="rejection-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>拒绝理由</h3>
                    </div>
                    <div class="rejection-content">
                        <?= nl2br(htmlspecialchars($upload['review_reason'])) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 内容详情 -->
            <div class="content-body">
                <h3>内容详情</h3>
                <div class="content-text">
                    <?= nl2br(htmlspecialchars($upload['content'])) ?>
                </div>
            </div>

            <!-- 物种信息表格 -->
            <h3>物种信息</h3>
            <table class="info-table">
                <tr>
                    <th>Scientific Name</th>
                    <td><?= htmlspecialchars($species_info['scientific_name'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Common Name</th>
                    <td><?= htmlspecialchars($species_info['common_name'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Genus</th>
                    <td><?= htmlspecialchars($species_info['genus'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Genome Type</th>
                    <td><?= htmlspecialchars($species_info['genome_type'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Genome Size</th>
                    <td><?= htmlspecialchars($species_info['genome_size'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Chromosome Number</th>
                    <td><?= htmlspecialchars($species_info['chromosome_number'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Gene Number</th>
                    <td><?= htmlspecialchars($species_info['gene_number'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>CDS Number</th>
                    <td><?= htmlspecialchars($species_info['cds_number'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Species Description</th>
                    <td><?= nl2br(htmlspecialchars($species_info['species_description'] ?? '-')) ?></td>
                </tr>
            </table>

            <!-- 参考文献信息表格 -->
            <h3>参考文献信息</h3>
            <table class="info-table">
                <tr>
                    <th>Authors</th>
                    <td><?= htmlspecialchars($reference_info['authors'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Publication Year</th>
                    <td><?= htmlspecialchars($reference_info['publication_year'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Journal Name</th>
                    <td><?= htmlspecialchars($reference_info['journal_name'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Volume/Issue/Pages</th>
                    <td><?= htmlspecialchars($reference_info['volume_issue_pages'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>DOI</th>
                    <td>
                        <a href="<?= htmlspecialchars($reference_info['doi'] ?? '#') ?>" target="_blank">
                            <?= htmlspecialchars($reference_info['doi'] ?? '-') ?>
                        </a>
                    </td>
                </tr>
            </table>
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
