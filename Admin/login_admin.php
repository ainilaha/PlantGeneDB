<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
global $conn;
require 'config.php';
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        // 使用预处理语句防止SQL注入
        $query = "SELECT id, username, password_hash AS password, role 
                 FROM users
                 WHERE username = ? OR email = ? 
                 LIMIT 1";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("预处理失败：" . $conn->error);
        }

        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            throw new Exception("用户不存在");
        }

        $user = $result->fetch_assoc();

        if (!password_verify($password, $user['password'])) {
            throw new Exception("密码验证失败");
        }

        // 设置会话安全参数
        session_regenerate_id(true);
        $_SESSION = [
            'user_id' => (int)$user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ];

        header("Location: dashboard.php");
        exit();

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
    <title>Login to Your Account</title>
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
            background: linear-gradient(135deg, #3FBBC0 0%,  #FFEEE4 100%);
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
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
            margin-bottom: 25px;
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

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 20px;
            opacity: 0;
            margin-top: 10px;
            transition: opacity 0.3s ease;
        }

        .loading.show {
            opacity: 1;
        }

        .loading::before {
            content: '';
            width: 20px;
            height: 20px;
            margin-right: 5px;
            background: #2d3748;
            border-radius: 50%;
            animation: loading 1s ease-in-out infinite;
        }
        .brand-header {
            position: fixed;
            top: 25px;
            left: 30px;
            z-index: 1000;
            text-align: left;
        }

        .brand-title {
            font-size: 2em;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 5px;
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }

        .brand-tagline {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.95em;
            line-height: 1.3;
            max-width: 300px;
        }

        /* 调整容器位置 */
        .container {
            margin-top: 80px;
        }

        @media (max-width: 768px) {
            .brand-header {
                top: 15px;
                left: 20px;
            }
            .brand-title {
                font-size: 1.6em;
            }
            .brand-tagline {
                font-size: 0.85em;
                max-width: 200px;
            }
            .container {
                margin-top: 100px;
            }
        }

        @keyframes loading {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div class="brand-header">
    <div class="brand-title">Back-stage</div>
    <div class="brand-tagline"><strong>Management</strong></div>
</div>
<div class="container">
    <h1>Login to Your Account</h1>

    <?php if ($error): ?>
        <div style="color: red; margin-bottom: 15px; text-align: center;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form id="loginForm" method="POST" action="login_admin.php">
        <div class="form-group">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <span class="toggle-password" onclick="togglePassword()">?</span>
            </div>
        </div>

        <button type="submit">Login</button>

        <div class="loading" id="loading">Loading...</div>


    </form>
</div>

<script>
    // 修改后的JavaScript验证
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
    }

    const form = document.getElementById('loginForm');
    const loading = document.getElementById('loading');
    let isLoading = false;

    form.addEventListener('submit', (e) => {
        // 前端基本验证
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        if (!username || !password) {
            e.preventDefault();
            alert('请填写用户名和密码');
            return;
        }

        // 显示加载状态
        isLoading = true;
        loading.classList.add('show');
        loading.style.opacity = 1;
    });
</script>
</body>
</html>
