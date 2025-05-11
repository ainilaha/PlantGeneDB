<?php
session_start();
ini_set('upload_max_filesize', '0');
ini_set('post_max_size', '0');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '0');
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
global $conn;
error_reporting(E_ALL);
ini_set('display_errors', 0); // 关闭直接显示错误，防止破坏JSON响应

// HTML 内容净化函数 - 修复版
function sanitize_html($html) {
    // 如果输入为空，直接返回空字符串而不是null
    if (empty($html)) {
        return '';
    }
    
    // 允许的标签列表
    $allowed_tags = '<p><br><a><em><i><strong><b><ul><ol><li><h1><h2><h3><h4><h5><h6><span><div><img><table><tr><td><th><thead><tbody><blockquote>';
    
    // 移除危险属性
    $html = preg_replace('/<([^>]*)(?:onclick|onload|onerror|onmouseover|onmouseout|onkeydown|onkeypress|onkeyup)([^>]*)>/i', '<$1$2>', $html);
    
    // 保留允许的标签，移除其他标签
    $html = strip_tags($html, $allowed_tags);
    
    // 确保返回的是字符串，不是null或false
    return $html !== false ? $html : '';
}

require __DIR__ . '/config.php';
require_admin();

// 分片上传处理逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'upload_chunk') {
    try {
        $chunk = $_FILES['chunk']['tmp_name'];
        $chunk_index = (int)$_POST['chunk_index'];
        $total_chunks = (int)$_POST['total_chunks'];
        $file_name = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $_POST['file_name']);
        $field = $_POST['field'];
        $temp_dir = __DIR__ . '/uploads/temp/' . session_id() . '/' . $field . '/';

        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }

        $chunk_path = $temp_dir . $file_name . '.part' . $chunk_index;
        if (!move_uploaded_file($chunk, $chunk_path)) {
            throw new Exception('Failed to save chunk');
        }

        $all_chunks_uploaded = true;
        for ($i = 0; $i < $total_chunks; $i++) {
            if (!file_exists($temp_dir . $file_name . '.part' . $i)) {
                $all_chunks_uploaded = false;
                break;
            }
        }

        if ($all_chunks_uploaded) {
            $upload_dirs = [
                'genomic_sequence' => __DIR__ . '/uploads/files/genomic/',
                'cds_sequence' => __DIR__ . '/uploads/files/cds/',
                'gff3_annotation' => __DIR__ . '/uploads/files/annotation/',
                'peptide_sequence' => __DIR__ . '/uploads/files/peptide/',
                'image' => __DIR__ . '/uploads/images/'
            ];

            if (!is_dir($upload_dirs[$field])) {
                mkdir($upload_dirs[$field], 0755, true);
            }

            $counter = 1;
            $base_name = pathinfo($file_name, PATHINFO_FILENAME);
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $final_name = $file_name;
            while (file_exists($upload_dirs[$field] . $final_name)) {
                $final_name = $base_name . '_' . $counter . '.' . $ext;
                $counter++;
            }

            $final_path = $upload_dirs[$field] . $final_name;
            $out = fopen($final_path, 'wb');
            if (!$out) {
                throw new Exception('Failed to open output file');
            }

            for ($i = 0; $i < $total_chunks; $i++) {
                $chunk_path = $temp_dir . $file_name . '.part' . $i;
                $in = fopen($chunk_path, 'rb');
                if (!$in) {
                    fclose($out);
                    throw new Exception('Failed to open chunk ' . $i);
                }
                while ($buff = fread($in, 2097152)) {
                    fwrite($out, $buff);
                }
                fclose($in);
                unlink($chunk_path);
            }
            fclose($out);
            rmdir($temp_dir);

            echo json_encode(['status' => 'success', 'file_name' => $final_name]);
        } else {
            echo json_encode(['status' => 'chunk_uploaded']);
        }
    } catch (Exception $e) {
        error_log('Chunk upload error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// 表单处理和用户信息获取逻辑
$success = '';
$error = '';
$file_paths = [
    'genomic_sequence' => ['name' => '', 'path' => ''],
    'cds_sequence' => ['name' => '', 'path' => ''],
    'gff3_annotation' => ['name' => '', 'path' => ''],
    'peptide_sequence' => ['name' => '', 'path' => ''],
    'image' => ['name' => '', 'path' => '']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['action'])) {
    // 启用输出缓冲，防止直接输出错误信息
    ob_start();
    
    error_log('Form submitted: ' . print_r($_POST, true));
    try {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Security verification failed, please resubmit");
        }

        // 在处理表单数据之前，对富文本内容进行净化
        if (isset($_POST['description'])) {
            $_POST['description'] = sanitize_html($_POST['description']);
            // 额外日志记录，用于调试
            error_log('Sanitized description: ' . $_POST['description']);
        }

        if (isset($_POST['reference_link'])) {
            $_POST['reference_link'] = sanitize_html($_POST['reference_link']);
            error_log('Sanitized reference_link: ' . $_POST['reference_link']);
        }

        if (isset($_POST['scientific_name'])) {
            $_POST['scientific_name'] = sanitize_html($_POST['scientific_name']);
            error_log('Sanitized scientific_name: ' . $_POST['scientific_name']);
        }

        $fields = [
            'species_name' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'scientific_name' => [
                'filter' => FILTER_CALLBACK,
                'options' => function($value) { 
                    // 确保不返回null或false
                    return $value !== null && $value !== false ? $value : ''; 
                }
            ],
            'common_name' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'genus' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'genome_type' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'genome_size' => FILTER_SANITIZE_FULL_SPECIAL_CHARS, // 修改为字符串过滤器
            'chromosome_number' => FILTER_SANITIZE_NUMBER_INT,
            'gene_number' => FILTER_SANITIZE_NUMBER_INT,
            'cds_number' => FILTER_SANITIZE_NUMBER_INT,
            'description' => [
                'filter' => FILTER_CALLBACK,
                'options' => function($value) { 
                    // 确保不返回null或false
                    return $value !== null && $value !== false ? $value : ''; 
                }
            ],
            'reference_link' => [
                'filter' => FILTER_CALLBACK,
                'options' => function($value) { 
                    // 确保不返回null或false
                    return $value !== null && $value !== false ? $value : ''; 
                }
            ]
        ];

        $form_data = filter_input_array(INPUT_POST, $fields, true) ?? [];
        if ($form_data === null || in_array(null, $form_data, true)) {
            throw new Exception("Form data validation failed");
        }

        // 确保所有字段都有值，即使是空字符串
        $form_data = array_map(function($value) {
            return $value ?? '';
        }, $form_data);

        // 记录处理后的表单数据以便调试
        error_log('Processed form data: ' . print_r($form_data, true));

        $file_fields = ['genomic_sequence', 'cds_sequence', 'gff3_annotation', 'peptide_sequence', 'image'];
        $upload_dirs = [
            'genomic_sequence' => __DIR__ . '/uploads/files/genomic/',
            'cds_sequence' => __DIR__ . '/uploads/files/cds/',
            'gff3_annotation' => __DIR__ . '/uploads/files/annotation/',
            'peptide_sequence' => __DIR__ . '/uploads/files/peptide/',
            'image' => __DIR__ . '/uploads/images/'
        ];

        $allowed_types = [
            'genomic_sequence' => ['fasta', 'fa', 'fna', 'faa', 'gb', 'gbk', 'sam', 'bam'],
            'cds_sequence' => ['cds', 'fa', 'fasta', 'ffn'],
            'gff3_annotation' => ['gff3', 'gff'],
            'peptide_sequence' => ['fa', 'fasta', 'faa', 'pep', 'peptide', 'txt', 'pepXML', 'dat'],
            'image' => ['jpg', 'jpeg', 'png', 'gif']
        ];

        foreach ($file_fields as $field) {
            if (!empty($_POST[$field . '_name'])) {
                $file_name = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $_POST[$field . '_name']);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if (!in_array($file_ext, $allowed_types[$field])) {
                    throw new Exception("Invalid file type for {$field}: {$file_name}");
                }
                $file_paths[$field] = [
                    'name' => $file_name,
                    'path' => $upload_dirs[$field] . $file_name
                ];
                if ($field === 'image') {
                    $file_paths[$field]['path'] = 'Admin/uploads/images/' . $file_name;
                }
            }
        }

        if (empty($file_paths['genomic_sequence']['name']) || !file_exists($upload_dirs['genomic_sequence'] . $file_paths['genomic_sequence']['name'])) {
            throw new Exception("Genomic sequence file is required and must exist");
        }

        $conn->begin_transaction();

        // 修正SQL语句：17个字段需要17个问号
        $stmt = $conn->prepare("INSERT INTO genomics_species 
            (species_name, scientific_name, common_name, genus, genome_type, genome_size,
            chromosome_number, gene_number, cds_number, description, image_url,
            genomic_sequence, cds_sequence, gff3_annotation, peptide_sequence,
            reference_link, submitted_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            throw new Exception("SQL preparation error: " . $conn->error);
        }

        // 准备数据，确保所有值都是字符串（对于字符串字段）
        $species_name = strval($form_data['species_name']);
        $scientific_name = strval($form_data['scientific_name']);
        $common_name = strval($form_data['common_name']);
        $genus = strval($form_data['genus']);
        $genome_type = strval($form_data['genome_type']);
        $genome_size = strval($form_data['genome_size']); // 修改为 strval，保留文本格式
        $chromosome_number = intval($form_data['chromosome_number']);
        $gene_number = intval($form_data['gene_number']);
        $cds_number = intval($form_data['cds_number']);
        $description = strval($form_data['description']);
        $image_url = strval($file_paths['image']['path']);
        $genomic_sequence = strval($file_paths['genomic_sequence']['name']);
        $cds_sequence = strval($file_paths['cds_sequence']['name']);
        $gff3_annotation = strval($file_paths['gff3_annotation']['name']);
        $peptide_sequence = strval($file_paths['peptide_sequence']['name']);
        $reference_link = strval($form_data['reference_link']);
        $submitted_by = intval($_SESSION['user_id']);

        // 修正参数绑定：确保顺序与字段匹配，将 genome_size 的类型从 i 改为 s
        $bind_result = $stmt->bind_param("ssssssiiisssssssi",
            $species_name,        // 1. species_name
            $scientific_name,     // 2. scientific_name
            $common_name,         // 3. common_name
            $genus,               // 4. genus
            $genome_type,         // 5. genome_type
            $genome_size,         // 6. genome_size - 现在是s类型(字符串)
            $chromosome_number,   // 7. chromosome_number
            $gene_number,         // 8. gene_number
            $cds_number,          // 9. cds_number
            $description,         // 10. description
            $image_url,           // 11. image_url
            $genomic_sequence,    // 12. genomic_sequence
            $cds_sequence,        // 13. cds_sequence
            $gff3_annotation,     // 14. gff3_annotation
            $peptide_sequence,    // 15. peptide_sequence
            $reference_link,      // 16. reference_link
            $submitted_by         // 17. submitted_by
        );

        if (!$bind_result) {
            throw new Exception("Parameter binding error: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }

        $insert_id = $stmt->insert_id;
        $conn->commit();

        error_log("Insert successful with ID: " . $insert_id);
        $success = "Data submitted successfully! ID: " . $insert_id;
        
        // 清除缓冲区并发送JSON响应
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'insert_id' => $insert_id]);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        error_log('Submission error: ' . $e->getMessage());
        $error = "Submission failed: " . $e->getMessage();
        
        // 清除缓冲区并发送JSON错误响应
        ob_clean();
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

$user_id = $_SESSION['user_id'];
$query = "SELECT username, role FROM users WHERE id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    error_log("Query preparation failed: " . $conn->error);
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    error_log("Query failed: " . $conn->error);
    die("Query failed: " . $conn->error);
}

$user = $result->fetch_assoc();
if (!$user) {
    error_log("User information retrieval failed");
    die("User information retrieval failed");
}

$stmt->close();

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>数据上传</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- TinyMCE 编辑器 -->
    <script src="https://cdn.tiny.cloud/1/17ulot83qpdv0de56wq31hm49zthms8q06rwv9cu8itx55es/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        .progress-container {
            margin-top: 10px;
            display: none;
        }
        .progress-container.active {
            display: block;
        }
        .progress-bar {
            transition: width 0.3s ease-in-out;
            background-color: #007bff;
        }
        .progress-bar.completed {
            background-color: #28a745;
        }
        .progress-bar.failed {
            background-color: #dc3545;
        }
        .file-preview {
            margin-top: 0.3rem;
            font-size: 0.8rem;
            word-break: break-all;
        }
        .file-preview.error {
            color: #dc3545;
        }
        .file-preview.success {
            color: #28a745;
        }
        .status-text {
            margin-top: 0.3rem;
            font-size: 0.8rem;
            color: #666;
        }
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
            grid-template-columns: repeat(3, 1fr); /* 修改为3列布局 */
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
        .form-group.description {
            grid-column: 1 / -1;
        }
        .form-group.reference-link {
            grid-column: 1 / -1;
        }
        .upload-zone {
            border: 2px dashed #ced4da;
            border-radius: 6px;
            padding: 12px;
            background: #f8f9fa;
            transition: all 0.3s;
            text-align: center;
            min-height: 120px;
            position: relative;
        }
        .upload-zone.dragover {
            background: rgba(76,175,80,0.1);
            border-color: #4CAF50;
        }
        .upload-zone h6 {
            margin: 0 0 8px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .upload-instruction {
            margin: 0.3rem 0;
            color: #666;
            font-size: 0.8rem;
        }
        .custom-upload-btn {
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            display: inline-block;
        }
        .upload-zone small {
            display: block;
            margin-top: 0.3rem;
            color: #6c757d;
            font-size: 0.75rem;
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
        .submit-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
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
        
        /* TinyMCE 编辑器样式 */
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
        .form-group.description, .form-group.reference-link {
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
                <li><a href="genomics_content.php" class="active-sub">Genomics</a></li>
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
        <h3>数据上传</h3>
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

        <form id="uploadForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="genomic_sequence_name" id="genomic_sequence_name">
            <input type="hidden" name="cds_sequence_name" id="cds_sequence_name">
            <input type="hidden" name="gff3_annotation_name" id="gff3_annotation_name">
            <input type="hidden" name="peptide_sequence_name" id="peptide_sequence_name">
            <input type="hidden" name="image_name" id="image_name">

            <h5>Genomic</h5>
            <div class="form-grid">
                <div class="form-group">
                    <label>Species Name *</label>
                    <input type="text" name="species_name" required>
                    <small class="text-muted">（默认斜体显示）</small>
                </div>
                <div class="form-group">
                    <label>Scientific Name *</label>
                    <textarea id="editor_scientific_name" name="scientific_name" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label>Common Name</label>
                    <input type="text" name="common_name">
                </div>
                <div class="form-group">
                    <label>Genus *</label>
                    <input type="text" name="genus" required>
                    <small class="text-muted">（默认斜体显示）</small>
                </div>
                <div class="form-group">
                    <label>Genome Type *</label>
                    <input type="text" name="genome_type" required>
                </div>
                <div class="form-group">
                    <label>Genome Size *</label>
                    <input type="text" name="genome_size" required>
                    <small class="text-muted">（可输入带单位的值，如3.1GB, 147Mb等）</small>
                </div>
                <div class="form-group">
                    <label>Chromosome Number *</label>
                    <input type="number" name="chromosome_number" required>
                </div>
                <div class="form-group">
                    <label>Gene Number *</label>
                    <input type="number" name="gene_number" required>
                </div>
                <div class="form-group">
                    <label>CDS Number *</label>
                    <input type="number" name="cds_number" required>
                </div>
                <div class="form-group description">
                    <label>Description</label>
                    <textarea name="description" id="editor_description"></textarea>
                </div>
                <div class="form-group reference-link">
                    <label>Reference</label>
                    <textarea name="reference_link" id="editor_reference"></textarea>
                </div>
            </div>

            <h5 class="mt-4">File Uploads</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="upload-zone" data-accept=".fasta,.fa,.fna,.faa,.gb,.gbk,.sam,.bam">
                        <h6>Genomic Sequence *</h6>
                        <div class="upload-instruction">
                            <label class="custom-upload-btn">
                                Select or Drag
                                <input type="file" class="native-input d-none"
                                       name="genomic_sequence"
                                       accept=".fasta,.fa,.fna,.faa,.gb,.gbk,.sam,.bam">
                            </label>
                        </div>
                        <div class="file-preview"></div>
                        <div class="progress-container">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                        </div>
                        <small>Supported: .fasta, .fa, .fna, .faa, .gb, .gbk, .sam, .bam</small>
                        <div class="status-text"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="upload-zone" data-accept=".cds,.fa,.fasta,.ffn">
                        <h6>CDS Sequence</h6>
                        <div class="upload-instruction">
                            <label class="custom-upload-btn">
                                Select or Drag
                                <input type="file" class="native-input d-none"
                                       name="cds_sequence"
                                       accept=".cds,.fa,.fasta,.ffn">
                            </label>
                        </div>
                        <div class="file-preview"></div>
                        <div class="progress-container">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                        </div>
                        <small>Supported: .cds, .fa, .fasta, .ffn</small>
                        <div class="status-text"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="upload-zone" data-accept=".gff3,.gff">
                        <h6>GFF3 Annotation</h6>
                        <div class="upload-instruction">
                            <label class="custom-upload-btn">
                                Select or Drag
                                <input type="file" class="native-input d-none"
                                       name="gff3_annotation"
                                       accept=".gff3,.gff">
                            </label>
                        </div>
                        <div class="file-preview"></div>
                        <div class="progress-container">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                        </div>
                        <small>Supported: .gff3, .gff</small>
                        <div class="status-text"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="upload-zone" data-accept=".fa,.fasta,.faa,.pep,.peptide,.txt,.pepXML,.dat">
                        <h6>Peptide Sequence</h6>
                        <div class="upload-instruction">
                            <label class="custom-upload-btn">
                                Select or Drag
                                <input type="file" class="native-input d-none"
                                       name="peptide_sequence"
                                       accept=".fa,.fasta,.faa,.pep,.peptide,.txt,.pepXML,.dat">
                            </label>
                        </div>
                        <div class="file-preview"></div>
                        <div class="progress-container">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                        </div>
                        <small>Supported: .fa, .fasta, .faa, .pep, .peptide, .txt, .pepXML, .dat</small>
                        <div class="status-text"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="upload-zone" data-accept=".jpg,.jpeg,.png,.gif">
                        <h6>Image</h6>
                        <div class="upload-instruction">
                            <label class="custom-upload-btn">
                                Select or Drag
                                <input type="file" class="native-input d-none"
                                       name="image"
                                       accept=".jpg,.jpeg,.png,.gif">
                            </label>
                        </div>
                        <div class="file-preview"></div>
                        <div class="progress-container">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                        </div>
                        <small>Supported: .jpg, .jpeg, .png, .gif</small>
                        <div class="status-text"></div>
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn" disabled>Submit Data</button>
        </form>
    </div>
</div>

<script>
    const submitBtn = document.querySelector('.submit-btn');
    let pendingUploads = 0;

    // 初始化 TinyMCE 编辑器
    tinymce.init({
        selector: '#editor_scientific_name',
        plugins: 'autoresize',
        toolbar: 'formatscientific italic | undo redo',
        menubar: false,
        statusbar: false,
        height: 70,
        setup: function(editor) {
            // 添加科学名称格式化按钮
            editor.ui.registry.addButton('formatscientific', {
                text: '格式化学名',
                tooltip: '自动将属名和种名格式化为斜体',
                onAction: function () {
                    // 获取当前选中的文本
                    const selectedText = editor.selection.getContent({format: 'text'});
                    
                    if (selectedText) {
                        // 假设输入格式为 "Genus species cv. Variety" 或类似格式
                        const parts = selectedText.split(' ');
                        if (parts.length >= 2) {
                            // 将属名和种名设为斜体
                            let formattedText = '<em>' + parts[0] + ' ' + parts[1] + '</em>';
                            
                            // 添加其余部分（如果有）
                            if (parts.length > 2) {
                                formattedText += ' ' + parts.slice(2).join(' ');
                            }
                            
                            editor.selection.setContent(formattedText);
                        }
                    }
                }
            });
            
            editor.on('change', function() {
                editor.save();
                console.log('Scientific name changed:', editor.getContent());
            });
        }
    });

    tinymce.init({
        selector: '#editor_description',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        menubar: false,
        height: 300,
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 16px; }',
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
                console.log('Description changed:', editor.getContent());
            });
        }
    });

    tinymce.init({
        selector: '#editor_reference',
        plugins: 'anchor autolink charmap codesample emoticons link lists searchreplace visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link | align lineheight | numlist bullist indent outdent | removeformat',
        menubar: false,
        height: 200,
        content_style: 'body { font-family: "Times New Roman", Times, serif; font-size: 16px; }',
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
                console.log('Reference changed:', editor.getContent());
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
        }, 5000); // 5秒后移除提示
    }

    // 显示成功提示的函数
    function showSuccessMessage(message) {
        const successDiv = document.createElement('div');
        successDiv.className = 'alert alert-success';
        successDiv.textContent = message;
        document.querySelector('.upload-form').prepend(successDiv);
        setTimeout(() => {
            successDiv.remove();
        }, 5000); // 5秒后移除提示
    }

    document.querySelectorAll('.upload-zone').forEach(zone => {
        const input = zone.querySelector('input[type="file"]');
        const preview = zone.querySelector('.file-preview');
        const progressContainer = zone.querySelector('.progress-container');
        const progressBar = zone.querySelector('.progress-bar');
        const statusText = zone.querySelector('.status-text');
        const acceptTypes = zone.dataset.accept.split(',').map(t => t.trim());
        const fieldName = input.name;
        const CHUNK_SIZE = 10 * 1024 * 1024; // 10MB 分片大小

        zone.addEventListener('dragover', e => {
            e.preventDefault();
            zone.classList.add('dragover');
        });

        zone.addEventListener('dragleave', e => {
            e.preventDefault();
            zone.classList.remove('dragover');
        });

        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        input.addEventListener('change', e => handleFiles(e.target.files));

        function handleFiles(files) {
            const validFiles = Array.from(files).filter(file => {
                const ext = '.' + file.name.split('.').pop().toLowerCase();
                return acceptTypes.includes(ext);
            });

            if (validFiles.length === 0) {
                showError('文件类型不支持');
                return;
            }

            const file = validFiles[0];
            preview.textContent = file.name;
            preview.classList.remove('error', 'success');
            uploadFile(file, fieldName);
        }

        async function uploadFile(file, fieldName) {
            pendingUploads++;
            submitBtn.disabled = true;
            statusText.textContent = '上传中...';
            statusText.style.color = '#666';

            const totalSize = file.size;
            const totalChunks = Math.ceil(totalSize / CHUNK_SIZE);
            let uploadedBytes = 0;

            progressContainer.classList.add('active');
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';
            progressBar.setAttribute('aria-valuenow', 0);
            progressBar.classList.remove('completed', 'failed');

            try {
                for (let i = 0; i < totalChunks; i++) {
                    const start = i * CHUNK_SIZE;
                    const end = Math.min(start + CHUNK_SIZE, totalSize);
                    const chunk = file.slice(start, end);

                    const formData = new FormData();
                    formData.append('chunk', chunk, file.name);
                    formData.append('chunk_index', i);
                    formData.append('total_chunks', totalChunks);
                    formData.append('file_name', file.name);
                    formData.append('field', fieldName);

                    const response = await new Promise((resolve, reject) => {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', '?action=upload_chunk', true);

                        xhr.upload.onprogress = function(e) {
                            if (e.lengthComputable) {
                                const chunkProgress = e.loaded / e.total;
                                const overallProgress = ((uploadedBytes + (chunkProgress * (end - start))) / totalSize) * 100;
                                progressBar.style.width = overallProgress.toFixed(2) + '%';
                                progressBar.textContent = overallProgress.toFixed(2) + '%';
                                progressBar.setAttribute('aria-valuenow', overallProgress.toFixed(2));
                            }
                        };

                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                resolve(JSON.parse(xhr.responseText));
                            } else {
                                reject(new Error(`上传失败，状态码: ${xhr.status}`));
                            }
                        };

                        xhr.onerror = function() {
                            reject(new Error('网络错误'));
                        };

                        xhr.send(formData);
                    });

                    uploadedBytes += end - start;

                    if (response.status === 'error') {
                        throw new Error(response.message);
                    } else if (response.status === 'success') {
                        console.log('Setting hidden field:', fieldName + '_name', response.file_name);
                        document.getElementById(fieldName + '_name').value = response.file_name;
                        preview.textContent = file.name + ' (上传完成)';
                        preview.classList.add('success');
                        progressBar.classList.add('completed');
                        progressBar.style.width = '100%';
                        progressBar.textContent = '100%';
                        statusText.textContent = '上传完成';
                        statusText.style.color = '#28a745';
                        showSuccessMessage(`文件 ${file.name} 上传成功！`);
                        setTimeout(() => {
                            progressContainer.classList.remove('active');
                        }, 1000);
                    }
                }
            } catch (error) {
                console.error('Upload error:', error.message);
                progressBar.classList.add('failed');
                showError(`上传失败: ${error.message}`);
            } finally {
                pendingUploads--;
                if (pendingUploads === 0) {
                    submitBtn.disabled = false;
                }
            }
        }

        function showError(msg) {
            preview.textContent = msg;
            preview.classList.add('error');
            progressContainer.classList.add('active');
            progressBar.classList.add('failed');
            statusText.textContent = msg;
            statusText.style.color = '#dc3545';
            setTimeout(() => {
                preview.textContent = '';
                preview.classList.remove('error');
                progressContainer.classList.remove('active');
                progressBar.classList.remove('failed');
                statusText.textContent = '';
                statusText.style.color = '#666';
            }, 8000);
        }
    });

    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Submitting form...');
        
        // 验证必填字段
        const speciesName = document.querySelector('input[name="species_name"]').value;
        if (!speciesName.trim()) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.textContent = '请填写 Species Name 字段';
            this.prepend(errorDiv);
            return;
        }
        
        // 确保 TinyMCE 编辑器的内容已保存到表单
        tinymce.triggerSave();
        
        // 记录富文本内容，便于调试
        console.log('Scientific Name:', document.getElementById('editor_scientific_name').value);
        console.log('Description:', document.getElementById('editor_description').value);
        console.log('Reference:', document.getElementById('editor_reference').value);

        const genomicSequenceName = document.getElementById('genomic_sequence_name').value;
        if (!genomicSequenceName) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.textContent = '请上传 Genomic Sequence 文件';
            this.prepend(errorDiv);
            return;
        }

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
        xhr.open('POST', 'gene_upload.php', true);

        xhr.onload = function() {
            console.log('Response status:', xhr.status);
            console.log('Response text:', xhr.responseText);
            
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        showGlobalSuccessMessage('成功上传');
                        showSuccessMessage('表单提交成功！ID: ' + response.insert_id);
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        throw new Error(response.message || '提交失败');
                    }
                } catch (e) {
                    console.error('JSON解析错误:', e);
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger';
                    errorDiv.textContent = '表单提交失败: ' + (e.message || '服务器返回无效数据');
                    
                    // 如果响应不是有效JSON，显示原始响应内容（限制长度）
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
                errorDiv.textContent = `表单提交失败: HTTP ${xhr.status}`;
                
                // 添加响应内容提示
                const responsePreview = xhr.responseText.substring(0, 200) + (xhr.responseText.length > 200 ? '...' : '');
                const debugInfo = document.createElement('div');
                debugInfo.className = 'mt-2 text-muted small';
                debugInfo.textContent = '服务器响应: ' + responsePreview;
                errorDiv.appendChild(debugInfo);
                
                form.prepend(errorDiv);
            }
        };

        xhr.onerror = function() {
            console.error('Network error');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.textContent = '网络错误，请检查网络连接';
            form.prepend(errorDiv);
        };

        xhr.send(formData);
    });

    document.addEventListener('DOMContentLoaded', function() {
        // 菜单切换逻辑
        document.querySelectorAll('.menu-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Menu toggle clicked:', this.textContent);
                const parent = this.closest('.has-submenu');
                if (parent) {
                    parent.classList.toggle('active');
                    document.querySelectorAll('.has-submenu').forEach(other => {
                        if (other !== parent) {
                            other.classList.remove('active');
                        }
                    });
                } else {
                    console.error('Parent .has-submenu not found for toggle:', this);
                }
            });
        });

        // 点击页面其他区域关闭子菜单
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.has-submenu')) {
                console.log('Clicked outside submenu, closing all submenus');
                document.querySelectorAll('.has-submenu').forEach(menu => {
                    menu.classList.remove('active');
                });
            }
        });

        // 子菜单链接点击不关闭父菜单
        document.querySelectorAll('.submenu a').forEach(link => {
            link.addEventListener('click', function(e) {
                console.log('Submenu link clicked:', this.textContent);
                e.stopPropagation(); // 阻止冒泡到 document 的点击事件
            });
        });
    });
</script>
</body>
</html>
