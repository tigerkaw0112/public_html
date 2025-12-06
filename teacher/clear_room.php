<?php
// teacher/clear_room.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once('../db_connect.php');

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
if ($course_id == 0) {
    header('Location: dashboard.php?message=clear_error');
    exit();
}

try {
    // อัปเดตสถานะของ bookings ที่ active ในวิชานี้
    $stmt = $pdo->prepare("
        UPDATE bookings
        SET status = 'cleared', session_date = CURDATE(), session_cleared_at = NOW()
        WHERE course_id = ? AND status = 'active'
    ");
    $stmt->execute([$course_id]);

    header('Location: view_bookings.php?course_id=' . $course_id . '&message=cleared_success');
    exit();
} catch (PDOException $e) {
    header('Location: view_bookings.php?course_id=' . $course_id . '&message=clear_error&detail=' . urlencode($e->getMessage()));
    exit();
}
?>