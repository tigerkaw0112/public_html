<?php
// teacher/logout.php
session_start();        // เริ่มต้นการทำงานของ Session
session_unset();        // ล้างตัวแปร Session ทั้งหมดที่ถูกตั้งค่าไว้
session_destroy();      // ทำลาย Session ทั้งหมด (ลบไฟล์ Session ออกจากเซิร์ฟเวอร์)
header('Location: login.php'); // Redirect ผู้ใช้กลับไปที่หน้า Login
exit();                 // หยุดการทำงานของ Script เพื่อให้แน่ใจว่าการ Redirect เกิดขึ้น
?>