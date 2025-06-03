<?php
// Include database connection file
require_once 'Admin/config.php';

// Process filter conditions
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build SQL query WITHOUT any LIMIT clause
$sql = "SELECT * FROM Microbiomics WHERE 1=1";

if (!empty($category)) {
    $sql .= " AND Species = '" . $conn->real_escape_string($category) . "'";
}

if (!empty($search)) {
    $sql .= " AND (
        Species LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Article_Overview LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Family_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Genus_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Species_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Genome_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Transcriptome_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Microbiome_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Tissue LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Biotic_stress LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Abiotic_stress LIKE '%" . $conn->real_escape_string($search) . "%'
    )";
}

// Execute query - ensure we're getting ALL results
$result = $conn->query($sql);

// Function to clean HTML tags and preserve text content
function clean_html_for_csv($html) {
    if (empty($html)) {
        return '';
    }
    
    // Replace <br> tags with spaces
    $html = preg_replace('/<br\s*\/?>/i', ' ', $html);
    
    // Replace paragraph tags with spaces
    $html = preg_replace('/<\/p>/i', ' ', $html);
    $html = preg_replace('/<p[^>]*>/i', '', $html);
    
    // Remove all HTML tags but preserve the text content
    $html = strip_tags($html);
    
    // Clean up multiple spaces and line breaks
    $html = preg_replace('/\s+/', ' ', $html);
    
    // Trim whitespace
    $html = trim($html);
    
    return $html;
}

// Set response headers for Excel file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename=microbiomics_data_with_italics.xlsx');
header('Cache-Control: max-age=0');

// Create a simple Excel-compatible XML structure
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";

// Define styles
echo '<Styles>' . "\n";
echo '<Style ss:ID="Header">' . "\n";
echo '<Font ss:Bold="1" ss:Color="#FFFFFF"/>' . "\n";
echo '<Interior ss:Color="#3FBBC0" ss:Pattern="Solid"/>' . "\n";
echo '</Style>' . "\n";
echo '<Style ss:ID="Italic">' . "\n";
echo '<Font ss:Italic="1"/>' . "\n";
echo '</Style>' . "\n";
echo '<Style ss:ID="Normal">' . "\n";
echo '<Font/>' . "\n";
echo '</Style>' . "\n";
echo '</Styles>' . "\n";

echo '<Worksheet ss:Name="Microbiomics Data">' . "\n";
echo '<Table>' . "\n";

// Output header row - 更新字段顺序和添加新字段
echo '<Row>' . "\n";
$headers = [
    'Species', 'Article Overview', 'Family of Endophyte Fungi', 'Genus of Endophyte Fungi', 
    'Species of Endophyte Fungi', 'Genome of Endophyte Fungi', 'Transcriptome of Endophyte Fungi',
    'Microbiome of Endophyte Fungi', 'Tissue', 'Biotic Stress', 'Abiotic Stress', 'Source'
];

foreach ($headers as $header) {
    echo '<Cell ss:StyleID="Header"><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>' . "\n";
}
echo '</Row>' . "\n";

// Output data rows
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<Row>' . "\n";
        
        // Column 1: Species (italic)
        echo '<Cell ss:StyleID="Italic"><Data ss:Type="String">' . htmlspecialchars(clean_html_for_csv($row['Species'])) . '</Data></Cell>' . "\n";
        
        // Column 2: Article Overview (normal)
        echo '<Cell ss:StyleID="Normal"><Data ss:Type="String">' . htmlspecialchars(clean_html_for_csv($row['Article_Overview'])) . '</Data></Cell>' . "\n";
        
        // Column 3: Family of Endophyte Fungi (italic)
        echo '<Cell ss:StyleID="Italic"><Data ss:Type="String">' . htmlspecialchars($row['Family_of_endophyte_fungi']) . '</Data></Cell>' . "\n";
        
        // Column 4: Genus of Endophyte Fungi (italic)
        echo '<Cell ss:StyleID="Italic"><Data ss:Type="String">' . htmlspecialchars($row['Genus_of_endophyte_fungi']) . '</Data></Cell>' . "\n";
        
        // Column 5: Species of Endophyte Fungi (italic)
        echo '<Cell ss:StyleID="Italic"><Data ss:Type="String">' . htmlspecialchars($row['Species_of_endophyte_fungi']) . '</Data></Cell>' . "\n";
        
        // Column 6: Genome of Endophyte Fungi (normal)
        echo '<Cell ss:StyleID="Normal"><Data ss:Type="String">' . htmlspecialchars($row['Genome_of_endophyte_fungi']) . '</Data></Cell>' . "\n";
        
        // Column 7: Transcriptome of Endophyte Fungi (normal) - 新添加的字段
        echo '<Cell ss:StyleID="Normal"><Data ss:Type="String">' . htmlspecialchars($row['Transcriptome_of_endophyte_fungi'] ?? '') . '</Data></Cell>' . "\n";
        
        // Column 8: Microbiome of Endophyte Fungi (normal)
        echo '<Cell ss:StyleID="Normal"><Data ss:Type="String">' . htmlspecialchars($row['Microbiome_of_endophyte_fungi']) . '</Data></Cell>' . "\n";
        
        // Column 9: Tissue (normal)
        echo '<Cell ss:StyleID="Normal"><Data ss:Type="String">' . htmlspecialchars($row['Tissue']) . '</Data></Cell>' . "\n";
        
        // Column 10: Biotic Stress (normal)
        echo '<Cell ss:StyleID="Normal"><Data ss:Type="String">' . htmlspecialchars(clean_html_for_csv($row['Biotic_stress'])) . '</Data></Cell>' . "\n";
        
        // Column 11: Abiotic Stress (normal)
        echo '<Cell ss:StyleID="Normal"><Data ss:Type="String">' . htmlspecialchars($row['Abiotic_stress']) . '</Data></Cell>' . "\n";
        
        // Column 12: Source (normal)
        echo '<Cell ss:StyleID="Normal"><Data ss:Type="String">' . htmlspecialchars($row['Source']) . '</Data></Cell>' . "\n";
        
        echo '</Row>' . "\n";
    }
}

echo '</Table>' . "\n";
echo '</Worksheet>' . "\n";
echo '</Workbook>' . "\n";

// Close database connection
$conn->close();
exit;
?>