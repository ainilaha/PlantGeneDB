<?php
// Include database connection file
require_once 'admin/config.php';

// Process filter conditions
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build SQL query WITHOUT any LIMIT clause
$sql = "SELECT * FROM Microbiomics WHERE 1=1";

if (!empty($category)) {
    $sql .= " AND Biotic_stress = '" . $conn->real_escape_string($category) . "'";
}

if (!empty($search)) {
    $sql .= " AND (
        Species LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Article_Overview LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Family_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Genus_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Species_of_endophyte_fungi LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Tissue LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Biotic_stress LIKE '%" . $conn->real_escape_string($search) . "%' OR 
        Abiotic_stress LIKE '%" . $conn->real_escape_string($search) . "%'
    )";
}

// Execute query - ensure we're getting ALL results
$result = $conn->query($sql);

// Debug: Print the number of rows found (you can remove this in production)
// echo "Found " . $result->num_rows . " rows to export"; exit;

// Set response headers, specify file type and download filename
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=microbiomics_data.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM mark (for Excel to display Unicode correctly)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Output CSV header
fputcsv($output, [
    'Species', 'Article Overview', 'Family of Endophyte Fungi', 'Genus of Endophyte Fungi', 
    'Species of Endophyte Fungi', 'Genome of Endophyte Fungi', 'Microbiome of Endophyte Fungi', 
    'Tissue', 'Biotic Stress', 'Abiotic Stress', 'Source', 'Link'
]);

// Output data rows - check that we have results
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['Species'],
            $row['Article_Overview'],
            $row['Family_of_endophyte_fungi'],
            $row['Genus_of_endophyte_fungi'],
            $row['Species_of_endophyte_fungi'],
            $row['Genome_of_endophyte_fungi'],
            $row['Microbiome_of_endophyte_fungi'],
            $row['Tissue'],
            $row['Biotic_stress'],
            $row['Abiotic_stress'],
            $row['Source'],
            $row['Link']
        ]);
    }
}

// Close output stream
fclose($output);

// Close database connection
$conn->close();
exit;
?>
