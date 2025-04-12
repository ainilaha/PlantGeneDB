<?php
session_start();
global $conn;
require_once __DIR__ . '/config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_id = (int)$_POST['upload_id'];
    $action = $_POST['action'];
    $reason = $_POST['reason'] ?? '';

    try {
        // 开启事务
        $conn->begin_transaction();

        // 更新上传状态
        $update_query = "
            UPDATE uploads 
            SET 
                status = ?,
                reviewed_at = NOW(),
                reject_reason = ?,
                updated_at = NOW()
            WHERE upload_id = ?
        ";

        $status = ($action === 'approve') ? 'approved' : 'rejected';
        $reject_reason = ($status === 'rejected') ? trim($reason) : null;

        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('sss', $status, $reject_reason, $upload_id);
        $stmt->execute();

        // 记录审核日志
        $log_query = "
            INSERT INTO audit_logs (upload_id, admin_id, action, reason)
            VALUES (?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($log_query);
        $stmt->bind_param('iiss', $upload_id, $_SESSION['user_id'], $action, $reason);
        $stmt->execute();

        // 提交事务
        $conn->commit();

        header("Location: review.php");
        exit();

    } catch (Exception $e) {
        // 回滚事务
        $conn->rollback();
        die("操作失败: " . $e->getMessage());
    }
}
?>