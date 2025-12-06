<?php
// student/process_booking.php
require_once('../db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $student_id = trim($_POST['student_id']);
    $seat_number = isset($_POST['seat_number']) ? trim($_POST['seat_number']) : null; // สำหรับ Lab
    $room_type_from_form = isset($_POST['room_type']) ? $_POST['room_type'] : null; // สำหรับ Lecture

    if ($course_id == 0 || empty($student_id)) {
        header('Location: select_course.php?message=invalid_data');
        exit();
    }

    // ตรวจสอบวิชาและสถานะ hidden และ room_type จาก database
    $stmt_course = $pdo->prepare("SELECT room_type FROM courses WHERE course_id = ? AND is_hidden = 0");
    $stmt_course->execute([$course_id]);
    $course = $stmt_course->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        header('Location: select_course.php?message=course_not_found_or_not_available');
        exit();
    }
    $actual_room_type = $course['room_type'];

    // ตรวจสอบว่านักศึกษาอยู่ในรายชื่อของวิชานี้หรือไม่
    $stmt_check_student = $pdo->prepare("SELECT COUNT(*) FROM course_students WHERE course_id = ? AND student_id = ?");
    $stmt_check_student->execute([$course_id, $student_id]);
    if ($stmt_check_student->fetchColumn() == 0) {
        // Redirect กลับไปหน้าเดิมด้วย message แจ้ง error
        if ($actual_room_type == 'lab') {
            header('Location: lab_booking.php?course_id=' . $course_id . '&message=student_not_registered');
        } else {
            header('Location: lecture_checkin.php?course_id=' . $course_id . '&message=student_not_registered');
        }
        exit();
    }

    // ตรวจสอบการจองซ้ำสำหรับคาบเดียวกัน (สถานะ active)
    $stmt_check_duplicate = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE course_id = ? AND student_id = ? AND status = 'active'");
    $stmt_check_duplicate->execute([$course_id, $student_id]);
    if ($stmt_check_duplicate->fetchColumn() > 0) {
        header('Location: confirmation.php?status=duplicate&course_id=' . $course_id . '&student_id=' . $student_id);
        exit();
    }

    // สำหรับห้อง Lab: ตรวจสอบที่นั่ง
    if ($actual_room_type == 'lab') {
        if (empty($seat_number)) {
            header('Location: lab_booking.php?course_id=' . $course_id . '&message=no_seat_selected');
            exit();
        }
        $stmt_check_seat = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE course_id = ? AND seat_number = ? AND status = 'active'");
        $stmt_check_seat->execute([$course_id, $seat_number]);
        if ($stmt_check_seat->fetchColumn() > 0) {
            header('Location: lab_booking.php?course_id=' . $course_id . '&message=seat_already_booked');
            exit();
        }
    } else { // สำหรับ Lecture
        $seat_number = null; // ไม่มีที่นั่งสำหรับ Lecture
    }

    try {
        // บันทึกการจอง/การเช็คอิน
        $stmt = $pdo->prepare("INSERT INTO bookings (course_id, student_id, seat_number, check_in_time, session_date, status) VALUES (?, ?, ?, NOW(), CURDATE(), 'active')");
        $stmt->execute([$course_id, $student_id, $seat_number]);

        header('Location: confirmation.php?status=success&course_id=' . $course_id . '&student_id=' . $student_id . '&seat_number=' . ($seat_number ?? ''));
        exit();
    } catch (PDOException $e) {
        // Handle database error
        header('Location: select_course.php?message=db_error&detail=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: select_course.php?message=invalid_request');
    exit();
}
?>