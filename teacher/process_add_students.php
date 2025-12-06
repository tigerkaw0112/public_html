<?php
// teacher/process_add_students.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once('../db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $student_data_raw = $_POST['student_data'];

    if ($course_id == 0 || empty($student_data_raw)) {
        header('Location: view_bookings.php?course_id=' . $course_id . '&message=invalid_data');
        exit();
    }

    $lines = explode("\n", $student_data_raw);
    $added_count = 0;
    $errors = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // ลองแยกด้วยแท็บก่อน ถ้าไม่สำเร็จ ค่อยลองด้วยช่องว่าง
        $parts = preg_split('/\s+/', $line, 2); // Split by one or more spaces, max 2 parts

        if (count($parts) >= 2) {
            $student_id = $parts[0];
            $full_name = $parts[1];

            try {
                // 1. เพิ่มนักศึกษาเข้าตาราง students ถ้ายังไม่มี (IGNORE เพื่อไม่ให้ error ถ้ามีอยู่แล้ว)
                $stmt_insert_student = $pdo->prepare("INSERT IGNORE INTO students (student_id, full_name) VALUES (?, ?)");
                $stmt_insert_student->execute([$student_id, $full_name]);

                // 2. เพิ่มการเชื่อมโยงระหว่างนักศึกษากับวิชาใน course_students
                $stmt_add_to_course = $pdo->prepare("INSERT INTO course_students (course_id, student_id) VALUES (?, ?)");
                $stmt_add_to_course->execute([$course_id, $student_id]);
                $added_count++;

            } catch (PDOException $e) {
                // หากเกิดข้อผิดพลาด เช่น Unique constraint violation (นักศึกษาถูกเพิ่มในวิชานี้แล้ว)
                if ($e->getCode() == '23000') { // SQLSTATE for integrity constraint violation
                    $errors[] = "นักศึกษา " . htmlspecialchars($student_id) . " มีอยู่ในวิชานี้แล้ว";
                } else {
                    $errors[] = "Error adding student " . htmlspecialchars($student_id) . ": " . $e->getMessage();
                }
            }
        } else {
            $errors[] = "ข้อมูลรูปแบบไม่ถูกต้อง: " . htmlspecialchars($line);
        }
    }

    $redirect_message = 'add_students_success&count=' . $added_count;
    if (!empty($errors)) {
        $redirect_message = 'add_students_error&detail=' . urlencode(implode("||", $errors));
    }
    header('Location: view_bookings.php?course_id=' . $course_id . '&message=' . $redirect_message);
    exit();
} else {
    header('Location: dashboard.php');
    exit();
}
?>