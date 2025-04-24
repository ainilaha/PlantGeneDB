<?php
require 'Admin/config.php';

header('Content-Type: application/json');

if (!isset($_GET['species_id']) || !isset($_GET['file_type'])) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$species_id = $conn->real_escape_string($_GET['species_id']);
$file_type = $conn->real_escape_string($_GET['file_type']);

// 验证文件类型
$valid_types = ['genomic', 'cds', 'gff3', 'peptide'];
if (!in_array($file_type, $valid_types)) {
    echo json_encode(['error' => 'Invalid file type']);
    exit;
}

// 获取物种信息
$query = "SELECT * FROM genomics_species WHERE id = '$species_id'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $species = $result->fetch_assoc();
    
    // 根据文件类型获取对应的文件名
    $filename = '';
    $subfolder = '';
    
    switch ($file_type) {
        case 'genomic':
            $filename = $species['genomic_sequence'];
            $subfolder = 'genomic';
            break;
        case 'cds':
            $filename = $species['cds_sequence'];
            $subfolder = 'cds';
            break;
        case 'gff3':
            $filename = $species['gff3_annotation'];
            $subfolder = 'annotation';
            break;
        case 'peptide':
            $filename = $species['peptide_sequence'];
            $subfolder = 'peptide';
            break;
    }
    
    if ($filename && file_exists("files/$subfolder/$filename")) {
        echo json_encode([
            'filename' => $filename,
            'path' => $subfolder
        ]);
    } else {
        echo json_encode(['error' => 'File not found']);
    }
} else {
    echo json_encode(['error' => 'Species not found']);
}
?>