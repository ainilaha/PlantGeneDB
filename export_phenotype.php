<?php
// Include database connection file
require_once 'Admin/config.php';

// Process filter conditions
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build SQL query WITHOUT any LIMIT clause
$sql = "SELECT * FROM Phenotype WHERE 1=1";

if (!empty($category)) {
    $sql .= " AND Species = '" . $conn->real_escape_string($category) . "'";
}

if (!empty($search)) {
    $sql .= " AND (
        Species LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Class LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Trait_name LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Planting_location LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Planting_date LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Treatment LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Source LIKE '%" . $conn->real_escape_string($search) . "%'
    )";
}

// Execute query - ensure we're getting ALL results
$result = $conn->query($sql);

// Set response headers for Excel file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename=phenotype_data_with_italics.xlsx');
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

echo '<Worksheet ss:Name="Phenotype Data">' . "\n";
echo '<Table>' . "\n";

// Output header row
echo '<Row>' . "\n";
$headers = [
    'Species', 'Class', 'Trait Name', 'Records', 'Location', 'Planting Date', 'Treatment', 'Source'
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
        echo '<Cell ss:StyleID="Italic"><Data ss:Type="String">' . htmlspecialchars($row['Species']) . '</Data></Cell>' . "\n";
        
        // Column 2: Class (normal)
        echo '<Cell ss:StyleID="Normal"><Data ss:Type="String">' . htmlspecialchars($row['Class']) . '</Data></Cell>' . "\n";
        
        // Column 3: Trait Name (normal)
        echo '<Cell ss:StyleID="Normal"><Data ss:Type="String">' . htmlspecialchars($row['Trait_name']) . '</Data></Cell>' . "\n";
        
        // Column 4: Records (normal, number)
        echo '<Cell ss:StyleID="Normal"><Data ss:Type="Number">' . htmlspecialchars($row['Record_num']) . '</Data></Cell>' . "\n";
        
        // Column 5: Location (normal)
        echo '<Cell ss:StyleID="Normal"><Data ss:Type="String">' . htmlspecialchars($row['Planting_location']) . '</Data></Cell>' . "\n";
        
        // Column 6: Planting Date (normal)
        echo '<Cell ss:StyleID="Normal"><Data ss:Type="String">' . htmlspecialchars($row['Planting_date']) . '</Data></Cell>' . "\n";
        
        // Column 7: Treatment (normal)
        echo '<Cell ss:StyleID="Normal"><Data ss:Type="String">' . htmlspecialchars($row['Treatment']) . '</Data></Cell>' . "\n";
        
        // Column 8: Source (normal)
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