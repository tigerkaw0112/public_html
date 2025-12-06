<?php
// teacher/process_delete_course.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once('../db_connect.php');

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($course_id == 0) {
    header('Location: dashboard.php?message=delete_error_invalid_id');
    exit();
}

try {
    // ตรวจสอบสิทธิ์: อาจารย์ที่ Login ต้องเป็นคนสร้างวิชานี้
    $stmt_check_owner = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_id = ? AND created_by_user_id = ?");
    $stmt_check_owner->execute([$course_id, $_SESSION['user_id']]);
    if ($stmt_check_owner->fetchColumn() == 0) {
        header('Location: dashboard.php?message=delete_error_unauthorized');
        exit();
    }

    // เริ่มต้น Transaction เพื่อให้แน่ใจว่าข้อมูลถูกลบทั้งหมดหรือไม่มีการลบเลย
    $pdo->beginTransaction();

    // เนื่องจากเราตั้งค่า FOREIGN KEY เป็น ON DELETE CASCADE ไว้ใน SQL
    // การลบวิชาในตาราง `courses` จะทำให้ข้อมูลที่เกี่ยวข้องใน
    // `course_students` และ `bookings` ถูกลบตามไปด้วยโดยอัตโนมัติ
    $stmt_delete = $pdo->prepare("DELETE FROM courses WHERE course_id = ?");
    $stmt_delete->execute([$course_id]);

    $pdo->commit(); // ยืนยันการเปลี่ยนแปลงในฐานข้อมูล

    header('Location: dashboard.php?message=delete_success');
    exit();
} catch (PDOException $e) {
    $pdo->rollBack(); // หากเกิดข้อผิดพลาด ให้ยกเลิกการเปลี่ยนแปลงทั้งหมด
    header('Location: dashboard.php?message=delete_error&detail=' . urlencode($e->getMessage()));
    exit();
}
?>