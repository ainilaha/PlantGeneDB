<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 处理表单提交
    $data = [
        'scientific_name' => $conn->real_escape_string($_POST['scientific_name']),
        'common_name' => $conn->real_escape_string($_POST['common_name']),
        'genus' => $conn->real_escape_string($_POST['genus']),
        'genome_type' => $conn->real_escape_string($_POST['genome_type']),
        'genome_size' => $conn->real_escape_string($_POST['genome_size']),
        'chromosome_number' => $conn->real_escape_string($_POST['chromosome_number']),
        'gene_number' => $conn->real_escape_string($_POST['gene_number']),
        'cds_number' => $conn->real_escape_string($_POST['cds_number']),
        'description' => $conn->real_escape_string($_POST['description']),
        'genomic_sequence' => $conn->real_escape_string($_POST['genomic_sequence']),
        'cds_sequence' => $conn->real_escape_string($_POST['cds_sequence']),
        'gff3_annotation' => $conn->real_escape_string($_POST['gff3_annotation']),
        'peptide_sequence' => $conn->real_escape_string($_POST['peptide_sequence']),
        'reference_link' => $conn->real_escape_string($_POST['reference_link']),
        'submitted_by' => $_SESSION['user_id'],
        'status' => 'pending'
    ];

    // 处理图片上传
    $imageUrl = '';
    if (isset($_FILES['image']) {
        $targetDir = "assets/img/species/";
        $targetFile = $targetDir . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imageUrl = $targetFile;
        }
    }
    $data['image_url'] = $imageUrl;

    // 构建SQL查询
    $columns = implode(", ", array_keys($data));
    $values = "'" . implode("', '", array_values($data)) . "'";
    
    $query = "INSERT INTO genomics_species ($columns) VALUES ($values)";
    
    if ($conn->query($query)) {
        header('Location: submit_species.php?success=1');
        exit;
    } else {
        $error = "提交失败: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit New Species - QTP-GMD</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="main">
        <section class="section">
            <div class="container">
                <h2>Submit New Species</h2>
                
                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Species submitted successfully! It will be reviewed by our team.</div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="post" enctype="multipart/form-data" class="mt-4">
                    <!-- 表单内容与之前相同 -->
                    <div class="row">
                        <div class="col-md-6">
                            <!-- 左侧表单字段 -->
                        </div>
                        <div class="col-md-6">
                            <!-- 右侧表单字段 -->
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary">Submit Species</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>