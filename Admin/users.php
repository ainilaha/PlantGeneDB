<?php
session_start();
global $conn;
require_once __DIR__ . '/config.php';
require_admin();

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login_admin.php");
    exit();
}

// 检查权限
if ($_SESSION['role'] !== 'admin') {
    die("无权访问此页面");
}

// 获取用户列表（包含所有字段）
$query = "SELECT 
    id, username, first_name, last_name, gender, 
    institution, job_type, email, phone_prefix, 
    phone_number, country, role, created_at 
    FROM users ORDER BY created_at DESC";
$result = $conn->query($query);
$users = $result->fetch_all(MYSQLI_ASSOC) ?: [];

// 获取当前用户信息（包含所有字段）
$current_user_query = "SELECT 
    username, first_name, last_name, role 
    FROM users WHERE id = ?";
$stmt = $conn->prepare($current_user_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$current_user_result = $stmt->get_result();
$current_user = $current_user_result->fetch_assoc();


// 处理删除请求
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    if ($delete_id != $_SESSION['user_id']) {
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete_stmt->bind_param("i", $delete_id);

        if ($delete_stmt->execute()) {
            header("Location: users.php");
            exit();
        } else {
            die("删除失败: " . $conn->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理系统 - 用户管理</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .action-btns a {
            color: #007bff;
            margin-right: 10px;
            text-decoration: none;
        }
        .action-btns a.delete {
            color: #dc3545;
        }
        .add-user-btn {
            display: inline-block;
            padding: 8px 15px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .add-user-btn:hover {
            background-color: #218838;
        }
        .compact-column {
            max-width: 120px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .phone-number {
            font-family: monospace;
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
    <div class="header">
        <h3>用户管理</h3>
        <div class="user-info">
            <span><?= htmlspecialchars($current_user['username']) ?>
             (<?= htmlspecialchars($current_user['role']) ?>)</span>
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn">退出登录</button>
            </form>
        </div>
    </div>

    <a href="add_user.php" class="add-user-btn" style="margin-top: 10px"><i class="fas fa-plus"></i> 添加用户</a>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>用户名</th>
            <th class="compact-column">姓名</th>
            <th>性别</th>
            <th>机构</th>
            <th>职业类型</th>
            <th>邮箱</th>
            <th>联系电话</th>
            <th>国家/地区</th>
            <th>角色</th>
            <th>注册时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td class="compact-column">
                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                </td>
                <td><?= ($user['gender'] === 'male') ? '男' : '女' ?></td>
                <td class="compact-column"><?= htmlspecialchars($user['institution']) ?></td>
                <td><?= htmlspecialchars($user['job_type']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td class="phone-number">
                    <?= htmlspecialchars($user['phone_prefix'] . $user['phone_number']) ?>
                </td>
                <td><?= htmlspecialchars($user['country']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td><?= htmlspecialchars($user['created_at']) ?></td>
                <td class="action-btns">
                    <a href="edit_user.php?id=<?= $user['id'] ?>"><i class="fas fa-edit"></i> 编辑</a>
                    <a href="users.php?delete=<?= $user['id'] ?>" class="delete"
                       onclick="return confirm('确定要删除此用户吗？')">
                        <i class="fas fa-trash"></i> 删除
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
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