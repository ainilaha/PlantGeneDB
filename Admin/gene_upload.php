<?php
// 原有的 PHP 代码保持不变
session_start();
ini_set('upload_max_filesize', '0');
ini_set('post_max_size', '0');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '0');
global $conn;
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config.php';
require_admin();

// 原有的分片上传处理逻辑保持不变
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'upload_chunk') {
    try {
        $chunk = $_FILES['chunk']['tmp_name'];
        $chunk_index = (int)$_POST['chunk_index'];
        $total_chunks = (int)$_POST['total_chunks'];
        $file_name = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $_POST['file_name']);
        $field = $_POST['field'];
        $temp_dir = __DIR__ . '/Uploads/temp/' . session_id() . '/' . $field . '/';

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
                'genomic_sequence' => __DIR__ . '/Uploads/files/genomic/',
                'cds_sequence' => __DIR__ . '/Uploads/files/cds/',
                'gff3_annotation' => __DIR__ . '/Uploads/files/annotation/',
                'peptide_sequence' => __DIR__ . '/Uploads/files/peptide/',
                'image' => __DIR__ . '/Uploads/images/'
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
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// 原有的表单处理和用户信息获取逻辑保持不变
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
    try {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Security verification failed, please resubmit");
        }

        $fields = [
            'scientific_name' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'common_name' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'genus' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'genome_type' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'genome_size' => FILTER_SANITIZE_NUMBER_INT,
            'chromosome_number' => FILTER_SANITIZE_NUMBER_INT,
            'gene_number' => FILTER_SANITIZE_NUMBER_INT,
            'cds_number' => FILTER_SANITIZE_NUMBER_INT,
            'description' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'reference_link' => FILTER_VALIDATE_URL
        ];

        $form_data = filter_input_array(INPUT_POST, $fields, true) ?? [];
        if ($form_data === null || in_array(null, $form_data, true)) {
            throw new Exception("Form data validation failed");
        }

        $form_data = array_map(function($value) {
            return $value ?? '';
        }, $form_data);

        $file_fields = ['genomic_sequence', 'cds_sequence', 'gff3_annotation', 'peptide_sequence', 'image'];
        $upload_dirs = [
            'genomic_sequence' => __DIR__ . '/Uploads/files/genomic/',
            'cds_sequence' => __DIR__ . '/Uploads/files/cds/',
            'gff3_annotation' => __DIR__ . '/Uploads/files/annotation/',
            'peptide_sequence' => __DIR__ . '/Uploads/files/peptide/',
            'image' => __DIR__ . '/Uploads/images/'
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

        if (empty($file_paths['genomic_sequence']['name'])) {
            throw new Exception("Genomic sequence file is required");
        }

        $conn->begin_transaction();

        $stmt = $conn->prepare("INSERT INTO genomics_species 
            (scientific_name, common_name, genus, genome_type, genome_size,
            chromosome_number, gene_number, cds_number, description, image_url,
            genomic_sequence, cds_sequence, gff3_annotation, peptide_sequence,
            reference_link, submitted_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("sssssiiiissssssi",
            $form_data['scientific_name'],
            $form_data['common_name'],
            $form_data['genus'],
            $form_data['genome_type'],
            $form_data['genome_size'],
            $form_data['chromosome_number'],
            $form_data['gene_number'],
            $form_data['cds_number'],
            $form_data['description'],
            $file_paths['image']['path'],
            $file_paths['genomic_sequence']['name'],
            $file_paths['cds_sequence']['name'],
            $file_paths['gff3_annotation']['name'],
            $file_paths['peptide_sequence']['name'],
            $form_data['reference_link'],
            $_SESSION['user_id']
        );

        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }

        $conn->commit();
        $success = "Data submitted successfully! ID: " . $stmt->insert_id;
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Submission failed: " . $e->getMessage();
    }
}

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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
                <button type="submit" class="logout-btn">Logout</button>
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
                    <label>Scientific Name *</label>
                    <input type="text" name="scientific_name" required>
                </div>
                <div class="form-group">
                    <label>Common Name</label>
                    <input type="text" name="common_name">
                </div>
                <div class="form-group">
                    <label>Genus *</label>
                    <input type="text" name="genus" required>
                </div>
                <div class="form-group">
                    <label>Genome Type *</label>
                    <input type="text" name="genome_type" required>
                </div>
                <div class="form-group">
                    <label>Genome Size (bp) *</label>
                    <input type="number" name="genome_size" required>
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
                    <textarea name="description"></textarea>
                </div>
                <div class="form-group reference-link">
                    <label>Reference Link</label>
                    <input type="url" name="reference_link">
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
                                       accept=".fasta,.fa,.fna,.faa,.gb,.gbk,.sam,.bam"
                                       required>
                            </label>
                        </div>
                        <div class="file-preview"></div>
                        <div class="progress-container">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                        </div>
                        <small>Supported: .fasta, .fa, .fna, .faa, .gb, .gbk, .sam, .bam</small>
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
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn">Submit Data</button>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.upload-zone').forEach(zone => {
        const input = zone.querySelector('input[type="file"]');
        const preview = zone.querySelector('.file-preview');
        const progressContainer = zone.querySelector('.progress-container');
        const progressBar = zone.querySelector('.progress-bar');
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
            const totalSize = file.size;
            const totalChunks = Math.ceil(totalSize / CHUNK_SIZE);
            let uploadedBytes = 0;

            // 重置进度条
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
                        document.getElementById(fieldName + '_name').value = response.file_name;
                        preview.textContent = file.name + ' (上传完成)';
                        preview.classList.add('success');
                        progressBar.classList.add('completed');
                        progressBar.style.width = '100%';
                        progressBar.textContent = '100%';
                        setTimeout(() => {
                            progressContainer.classList.remove('active');
                        }, 1000); // 上传完成后短暂显示进度条
                    }
                }
            } catch (error) {
                progressBar.classList.add('failed');
                showError(`上传失败: ${error.message}`);
            }
        }

        function showError(msg) {
            preview.textContent = msg;
            preview.classList.add('error');
            progressContainer.classList.add('active');
            progressBar.classList.add('failed');
            setTimeout(() => {
                preview.textContent = '';
                preview.classList.remove('error');
                progressContainer.classList.remove('active');
                progressBar.classList.remove('failed');
            }, 8000); // 错误显示时间延长至8秒
        }
    });

    // 表单提交处理保持不变
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);

        xhr.onload = function() {
            if (xhr.status === 200) {
                window.location.reload();
            } else {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger';
                errorDiv.textContent = '表单提交失败';
                form.prepend(errorDiv);
            }
        };

        xhr.onerror = function() {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.textContent = '网络错误';
            form.prepend(errorDiv);
        };

        xhr.send(formData);
    });

    // 菜单切换逻辑保持不变
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
    });
</script>
</body>
</html>