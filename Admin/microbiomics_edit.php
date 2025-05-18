<?php
session_start();
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
global $conn;
error_reporting(E_ALL);
ini_set('display_errors', 0); // 关闭直接显示错误，防止破坏JSON响应

function sanitize_html($html) {
    if (empty($html)) {
        return '';
    }
    $allowed_tags = '<p><br><a><em><i><strong><b><ul><ol><li><h1><h2><h3><h4><h5><h6><span><div><table><tr><td><th><thead><tbody><blockquote>';
    $html = preg_replace('/<([^>]*)(?:onclick|onload|onerror|onmouseover|onmouseout|onkeydown|onkeypress|onkeyup)([^>]*)>/i', '<$1$2>', $html);
    $html = strip_tags($html, $allowed_tags);
    return $html !== false ? $html : '';
}

require __DIR__ . '/config.php';
require_admin();

// 初始化消息变量
$success = '';
$error = '';

// 获取用户信息
$user_id = $_SESSION['user_id'];
$query = "SELECT username, role FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("查询准备失败: " . $conn->error);
    die("查询准备失败: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
    error_log("用户信息获取失败");
    die("用户信息获取失败");
}
$stmt->close();

// 生成 CSRF 令牌
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 获取要编辑的记录
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = "SELECT * FROM Microbiomics WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$record = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$record) {
    die("记录未找到");
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_start();
    error_log('表单提交: ' . print_r($_POST, true));
    try {
        // 验证 CSRF 令牌
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("安全验证失败，请重新提交");
        }

        // 净化富文本内容
        if (isset($_POST['article_overview'])) {
            $_POST['article_overview'] = sanitize_html($_POST['article_overview']);
            error_log('净化后的文章概述: ' . $_POST['article_overview']);
        }

        // 验证表单数据
        $fields = [
            'species' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'article_overview' => [
                'filter' => FILTER_CALLBACK,
                'options' => function($value) {
                    return $value !== null && $value !== false ? $value : '';
                }
            ],
            'family_of_endophyte_fungi' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'genus_of_endophyte_fungi' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'species_of_endophyte_fungi' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'genome_of_endophyte_fungi' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'microbiome_of_endophyte_fungi' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'tissue' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'biotic_stress' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'abiotic_stress' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'source' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'link' => FILTER_SANITIZE_URL
        ];

        $form_data = filter_input_array(INPUT_POST, $fields, true) ?? [];
        if ($form_data === null || in_array(null, $form_data, true)) {
            throw new Exception("表单数据验证失败");
        }

        $form_data = array_map(function($value) {
            return $value ?? '';
        }, $form_data);

        error_log('处理后的表单数据: ' . print_r($form_data, true));

        // 更新数据库
        $conn->begin_transaction();
        $stmt = $conn->prepare("UPDATE Microbiomics SET 
            Species = ?, Article_Overview = ?, Family_of_endophyte_fungi = ?, 
            Genus_of_endophyte_fungi = ?, Species_of_endophyte_fungi = ?, 
            Genome_of_endophyte_fungi = ?, Microbiome_of_endophyte_fungi = ?, 
            Tissue = ?, Biotic_stress = ?, Abiotic_stress = ?, Source = ?, Link = ?
            WHERE id = ?");

        if (!$stmt) {
            throw new Exception("SQL准备错误: " . $conn->error);
        }

        $stmt->bind_param("ssssssssssssi",
            $form_data['species'],
            $form_data['article_overview'],
            $form_data['family_of_endophyte_fungi'],
            $form_data['genus_of_endophyte_fungi'],
            $form_data['species_of_endophyte_fungi'],
            $form_data['genome_of_endophyte_fungi'],
            $form_data['microbiome_of_endophyte_fungi'],
            $form_data['tissue'],
            $form_data['biotic_stress'],
            $form_data['abiotic_stress'],
            $form_data['source'],
            $form_data['link'],
            $id
        );

        if (!$stmt->execute()) {
            throw new Exception("数据库错误: " . $stmt->error);
        }

        $conn->commit();
        $success = "数据更新成功！ID: " . $id;

        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => $success]);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        error_log('更新错误: ' . $e->getMessage());
        $error = "更新失败: " . $e->getMessage();

        ob_clean();
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑微生物组数据</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.tiny.cloud/1/17ulot83qpdv0de56wq31hm49zthms8q06rwv9cu8itx55es/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
        .upload-form {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
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
        .form-group.article-overview,
        .form-group.link {
            grid-column: 1 / -1;
        }
        .submit-btn {
            background: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            width: 100%;
            font-size: 16px;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .submit-btn:hover {
            background: #45a049;
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
        .global-alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            padding: 15px 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            font-size: 1rem;
            font-weight: 500;
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
        .tox-tinymce {
            border-radius: 4px !important;
            border-color: #ced4da !important;
        }
        .tox .tox-toolbar, .tox .tox-toolbar__primary {
            background-color: #f8f9fa !important;
        }
        .tox .tox-tbtn {
            border-radius: 4px !important;
        }
        .tox .tox-tbtn:hover {
            background-color: #e9ecef !important;
        }
        .form-group.article-overview, .form-group.link {
            margin-bottom: 1.5rem;
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
        <li><a href="users.php"><i class="fas fa-users"></i>用户管理</a></li>
        <li><a href="review.php"><i class="fas fa-box"></i>上传管理</a></li>
        <li class="has-submenu">
            <a href="javascript:void(0);" class="menu-toggle"><i class="fas fa-dna"></i>数据管理</a>
            <ul class="submenu">
                <li><a href="genomics_content.php">Genomics</a></li>
                <li><a href="microbiomics_content.php" class="active-sub">Microbiomics</a></li>
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
        <h3>编辑微生物组数据</h3>
        <div class="user-info">
            <span><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</span>
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn">退出登录</button>
            </form>
        </div>
    </div>

    <div class="upload-form">
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form id="uploadForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <h5>微生物组信息</h5>
            <div class="form-grid">
                <div class="form-group">
                    <label>Species *</label>
                    <input type="text" name="species" value="<?php echo htmlspecialchars($record['Species']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Family of endophyte fungi</label>
                    <input type="text" name="family_of_endophyte_fungi" value="<?php echo htmlspecialchars($record['Family_of_endophyte_fungi']); ?>">
                </div>
                <div class="form-group">
                    <label>Genus of endophyte fungi</label>
                    <input type="text" name="genus_of_endophyte_fungi" value="<?php echo htmlspecialchars($record['Genus_of_endophyte_fungi']); ?>">
                </div>
                <div class="form-group">
                    <label>Species of endophyte fungi</label>
                    <input type="text" name="species_of_endophyte_fungi" value="<?php echo htmlspecialchars($record['Species_of_endophyte_fungi']); ?>">
                </div>
                <div class="form-group">
                    <label>Genome of endophyte fungi</label>
                    <input type="text" name="genome_of_endophyte_fungi" value="<?php echo htmlspecialchars($record['Genome_of_endophyte_fungi']); ?>">
                </div>
                <div class="form-group">
                    <label>Microbiome of endophyte fungi</label>
                    <input type="text" name="microbiome_of_endophyte_fungi" value="<?php echo htmlspecialchars($record['Microbiome_of_endophyte_fungi']); ?>">
                </div>
                <div class="form-group">
                    <label>Tissue</label>
                    <input type="text" name="tissue" value="<?php echo htmlspecialchars($record['Tissue']); ?>">
                </div>
                <div class="form-group">
                    <label>Biotic stress</label>
                    <input type="text" name="biotic_stress" value="<?php echo htmlspecialchars($record['Biotic_stress']); ?>">
                </div>
                <div class="form-group">
                    <label>Abiotic stress</label>
                    <input type="text" name="abiotic_stress" value="<?php echo htmlspecialchars($record['Abiotic_stress']); ?>">
                </div>
                <div class="form-group">
                    <label>Source</label>
                    <input type="text" name="source" value="<?php echo htmlspecialchars($record['Source']); ?>">
                </div>
                <div class="form-group link">
                    <label>Link</label>
                    <input type="url" name="link" value="<?php echo htmlspecialchars($record['Link']); ?>">
                </div>
                <div class="form-group article-overview">
                    <label>Article overview</label>
                    <textarea name="article_overview" id="editor_article_overview"><?php echo htmlspecialchars($record['Article_Overview']); ?></textarea>
                </div>
            </div>

            <button type="submit" class="submit-btn">保存更改</button>
        </form>
    </div>
</div>

<script>
    // 初始化 TinyMCE 编辑器，仅用于文章概述
    tinymce.init({
        selector: '#editor_article_overview',
        plugins: 'anchor autolink charmap codesample emoticons link lists searchreplace visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link | align lineheight | numlist bullist indent outdent | removeformat',
        menubar: false,
        height: 300,
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 16px; }',
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
                console.log('文章概述已更改:', editor.getContent());
            });
        }
    });

    // 显示全局成功提示的函数
    function showGlobalSuccessMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'global-alert alert-success';
        alertDiv.textContent = message;
        document.body.appendChild(alertDiv);
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // 显示成功提示的函数
    function showSuccessMessage(message) {
        const successDiv = document.createElement('div');
        successDiv.className = 'alert alert-success';
        successDiv.textContent = message;
        document.querySelector('.upload-form').prepend(successDiv);
        setTimeout(() => {
            successDiv.remove();
        }, 5000);
    }

    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('提交表单...');

        // 验证必填字段
        const species = document.querySelector('input[name="species"]').value;
        if (!species.trim()) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.textContent = '请填写物种名称字段';
            this.prepend(errorDiv);
            return;
        }

        // 确保 TinyMCE 编辑器的内容已保存到表单
        tinymce.triggerSave();

        // 记录富文本内容，便于调试
        console.log('文章概述:', document.getElementById('editor_article_overview').value);

        const form = this;
        const formData = new FormData(form);

        // 输出 FormData 内容用于调试
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }

        // 移除任何现有的错误提示
        const existingAlerts = form.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'microbiomics_edit.php?id=<?php echo $id; ?>', true);

        xhr.onload = function() {
            console.log('响应状态:', xhr.status);
            console.log('响应内容:', xhr.responseText);

            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        showGlobalSuccessMessage('成功更新');
                        showSuccessMessage('数据更新成功！ID: ' + <?php echo $id; ?>);
                        setTimeout(() => {
                            window.location.href = 'microbiomics_content.php';
                        }, 2000);
                    } else {
                        throw new Error(response.message || '更新失败');
                    }
                } catch (e) {
                    console.error('JSON解析错误:', e);
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger';
                    errorDiv.textContent = '表单更新失败: ' + (e.message || '服务器返回无效数据');
                    if (xhr.responseText.length > 0) {
                        const responsePreview = xhr.responseText.substring(0, 200) + (xhr.responseText.length > 200 ? '...' : '');
                        const debugInfo = document.createElement('div');
                        debugInfo.className = 'mt-2 text-muted small';
                        debugInfo.textContent = '服务器响应: ' + responsePreview;
                        errorDiv.appendChild(debugInfo);
                    }
                    form.prepend(errorDiv);
                }
            } else {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger';
                errorDiv.textContent = `表单更新失败: HTTP ${xhr.status}`;
                const responsePreview = xhr.responseText.substring(0, 200) + (xhr.responseText.length > 200 ? '...' : '');
                const debugInfo = document.createElement('div');
                debugInfo.className = 'mt-2 text-muted small';
                debugInfo.textContent = '服务器响应: ' + responsePreview;
                errorDiv.appendChild(debugInfo);
                form.prepend(errorDiv);
            }
        };

        xhr.onerror = function() {
            console.error('网络错误');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.textContent = '网络错误，请检查网络连接';
            form.prepend(errorDiv);
        };

        xhr.send(formData);
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.menu-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('菜单切换点击:', this.textContent);
                const parent = this.closest('.has-submenu');
                if (parent) {
                    parent.classList.toggle('active');
                    document.querySelectorAll('.has-submenu').forEach(other => {
                        if (other !== parent) {
                            other.classList.remove('active');
                        }
                    });
                } else {
                    console.error('未找到父级 .has-submenu:', this);
                }
            });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.has-submenu')) {
                console.log('点击外部区域，关闭所有子菜单');
                document.querySelectorAll('.has-submenu').forEach(menu => {
                    menu.classList.remove('active');
                });
            }
        });

        document.querySelectorAll('.submenu a').forEach(link => {
            link.addEventListener('click', function(e) {
                console.log('子菜单链接点击:', this.textContent);
                e.stopPropagation();
            });
        });
    });
</script>
</body>
</html>