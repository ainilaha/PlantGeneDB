<?php
// genomics_manage.php
session_start();
require __DIR__ . '/config.php';
global $conn;
require_admin();

// 初始化消息变量
$success = '';
$error = '';

$user_id = $_SESSION['user_id'];
$query = "SELECT username, role FROM users WHERE id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query failed: " . $conn->error);
}

$user = $result->fetch_assoc();
if (!$user) {
    die("User information retrieval failed");
}

$stmt->close();

// 仅在不存在时生成 CSRF 令牌
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 分页设置
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// 搜索处理
$search = isset($_GET['search']) ? "%{$_GET['search']}%" : '%';
$where = "WHERE scientific_name LIKE ? OR common_name LIKE ?";
$params = [$search, $search];
$param_types = "ss";

// 获取总记录数
$count_sql = "SELECT COUNT(*) FROM genomics_species $where";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($param_types, ...$params);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_row()[0];
$total_pages = ceil($total / $per_page);

// 获取数据
$sql = "SELECT * FROM genomics_species $where 
        ORDER BY created_at DESC 
        LIMIT $per_page OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// 删除处理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    try {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Security verification failed");
        }

        $delete_id = (int)$_POST['delete_id'];
        $delete_stmt = $conn->prepare("DELETE FROM genomics_species WHERE id = ?");
        $delete_stmt->bind_param("i", $delete_id);
        if ($delete_stmt->execute()) {
            $success = "Record deleted successfully";
            // 刷新当前页数据
            header("Location: genomics_content.php?page=$page");
            exit();
        }
    } catch (Exception $e) {
        $error = "Delete failed: " . $e->getMessage();
    }
}

// 辅助函数：清理HTML，保留必要格式
function clean_html_for_display($html) {
    // 移除多余空格和换行
    $html = preg_replace('/\s+/', ' ', $html);
    // 移除Word特有的MsoNormal类
    $html = str_replace('class="MsoNormal"', '', $html);
    // 移除无用的空格实体
    $html = str_replace('&nbsp;', ' ', $html);
    // 移除多余空白
    $html = trim($html);
    return $html;
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>基因组数据管理</title>
    <link rel="stylesheet" href="./assets/css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

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
        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #fff;
            margin: 0;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
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
        .form-group label {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }

        .upload-zone h6 {
            margin: 0 0 8px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .upload-zone small {
            display: block;
            margin-top: 0.3rem;
            color: #6c757d;
            font-size: 0.75rem;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
        .has-submenu .submenu {
            display: none;
            list-style: none;
            padding-left: 30px;
            margin: 5px 0;
            background-color: #2c3338;
            transition: all 0.3s;
        }
        .has-submenu.active .submenu {
            display: block;
        }
        .submenu li a {
            padding: 8px 15px;
            font-size: 0.9rem;
            color: #9a9da0;
            display: block;
            transition: all 0.2s;
        }
        .submenu li a:hover,
        .submenu li a.active-sub {
            color: #fff;
            background-color: #495057;
        }
        .menu-toggle::after {
            content: "▸";
            float: right;
            margin-left: 10px;
            transition: transform 0.3s;
        }
        .has-submenu.active .menu-toggle::after {
            transform: rotate(90deg);
        }
        .data-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .table-responsive {
            min-height: 400px;
        }
        .action-btns .btn {
            margin: 0 3px;
            padding: 5px 10px;
        }
        .search-box {
            max-width: 300px;
            margin-bottom: 20px;
        }
        .pagination {
            margin-top: 20px;
        }
        /* 添加样式，将表格内容设为不可选择，并移除鼠标手型指针 */
        .table-content {
            user-select: none;
            cursor: default;
        }
        /* 设置学名显示样式 */
        .scientific-name {
            font-style: normal; /* 默认不倾斜 */
        }
        .scientific-name em {
            font-style: italic; /* 保留em标签的斜体效果 */
        }
    </style>
    <link rel="stylesheet" href="./assets/css/admin.css">
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>后台管理系统</h2>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>数据概括</a></li>
        <li><a href="users.php"><i class="fas fa-users"></i>用户管理</a></li>
        <li><a href="review.php"><i class="fas fa-box"></i>上传管理</a></li>
        <li class="has-submenu">
            <a href="javascript:void(0);" class="menu-toggle"><i class="fas fa-dna"></i>数据管理</a>
            <ul class="submenu">
                <li><a href="genomics_content.php" class="active-sub">Genomics</a></li>
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
        <h3>数据管理</h3>
        <div class="user-info">
            <span><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</span>
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn">退出登录</button>
            </form>
        </div>
    </div>
    <div class="container-fluid mt-4">
        <?php if($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="data-table p-3">
            <!-- 搜索框 -->
            <form class="search-box">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="搜索物种或通用名..."
                           name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>

            <!-- 数据表格 -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>学名</th>
                        <th>通用名</th>
                        <th>属</th>
                        <th>基因组类型</th>
                        <th>上传时间</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="table-content"><?= $row['id'] ?></td>
                            <td class="table-content scientific-name"><?= clean_html_for_display($row['scientific_name']) ?></td>
                            <td class="table-content"><?= htmlspecialchars($row['common_name']) ?></td>
                            <td class="table-content"><?= htmlspecialchars($row['genus']) ?></td>
                            <td class="table-content"><?= htmlspecialchars($row['genome_type']) ?></td>
                            <td class="table-content"><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
                            <td class="action-btns">
                                <a href="genomics_edit.php?id=<?= $row['id'] ?>"
                                   class="btn btn-sm btn-warning" title="编辑">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="csrf_token"
                                           value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="delete_id"
                                           value="<?= $row['id'] ?>">
                                    <button type="submit" name="delete"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('确定删除此记录？')"
                                            title="删除">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- 分页 -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?=
                            urlencode($_GET['search'] ?? '') ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
    // 菜单切换逻辑
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.menu-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const parent = this.closest('.has-submenu');
                parent.classList.toggle('active');
                document.querySelectorAll('.has-submenu').forEach(other => {
                    if (other !== parent) {
                        other.classList.remove('active');
                    }
                });
            });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.has-submenu')) {
                document.querySelectorAll('.has-submenu').forEach(menu => {
                    menu.classList.remove('active');
                });
            }
        });

        document.querySelectorAll('.submenu a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
        
        // 移除表格行点击事件，防止点击行时跳转到详情页
        // 原代码被删除
    });
</script>
</body>
</html>