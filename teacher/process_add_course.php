<?php
// teacher/process_add_course.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once('../db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code'];
    $section = $_POST['section'];
    $instructor_name = $_POST['instructor_name'];
    $room_type = $_POST['room_type'];
    $room_name = $_POST['room_name'];
    $created_by_user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO courses (course_name, course_code, section, instructor_name, room_type, room_name, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$course_name, $course_code, $section, $instructor_name, $room_type, $room_name, $created_by_user_id]);
        
        header('Location: dashboard.php?message=add_course_success'); // Redirect กลับ Dashboard
        exit();
    } catch (PDOException $e) {
        // Handle error, e.g., display error message
        header('Location: add_course.php?message=add_course_error&detail=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: add_course.php'); // ถ้าไม่ได้มาจาก POST ให้กลับไปหน้าเพิ่มวิชา
    exit();
}
?>