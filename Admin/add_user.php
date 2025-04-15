<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
global $conn;
require_once __DIR__ . '/config.php';
require_admin();

// 权限检查
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_admin.php");
    exit();
}

$error = '';
$success = '';
$user = ['username' => '未知用户', 'role' => '未知角色'];

try {
    // 获取当前用户信息
    $stmt = $conn->prepare("SELECT username, role FROM users WHERE id = ?");
    if (!$stmt) {
        throw new Exception("预处理失败: " . $conn->error);
    }
    $stmt->bind_param("i", $_SESSION['user_id']);
    if (!$stmt->execute()) {
        throw new Exception("执行失败: " . $stmt->error);
    }
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("获取结果失败");
    }
    $fetched_user = $result->fetch_assoc();
    if (!$fetched_user) {
        throw new Exception("用户不存在");
    }
    $user = $fetched_user; // 覆盖初始化值
    $stmt->close();
} catch (Exception $e) {
    // 清除会话并重定向
    session_unset();
    session_destroy();
    header("Location: login_backStage.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 接收所有表单字段
    $username = trim($_POST['username']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $gender = $_POST['gender'];
    $institution = trim($_POST['institution']);
    $jobType = $_POST['job_type'];
    $email = trim($_POST['email']);
    $phonePrefix = $_POST['phone_prefix'];
    $phoneNumber = $_POST['phone_number'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $country = $_POST['country'];
    $role = $_POST['role'];


    try {
        // 字段验证
        $requiredFields = [
            'username' => '用户名',
            'first_name' => '名字',
            'last_name' => '姓氏',
            'gender' => '性别',
            'institution' => '机构',
            'job_type' => '职业类型',
            'email' => '邮箱',
            'phone_prefix' => '电话区号',
            'phone_number' => '电话号码',
            'password' => '密码',
            'country' => '国家/地区'
        ];

        foreach ($requiredFields as $field => $name) {
            if (empty($_POST[$field])) {
                throw new Exception("{$name}不能为空");
            }
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("邮箱格式不正确");
        }

        if ($password !== $confirmPassword) {
            throw new Exception("两次密码输入不一致");
        }

        if (strlen($password) < 8) {
            throw new Exception("密码至少需要8个字符");
        }

        if (!preg_match('/^[0-9]{10,11}$/', $phoneNumber)) {
            throw new Exception("电话号码格式不正确");
        }

        // 检查唯一性
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            throw new Exception("用户名或邮箱已被注册");
        }

        // 插入数据库
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (
            username, first_name, last_name, gender, institution, 
            job_type, email, phone_prefix, phone_number, 
            password_hash, country, role
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "ssssssssssss",
            $username,
            $firstName,
            $lastName,
            $gender,
            $institution,
            $jobType,
            $email,
            $phonePrefix,
            $phoneNumber,
            $hashedPassword,
            $country,
            $role
        );

        if ($stmt->execute()) {
            $success = '用户添加成功';
            // 清空表单
            $_POST = array();
        } else {
            throw new Exception("添加失败: " . $conn->error);
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理系统 - 添加用户</title>
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

        .form-container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #218838;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-row > div {
            flex: 1;
        }
        .gender-group {
            display: flex;
            gap: 20px;
            margin-top: 5px;
        }
        .gender-option {
            flex: 1;
        }
        .gender-option input[type="radio"] {
            display: none;
        }
        .gender-option label {
            display: block;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
        }
        .gender-option input:checked + label {
            border-color: #667eea;
            background: #ebf4ff;
        }
    </style>
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
        <li><a href="orders.php"><i class="fas fa-shopping-cart"></i>基因数据</a></li>
        <li><a href="settings.php"><i class="fas fa-cog"></i>系统设置</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h3>添加用户</h3>
        <div class="user-info">
            <span><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</span>
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn">退出登录</button>
            </form>
        </div>
    </div>

    <div class="form-container">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">邮箱</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">名字</label>
                    <input type="text" id="first_name" name="first_name"
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">姓氏</label>
                    <input type="text" id="last_name" name="last_name"
                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>性别</label>
                <div class="gender-group">
                    <div class="gender-option">
                        <input type="radio" name="gender" id="male" value="male"
                            <?= ($_POST['gender'] ?? '') === 'male' ? 'checked' : '' ?> required>
                        <label for="male">男</label>
                    </div>
                    <div class="gender-option">
                        <input type="radio" name="gender" id="female" value="female"
                            <?= ($_POST['gender'] ?? '') === 'female' ? 'checked' : '' ?>>
                        <label for="female">女</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="institution">机构</label>
                <input type="text" id="institution" name="institution"
                       value="<?= htmlspecialchars($_POST['institution'] ?? '') ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="job_type">职业类型</label>
                    <select id="job_type" name="job_type" required>
                        <option value="">请选择</option>
                        <option value="Student" <?= ($_POST['job_type'] ?? '') === 'Student' ? 'selected' : '' ?>>学生</option>
                        <option value="Teacher" <?= ($_POST['job_type'] ?? '') === 'Teacher' ? 'selected' : '' ?>>教师</option>
                        <option value="Researcher" <?= ($_POST['job_type'] ?? '') === 'Researcher' ? 'selected' : '' ?>>研究员</option>
                        <option value="Engineer" <?= ($_POST['job_type'] ?? '') === 'Engineer' ? 'selected' : '' ?>>工程师</option>
                        <option value="Other" <?= ($_POST['job_type'] ?? '') === 'Other' ? 'selected' : '' ?>>其他</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="country">国家/地区</label>
                    <select id="country" name="country" required>
                        <option value="">请选择</option>
                        <option value="China" <?= ($_POST['country'] ?? '') === 'China' ? 'selected' : '' ?>>中国</option>
                        <option value="Japan" <?= ($_POST['country'] ?? '') === 'Japan' ? 'selected' : '' ?>>日本</option>
                        <option value="Britain" <?= ($_POST['country'] ?? '') === 'Britain' ? 'selected' : '' ?>>英国</option>
                        <option value="Russian" <?= ($_POST['country'] ?? '') === 'Russian' ? 'selected' : '' ?>>俄罗斯</option>
                        <option value="United States of America" <?= ($_POST['country'] ?? '') === 'United States of America' ? 'selected' : '' ?>>美国</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>电话号码</label>
                    <div class="phone-group" style="display: flex; gap: 10px;">
                        <select name="phone_prefix" style="width: 100px;" required>
                            <option value="+86" <?= ($_POST['phone_prefix'] ?? '') === '+86' ? 'selected' : '' ?>>+86</option>
                            <option value="+1" <?= ($_POST['phone_prefix'] ?? '') === '+1' ? 'selected' : '' ?>>+1</option>
                            <option value="+81" <?= ($_POST['phone_prefix'] ?? '') === '+81' ? 'selected' : '' ?>>+81</option>
                            <option value="+82" <?= ($_POST['phone_prefix'] ?? '') === '+82' ? 'selected' : '' ?>>+82</option>
                            <option value="+852" <?= ($_POST['phone_prefix'] ?? '') === '+852' ? 'selected' : '' ?>>+852</option>
                        </select>
                        <input type="tel" name="phone_number"
                               value="<?= htmlspecialchars($_POST['phone_number'] ?? '') ?>"
                               pattern="[0-9]{10,11}" placeholder="请输入10-11位数字" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">确认密码</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>

            <div class="form-group">
                <label for="role">用户角色</label>
                <select id="role" name="role" required>
                    <option value="user" <?= ($_POST['role'] ?? '') === 'user' ? 'selected' : '' ?>>普通用户</option>
                    <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>管理员</option>
                </select>
            </div>

            <button type="submit"><i class="fas fa-save"></i> 保存用户</button>
            <a href="users.php" style="margin-left: 15px;">返回列表</a>
        </form>
    </div>
</div>
</body>
</html>