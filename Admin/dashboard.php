<?php
global $conn;
session_start(); // 必须位于文件最开头
require 'config.php';
require_admin(); // 管理员权限检查

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login_admin.php");
    exit();
}

// 获取用户信息
$user_id = $_SESSION['user_id'];
$query = "SELECT username, role FROM users WHERE id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("查询预处理失败: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("查询失败: " . $conn->error);
}

$user = $result->fetch_assoc();
if (!$user) {
    die("用户信息获取失败");
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理系统 - 仪表盘</title>
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

    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

</head>
<body>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>后台管理系统</h2>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i>数据概括</a></li>
        <li><a href="users.php"><i class="fas fa-users"></i>用户管理</a></li>
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
        <h3>数据概括</h3>
        <div class="user-info">
            <span><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</span>
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn">退出登录</button>
            </form>
        </div>
    </div>

    <div class="card-container">
        <div class="card">
            <div class="card-header">
                👥 用户统计
            </div>
            <p>总用户数: 125</p>
            <p>今日新增: 5</p>
        </div>

        <div class="card">
            <div class="card-header">
                🌿 植物种类
            </div>
            <p>1243</p>
        </div>

        <div class="card">
            <div class="card-header">
                🧬 基因序列
            </div>
            <p>25638</p>
        </div>


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