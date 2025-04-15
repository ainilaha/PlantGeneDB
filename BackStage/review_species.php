<?php
require 'config.php';
require_admin();

// 处理审核操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $species_id = (int)$_POST['species_id'];
    $action = $_POST['action'];
    $reviewed_by = $_SESSION['user_id'];
    
    if ($action === 'approve') {
        $status = 'approved';
    } elseif ($action === 'reject') {
        $status = 'rejected';
    } else {
        die("Invalid action");
    }
    
    $query = "UPDATE genomics_species SET status = '$status', reviewed_by = $reviewed_by WHERE id = $species_id";
    if ($conn->query($query)) {
        header("Location: review_species.php?success=1");
        exit;
    } else {
        $error = "操作失败: " . $conn->error;
    }
}

// 获取待审核物种
$query = "SELECT g.*, u.username as submitter 
          FROM genomics_species g 
          JOIN users u ON g.submitted_by = u.id 
          WHERE g.status = 'pending' 
          ORDER BY g.created_at DESC";
$pendingSpecies = $conn->query($query);

// 获取已审核物种
$query = "SELECT g.*, u1.username as submitter, u2.username as reviewer 
          FROM genomics_species g 
          JOIN users u1 ON g.submitted_by = u1.id 
          LEFT JOIN users u2 ON g.reviewed_by = u2.id 
          WHERE g.status != 'pending' 
          ORDER BY g.updated_at DESC";
$reviewedSpecies = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Species - Admin Panel</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .species-card {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
        }
        .species-image {
            max-width: 200px;
            max-height: 200px;
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="container mt-4">
        <h2>Species Review</h2>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">操作成功!</div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <h3 class="mt-4">Pending Review</h3>
        <?php if ($pendingSpecies->num_rows > 0): ?>
            <?php while ($specie = $pendingSpecies->fetch_assoc()): ?>
            <div class="species-card">
                <div class="row">
                    <div class="col-md-3">
                        <?php if ($specie['image_url']): ?>
                        <img src="<?= htmlspecialchars($specie['image_url']) ?>" class="species-image img-fluid">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-9">
                        <h4><em><?= htmlspecialchars($specie['scientific_name']) ?></em></h4>
                        <p><strong>Submitted by:</strong> <?= htmlspecialchars($specie['submitter']) ?></p>
                        <p><strong>Submitted at:</strong> <?= htmlspecialchars($specie['created_at']) ?></p>
                        <p><?= htmlspecialchars(substr($specie['description'], 0, 200)) ?>...</p>
                        
                        <form method="post" class="d-inline">
                            <input type="hidden" name="species_id" value="<?= $specie['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-success">Approve</button>
                        </form>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="species_id" value="<?= $specie['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </form>
                        <a href="species_detail.php?id=<?= $specie['id'] ?>" class="btn btn-info">View Details</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No species pending review.</p>
        <?php endif; ?>
        
        <h3 class="mt-4">Reviewed Species</h3>
        <?php if ($reviewedSpecies->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Scientific Name</th>
                        <th>Status</th>
                        <th>Submitter</th>
                        <th>Reviewer</th>
                        <th>Updated At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($specie = $reviewedSpecies->fetch_assoc()): ?>
                    <tr>
                        <td><em><?= htmlspecialchars($specie['scientific_name']) ?></em></td>
                        <td><?= get_status_badge($specie['status']) ?></td>
                        <td><?= htmlspecialchars($specie['submitter']) ?></td>
                        <td><?= htmlspecialchars($specie['reviewer'] ?? 'N/A') ?></td>
                        <td><?= format_db_time($specie['updated_at']) ?></td>
                        <td>
                            <a href="species_detail.php?id=<?= $specie['id'] ?>" class="btn btn-sm btn-info">View</a>
                            <a href="edit_species.php?id=<?= $specie['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No reviewed species yet.</p>
        <?php endif; ?>
    </div>
    
    <?php include 'admin_footer.php'; ?>
</body>
</html>