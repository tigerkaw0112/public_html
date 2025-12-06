<?php
// teacher/get_active_bookings.php
session_start();
if (!isset($_SESSION['user_id'])) { // ตรวจสอบการล็อกอิน
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
require_once('../db_connect.php');

header('Content-Type: application/json');

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
if ($course_id == 0) {
    echo json_encode([]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT b.student_id, s.full_name, b.seat_number, b.check_in_time
        FROM bookings b
        JOIN students s ON b.student_id = s.student_id
        WHERE b.course_id = ? AND b.status = 'active'
        ORDER BY b.check_in_time ASC
    ");
    $stmt->execute([$course_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($bookings);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>