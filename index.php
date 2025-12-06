<?php
// index.php
session_start();

if (isset($_SESSION['user_id'])) {
    // ถ้าล็อกอินแล้วเป็นอาจารย์ ให้ไปหน้า Dashboard อาจารย์
    header('Location: teacher/dashboard.php');
    exit();
} else {
    // ถ้ายังไม่ล็อกอิน หรือเป็นนักศึกษา (เราจะใช้การกรอกรหัสสำหรับนักศึกษา)
    // ให้ไปหน้าเลือกวิชาสำหรับนักศึกษา
    header('Location: student/select_course.php');
    exit();
}
?>