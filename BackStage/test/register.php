<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
global $conn;
require 'config.php'; // 确保包含数据库连接配置

$error = '';

// 数据库表结构验证（确保已执行过之前的ALTER TABLE）
// CREATE TABLE users (... role字段已设置DEFAULT 'user' ...)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据
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

    try {
        // 验证密码匹配
        if ($password !== $confirmPassword) {
            throw new Exception("密码不一致，请重新输入");
        }

        // 密码强度验证（可选）
        if (strlen($password) < 8) {
            throw new Exception("密码至少需要8个字符");
        }

        // 哈希密码
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // 插入数据库（不包含role字段）
        $stmt = $conn->prepare("INSERT INTO users (
            username, 
            first_name, 
            last_name, 
            gender, 
            institution, 
            job_type, 
            email, 
            phone_prefix, 
            phone_number, 
            password_hash, 
            country
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sssssssssss",
            $username,
            $firstName,
            $lastName,
            $gender,
            $institution,
            $jobType,
            $email,
            $phonePrefix,
            $phoneNumber,
            $passwordHash,
            $country
        );

        if ($stmt->execute()) {
            $_SESSION['registration_success'] = true;
            header("Location: login_backStage.php");
            exit();
        } else {
            // 处理唯一约束错误
            if ($conn->errno === 1062) {
                throw new Exception("用户名或邮箱已被注册");
            }
            throw new Exception("注册失败: " . $conn->error);
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
    <title>Register Your Account!</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #3FBBC0 0%,  #e4f6ff 100%);
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            transform: translateY(-20px);
            transition: all 0.3s ease;
        }

        h1 {
            text-align: center;
            color: #2d3748;
            margin-bottom: 30px;
            font-size: 2em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 600;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .phone-group {
            display: flex;
            gap: 10px;
        }

        .phone-prefix {
            width: 120px;
        }

        .gender-group {
            display: flex;
            gap: 20px;
        }

        .gender-option {
            flex: 1;
            position: relative;
        }

        .gender-option input {
            display: none;
        }

        .gender-option label {
            display: block;
            padding: 12px;
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .gender-option input:checked + label {
            border-color: #667eea;
            background: #ebf4ff;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0aec0;
        }

        button {
            width: 100%;
            padding: 15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #3FBBC0;
        }
        a{
            color: #667eea;
        }
        .name-group {
            display: flex;
            gap: 10px;
        }
        .name-group input {
            flex: 1;
        }
        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Register Your Account!</h1>

    <?php if ($error): ?>
        <div style="color: red; margin-bottom: 15px; text-align: center;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form id="registerForm" method="POST" action="register.php">
        <!-- 用户名 -->
        <div class="form-group">
            <label for="name">Username</label>
            <input type="text" id="name" name="username" required
                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
        </div>

        <!-- 姓名 -->
        <div class="form-group">
            <label>Full Name</label>
            <div class="name-group">
                <input type="text" id="firstName" name="first_name" placeholder="First Name" required
                       value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '' ?>">
                <input type="text" id="lastName" name="last_name" placeholder="Last Name" required
                       value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '' ?>">
            </div>
        </div>

        <!-- 性别 -->
        <div class="form-group">
            <label>Gender</label>
            <div class="gender-group">
                <div class="gender-option">
                    <input type="radio" name="gender" id="male" value="male" checked>
                    <label for="male">Male</label>
                </div>
                <div class="gender-option">
                    <input type="radio" name="gender" id="female" value="female">
                    <label for="female">Female</label>
                </div>
            </div>
        </div>

        <!-- 机构 -->
        <div class="form-group">
            <label for="institution">Institution</label>
            <input type="text" id="institution" name="institution" required
                   value="<?php echo isset($_POST['institution']) ? htmlspecialchars($_POST['institution']) : '' ?>">
        </div>

        <!-- 职业 -->
        <div class="form-group">
            <label for="jobType">Job Type</label>
            <select id="jobType" name="job_type" required>
                <option value="">Please choose</option>
                <option value="Student" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'Student') ? 'selected' : '' ?>>Student</option>
                <option value="Teacher" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'Teacher') ? 'selected' : '' ?>>Teacher</option>
                <option value="Researcher" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'Researcher') ? 'selected' : '' ?>>Researcher</option>
                <option value="Engineer" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'Engineer') ? 'selected' : '' ?>>Engineer</option>
                <option value="Other" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'Other') ? 'selected' : '' ?>>Other</option>
            </select>
        </div>

        <!-- 邮箱 -->
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        </div>

        <!-- 电话号码 -->
        <div class="form-group">
            <label>Phone number</label>
            <div class="phone-group">
                <select class="phone-prefix" name="phone_prefix">
                    <option value="+86" <?php echo (isset($_POST['phone_prefix']) && $_POST['phone_prefix'] === '+86' ? 'selected' : '' )?>>+86</option>
                    <option value="+1" <?php echo (isset($_POST['phone_prefix']) && $_POST['phone_prefix'] === '+1' ? 'selected' : '' )?>>+1</option>
                    <option value="+81" <?php echo (isset($_POST['phone_prefix']) && $_POST['phone_prefix'] === '+81' ? 'selected' : '' )?>>+81</option>
                    <option value="+82" <?php echo (isset($_POST['phone_prefix']) && $_POST['phone_prefix'] === '+82' ? 'selected' : '' )?>>+82</option>
                    <option value="+852" <?php echo (isset($_POST['phone_prefix']) && $_POST['phone_prefix'] === '+852' ? 'selected' : '' )?>>+852</option>
                </select>
                <input type="tel" name="phone_number" pattern="[0-9]{10,11}" required
                       value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : '' ?>">
            </div>
        </div>

        <!-- 密码 -->
        <div class="form-group">
            <label for="password">Password</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required minlength="8">
                <span class="toggle-password" onclick="togglePassword()">?</span>
            </div>
        </div>

        <!-- 确认密码 -->
        <div class="form-group">
            <label for="confirmPassword">Confirm Password</label>
            <div class="password-container">
                <input type="password" id="confirmPassword" name="confirm_password" required minlength="8">
                <span class="toggle-password" onclick="toggleConfirmPassword()">?</span>
            </div>
        </div>

        <!-- 国家 -->
        <div class="form-group">
            <label for="country">Country or Region</label>
            <select id="country" name="country" required>
                <option value="">Please choose</option>
                <option value="China" <?php echo (isset($_POST['country']) && $_POST['country'] === 'China') ? 'selected' : '' ?>>China</option>
                <option value="Japan" <?php echo (isset($_POST['country']) && $_POST['country'] === 'Japan') ? 'selected' : '' ?>>Japan</option>
                <option value="Britain" <?php echo (isset($_POST['country']) && $_POST['country'] === 'Britain') ? 'selected' : '' ?>>Britain</option>
                <option value="Russian" <?php echo (isset($_POST['country']) && $_POST['country'] === 'Russian') ? 'selected' : '' ?>>Russian</option>
                <option value="United States of America" <?php echo (isset($_POST['country']) && $_POST['country'] === 'United States of America') ? 'selected' : '' ?>>United States of America</option>
            </select>
        </div>

        <button type="submit">Register</button>
        <br>
        <a href="login_backStage.php">Back to login</a>
    </form>
</div>

<script>
    // 保持原有JavaScript功能
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
    }

    function toggleConfirmPassword() {
        const confirmPasswordField = document.getElementById('confirmPassword');
        const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordField.setAttribute('type', type);
    }

    // 前端验证增强
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('两次密码输入不一致');
            return;
        }
    });
</script>
</body>
</html>