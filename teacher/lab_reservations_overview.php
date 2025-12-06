<?php
// teacher/lab_reservations_overview.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once('../db_connect.php');

$teacher_id = $_SESSION['user_id'];

// ดึงรายการวิชา Lab ทั้งหมดที่อาจารย์สอน
$stmt_lab_courses = $pdo->prepare("SELECT course_id, course_name, course_code, section, room_name FROM courses WHERE created_by_user_id = ? AND room_type = 'lab' ORDER BY course_name ASC");
$stmt_lab_courses->execute([$teacher_id]);
$lab_courses = $stmt_lab_courses->fetchAll(PDO::FETCH_ASSOC);

$selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : (empty($lab_courses) ? 0 : $lab_courses[0]['course_id']);
$current_course_info = null;
$booked_seats_data = []; // จะเก็บ seat_number => array('full_name' => 'ชื่อ', 'student_id' => 'รหัส')

if ($selected_course_id > 0) {
    $stmt_current_course = $pdo->prepare("SELECT course_name, course_code, section, room_name FROM courses WHERE course_id = ? AND created_by_user_id = ? AND room_type = 'lab'");
    $stmt_current_course->execute([$selected_course_id, $teacher_id]);
    $current_course_info = $stmt_current_course->fetch(PDO::FETCH_ASSOC);

    if ($current_course_info) {
        // ดึงข้อมูลการจองที่นั่งสำหรับวิชาที่เลือก (status = 'active')
        $stmt_bookings = $pdo->prepare("
            SELECT b.seat_number, s.full_name, s.student_id, b.check_in_time
            FROM bookings b
            JOIN students s ON b.student_id = s.student_id
            WHERE b.course_id = ? AND b.status = 'active'
        ");
        $stmt_bookings->execute([$selected_course_id]);
        $bookings_raw = $stmt_bookings->fetchAll(PDO::FETCH_ASSOC);
        foreach ($bookings_raw as $booking) {
            $booked_seats_data[$booking['seat_number']] = [
                'full_name' => $booking['full_name'],
                'student_id' => $booking['student_id'],
                'check_in_time' => $booking['check_in_time']
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ภาพรวมการจอง Lab</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
            color: #f5f5f5;
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255, 107, 53, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 140, 66, 0.1) 0%, transparent 50%);
            animation: float 25s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(-30px, -30px) rotate(1deg); }
            66% { transform: translate(30px, -20px) rotate(-1deg); }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 25px;
            position: relative;
            z-index: 1;
        }

        /* Enhanced Header */
        .page-header {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 50%, #ff4500 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.3),
                0 0 30px rgba(255, 107, 53, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
            animation: shimmer 4s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .page-header-content {
            position: relative;
            z-index: 2;
        }

        .page-header h2 {
            font-size: 2.2em;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .page-header h3 {
            font-size: 1.3em;
            font-weight: 500;
            opacity: 0.9;
        }

        .page-header i {
            margin-right: 15px;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.3));
        }

        /* Back Button */
        .back-button {
            position: fixed;
            top: 25px;
            left: 25px;
            background: linear-gradient(135deg, #404040 0%, #555 100%);
            color: #f5f5f5;
            border: 2px solid #666;
            border-radius: 12px;
            padding: 12px 20px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        .back-button:hover {
            border-color: #ff6b35;
            color: #ff6b35;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        .back-button i {
            margin-right: 8px;
        }

        /* Section Boxes */
        .section-box {
            background: linear-gradient(145deg, #2d2d2d 0%, #404040 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 
                0 15px 35px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border: 2px solid #555;
        }

        .section-box h3 {
            color: #ff6b35;
            font-size: 1.8em;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .section-box h3 i {
            margin-right: 15px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .section-box h3::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, #ff6b35, transparent);
            margin-left: 20px;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            color: #ff8c42;
            font-size: 1.1em;
        }

        select {
            width: 100%;
            padding: 15px 20px;
            background: #1a1a1a; /* เปลี่ยนเป็นสีดำ */
            border: 2px solid #555;
            border-radius: 12px;
            color: #ffffff; /* ตัวอักษรสีขาว */
            font-family: inherit;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        select:focus {
            border-color: #ff6b35;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.3);
            background: #000000; /* พื้นหลังสีดำเข้มขึ้นตอน focus */
        }

        /* Real-time Indicator */
        .realtime-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #28a745;
            font-size: 0.9em;
            margin-left: 15px;
        }

        .realtime-dot {
            width: 8px;
            height: 8px;
            background: #28a745;
            border-radius: 50%;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }

        /* Lab Map Wrapper */
        .lab-map-wrapper {
            background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 100%);
            border-radius: 15px;
            padding: 30px;
            margin: 25px 0;
            border: 2px solid #555;
            box-shadow: inset 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .map-boundary {
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
            font-weight: 600;
            font-size: 1.1em;
            border-radius: 10px;
            margin: 0 auto 25px auto;
            max-width: 300px;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .bottom-boundary {
            margin: 25px auto 0 auto;
        }

        /* Seat Map Styling */
        .seat-map {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .seat-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
        }

        .row-label {
            background: linear-gradient(135deg, #ff8c42 0%, #ff6b35 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9em;
            min-width: 80px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(255, 107, 53, 0.2);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .seat-center-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .seat-block-left,
        .seat-block-right {
            display: flex;
            gap: 8px;
        }

        .seat-gap {
            width: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .seat-gap::after {
            content: '';
            width: 2px;
            height: 30px;
            background: linear-gradient(to bottom, transparent, #666, transparent);
        }

        /* Enhanced Seats */
        .seat {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8em;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid transparent;
            position: relative;
            overflow: visible;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }



        /* Available Seat */
        .seat.available {
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .seat.available:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
            border-color: #66bb6a;
        }

        /* Booked Seat */
        .seat.booked {
            background: linear-gradient(135deg, #f44336 0%, #e57373 100%);
            color: white;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
            position: relative;
        }

        .seat.booked::after {
            content: '👤';
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 0.7em;
            background: #ff9800;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            z-index: 3;
        }

        .seat.booked:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(244, 67, 54, 0.4);
        }

        /* Enhanced Tooltip - แสดงเร็วขึ้นและไวกว่าเดิม */
        .seat-tooltip {
            position: absolute;
            bottom: 120%;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #2d2d2d 0%, #404040 100%);
            color: #f5f5f5;
            padding: 15px 18px;
            border-radius: 12px;
            font-size: 0.9em;
            white-space: nowrap;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            border: 2px solid #ff8c42;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease; /* ลดจาก 0.6s เป็น 0.2s */
            transition-delay: 0s; /* ลดจาก 0.8s เป็น 0s */
            z-index: 1000;
            pointer-events: none;
            min-width: 200px;
            text-align: center;
        }

        .seat-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 8px solid transparent;
            border-top-color: #ff8c42;
        }

        /* แสดง tooltip ทันทีเมื่อ active */
        .seat.booked.tooltip-active .seat-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(-10px);
            transition-delay: 0s; /* แสดงทันที */
        }

        /* ซ่อน tooltip เร็วขึ้น */
        .seat.booked:not(.tooltip-active) .seat-tooltip {
            transition: all 0.1s ease; /* ลดเวลาการซ่อนเป็น 0.1s */
            transition-delay: 0s; /* ซ่อนทันที */
        }

        .tooltip-name {
            font-weight: 700;
            color: #ff8c42;
            margin-bottom: 6px;
            font-size: 1.1em;
        }

        .tooltip-id {
            font-size: 0.9em;
            color: #ccc;
            margin-bottom: 6px;
        }

        .tooltip-time {
            font-size: 0.85em;
            color: #28a745;
            font-weight: 500;
        }

        /* Click Info Panel */
        .seat-info-panel {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(145deg, #2d2d2d 0%, #404040 100%);
            color: #f5f5f5;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
            border: 3px solid #ff6b35;
            z-index: 2000;
            min-width: 350px;
            text-align: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .seat-info-panel.show {
            opacity: 1;
            visibility: visible;
        }

        .seat-info-panel h3 {
            color: #ff6b35;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .seat-info-detail {
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .seat-info-label {
            color: #ff8c42;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .seat-info-value {
            font-size: 1.1em;
            font-weight: 500;
        }

        .close-panel {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-family: inherit;
            font-weight: 600;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .close-panel:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
        }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* Legend */
        .seat-legend {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 25px 0;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            background: linear-gradient(145deg, #404040 0%, #2d2d2d 100%);
            border-radius: 10px;
            border: 1px solid #555;
            transition: all 0.3s ease;
        }

        .legend-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .legend-seat {
            width: 25px;
            height: 25px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7em;
            font-weight: 600;
            position: relative;
        }

        .legend-seat.available {
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            color: white;
        }

        .legend-seat.booked {
            background: linear-gradient(135deg, #f44336 0%, #e57373 100%);
            color: white;
        }

        .legend-seat.booked::after {
            content: '👤';
            position: absolute;
            top: -4px;
            right: -4px;
            font-size: 0.6em;
            background: #ff9800;
            border-radius: 50%;
            width: 12px;
            height: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Statistics */
        .lab-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: linear-gradient(145deg, #404040 0%, #2d2d2d 100%);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #555;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .stat-number {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-number.available { color: #4caf50; }
        .stat-number.booked { color: #f44336; }
        .stat-number.total { color: #ff8c42; }

        .stat-label {
            color: #ccc;
            font-size: 0.9em;
        }

        /* No Data */
        .no-data {
            text-align: center;
            padding: 60px 30px;
            background: linear-gradient(145deg, #404040 0%, #2d2d2d 100%);
            border-radius: 20px;
            border: 2px dashed #666;
            color: #ccc;
        }

        .no-data i {
            font-size: 4em;
            color: #ff6b35;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .no-data p {
            font-size: 1.3em;
            margin-bottom: 10px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            
            .page-header {
                padding: 30px 20px;
            }
            
            .page-header h2 {
                font-size: 1.8em;
            }
            
            .seat-legend {
                gap: 15px;
            }
            
            .seat-row {
                gap: 6px;
            }
            
            .seat {
                width: 35px;
                height: 35px;
                font-size: 0.75em;
            }
            
            .row-label {
                min-width: 60px;
                font-size: 0.8em;
                padding: 6px 10px;
            }
            
            .back-button {
                top: 15px;
                left: 15px;
                padding: 10px 15px;
                font-size: 0.9em;
            }

            .lab-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .page-header h2 {
                font-size: 1.5em;
            }
            
            .seat {
                width: 30px;
                height: 30px;
                font-size: 0.7em;
            }
            
            .seat-gap {
                width: 20px;
            }
            
            .row-label {
                min-width: 50px;
                font-size: 0.75em;
            }

            .lab-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i>กลับ Dashboard
    </a>

    <div class="container">
        <div class="page-header">
            <div class="page-header-content">
                <h2><i class="fas fa-desktop"></i>ภาพรวมการจอง Lab</h2>
                <h3>ติดตามการใช้งานห้อง Lab แบบ Real-time</h3>
            </div>
        </div>

        <div class="section-box">
            <h3><i class="fas fa-book"></i>เลือกวิชา Lab</h3>
            <div class="form-group">
                <label for="courseSelector">วิชา Lab ของคุณ:</label>
                <select id="courseSelector" name="course_id">
                    <?php if (empty($lab_courses)): ?>
                        <option value="0">ไม่มีวิชา Lab ที่คุณสอน</option>
                    <?php else: ?>
                        <?php foreach ($lab_courses as $lab_course): ?>
                            <option value="<?php echo $lab_course['course_id']; ?>" <?php echo ($lab_course['course_id'] == $selected_course_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lab_course['course_name'] . ' (' . $lab_course['course_code'] . '-' . $lab_course['section'] . ') - ' . $lab_course['room_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="section-box">
            <h3>
                <i class="fas fa-map-marked-alt"></i>แผนที่นั่งห้อง Lab
                <span class="realtime-indicator">
                    <span class="realtime-dot"></span>
                    Real-time
                </span>
            </h3>
            
            <?php if ($selected_course_id == 0 && !empty($lab_courses)): ?>
                <div class="no-data">
                    <i class="fas fa-book-open"></i>
                    <p>กรุณาเลือกวิชา Lab จากด้านบนเพื่อแสดงแผนที่นั่ง</p>
                </div>
            <?php elseif ($selected_course_id == 0 && empty($lab_courses)): ?>
                <div class="no-data">
                    <i class="fas fa-book-open"></i>
                    <p>คุณยังไม่มีวิชา Lab ที่สร้างไว้</p>
                    <a href="add_course.php" style="color: #ff6b35; text-decoration: none; margin-top: 10px; display: inline-block;">
                        <i class="fas fa-plus"></i> สร้างวิชา Lab
                    </a>
                </div>
            <?php elseif (!$current_course_info): ?>
                <div class="no-data">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>ไม่พบวิชา Lab ที่เลือก หรือคุณไม่มีสิทธิ์</p>
                </div>
            <?php else: ?>
                <!-- Statistics -->
                <div class="lab-stats" id="labStats">
                    <div class="stat-card">
                        <div class="stat-number total" id="totalSeats">170</div>
                        <div class="stat-label">ที่นั่งทั้งหมด</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number available" id="availableSeats">0</div>
                        <div class="stat-label">ที่นั่งว่าง</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number booked" id="bookedSeats">0</div>
                        <div class="stat-label">ที่นั่งที่จอง</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number total" id="occupancyRate">0%</div>
                        <div class="stat-label">อัตราการใช้งาน</div>
                    </div>
                </div>

                <div class="seat-legend">
                    <div class="legend-item">
                        <div class="legend-seat available">A1</div>
                        <span>ที่นั่งว่าง</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat booked">B2</div>
                        <span>ที่นั่งที่มีคนนั่ง (คลิกเพื่อดูรายละเอียด)</span>
                    </div>
                </div>
                
                <div class="lab-map-wrapper">
                    <div class="map-boundary">หน้าสุด / Front</div>

                    <div class="seat-map" id="labSeatMapDisplay">
                        <div class="no-data">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>กำลังโหลดแผนที่นั่ง...</p>
                        </div>
                    </div>

                    <div class="map-boundary bottom-boundary">หลังสุด / Back</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal for Seat Information -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeSeatInfo()"></div>
    <div class="seat-info-panel" id="seatInfoPanel">
        <h3><i class="fas fa-chair"></i> ข้อมูลที่นั่ง <span id="modalSeatNumber"></span></h3>
        
        <div class="seat-info-detail">
            <div class="seat-info-label"><i class="fas fa-user"></i> ชื่อ-สกุล:</div>
            <div class="seat-info-value" id="modalStudentName">-</div>
        </div>
        
        <div class="seat-info-detail">
            <div class="seat-info-label"><i class="fas fa-id-card"></i> รหัสนักศึกษา:</div>
            <div class="seat-info-value" id="modalStudentId">-</div>
        </div>
        
        <div class="seat-info-detail">
            <div class="seat-info-label"><i class="fas fa-clock"></i> เวลาเข้าเรียน:</div>
            <div class="seat-info-value" id="modalCheckInTime">-</div>
        </div>
        
        <button class="close-panel" onclick="closeSeatInfo()">
            <i class="fas fa-times"></i> ปิด
        </button>
    </div>

    <script>
        function showSeatInfo(seatNumber, studentName, studentId, checkInTime) {
            document.getElementById('modalSeatNumber').textContent = seatNumber;
            document.getElementById('modalStudentName').textContent = studentName;
            document.getElementById('modalStudentId').textContent = studentId;
            document.getElementById('modalCheckInTime').textContent = checkInTime;
            
            document.getElementById('modalOverlay').classList.add('show');
            document.getElementById('seatInfoPanel').classList.add('show');
        }

        function closeSeatInfo() {
            document.getElementById('modalOverlay').classList.remove('show');
            document.getElementById('seatInfoPanel').classList.remove('show');
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSeatInfo();
            }
        });

                // Enhanced tooltip control - แสดงเฉพาะเมื่อเมาส์อยู่ตรงกลางจริงๆ
        // Enhanced tooltip control - แสดงเร็วขึ้นและไวกว่าเดิม
        let tooltipTimer = null;

        function handleSeatHover() {
            // เพิ่ม event listeners สำหรับที่นั่งที่มีคนนั่ง
            document.querySelectorAll('.seat.booked').forEach(seat => {
                seat.addEventListener('mouseenter', function(e) {
                    // ลดเวลาจาก 600ms เป็น 150ms (เร็วขึ้น 4 เท่า)
                    tooltipTimer = setTimeout(() => {
                        const rect = this.getBoundingClientRect();
                        const mouseX = e.clientX;
                        const mouseY = e.clientY;

                        // ขยายพื้นที่การ detect จาก 35% เป็น 50% (ไวกว่าเดิม)
                        const centerX = rect.left + rect.width / 2;
                        const centerY = rect.top + rect.height / 2;
                        const maxDistance = Math.min(rect.width, rect.height) * 0.5; // เพิ่มจาก 0.35 เป็น 0.5

                        const distance = Math.sqrt(
                            Math.pow(mouseX - centerX, 2) + Math.pow(mouseY - centerY, 2)
                        );

                        if (distance <= maxDistance) {
                            this.classList.add('tooltip-active');
                        }
                    }, 150); // ลดจาก 600ms เป็น 150ms
                });

                seat.addEventListener('mousemove', function(e) {
                    // ตรวจสอบตำแหน่งเมาส์แบบ real-time ด้วยพื้นที่ที่ใหญ่ขึ้น
                    const rect = this.getBoundingClientRect();
                    const mouseX = e.clientX;
                    const mouseY = e.clientY;

                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;
                    const maxDistance = Math.min(rect.width, rect.height) * 0.5; // เพิ่มจาก 0.35 เป็น 0.5

                    const distance = Math.sqrt(
                        Math.pow(mouseX - centerX, 2) + Math.pow(mouseY - centerY, 2)
                    );

                    // ถ้าเมาส์อยู่ในพื้นที่ แสดง tooltip ทันที (ไม่ต้องรอ timer)
                    if (distance <= maxDistance) {
                        // ยกเลิก timer เดิมและแสดงทันที
                        if (tooltipTimer) {
                            clearTimeout(tooltipTimer);
                            tooltipTimer = null;
                        }
                        this.classList.add('tooltip-active');
                    } else {
                        this.classList.remove('tooltip-active');
                        if (tooltipTimer) {
                            clearTimeout(tooltipTimer);
                            tooltipTimer = null;
                        }
                    }
                });

                seat.addEventListener('mouseleave', function() {
                    this.classList.remove('tooltip-active');
                    if (tooltipTimer) {
                        clearTimeout(tooltipTimer);
                        tooltipTimer = null;
                    }
                });
            });
        }
        function loadLabReservations() {
            const courseId = document.getElementById('courseSelector').value;
            if (!courseId || courseId == 0) {
                document.getElementById('labSeatMapDisplay').innerHTML = `
                    <div class="no-data">
                        <i class="fas fa-book-open"></i>
                        <p>กรุณาเลือกวิชา Lab ที่ต้องการดู</p>
                    </div>
                `;
                return;
            }

            fetch(`get_active_bookings.php?course_id=${courseId}`)
                .then(response => response.json())
                .then(data => {
                    const mapDiv = document.getElementById('labSeatMapDisplay');
                    const bookedSeatsInfo = {};
                    data.forEach(booking => {
                        bookedSeatsInfo[booking.seat_number] = {
                            name: booking.full_name,
                            id: booking.student_id,
                            time: booking.check_in_time
                        };
                    });

                    let html = '';
                    const max_total_seats = 170;
                    let total_seats_generated = 0;
                    let available_count = 0;
                    let booked_count = 0;
                    let row_char_code = 65; // 'A'

                    while (total_seats_generated < max_total_seats) {
                        const currentRow = String.fromCharCode(row_char_code);

                        let colsInRow;
                        if (currentRow === 'E' || currentRow === 'K') {
                            colsInRow = 10;
                        } else {
                            colsInRow = 12;
                        }

                        const remainingSeats = max_total_seats - total_seats_generated;
                        const colsToGenerate = Math.min(colsInRow, remainingSeats);

                        if (colsToGenerate <= 0) break;

                        html += `<div class='seat-row'>`;
                        html += `<div class='row-label'>${currentRow}</div>`;
                        html += `<div class='seat-center-container'>`;

                        // Left block
                        html += `<div class='seat-block-left'>`;
                        const numSeatsInLeftBlock = (colsInRow === 10) ? 5 : 6;
                        for (let c = 1; c <= numSeatsInLeftBlock; c++) {
                            if (total_seats_generated >= max_total_seats) break;
                            const seatLabel = `${currentRow}${c}`;
                            const isBooked = bookedSeatsInfo.hasOwnProperty(seatLabel);
                            const className = isBooked ? 'booked' : 'available';
                            
                            if (isBooked) {
                                booked_count++;
                                const studentInfo = bookedSeatsInfo[seatLabel];
                                html += `<div class='seat ${className}' data-seat='${seatLabel}' 
                                    data-student-name='${studentInfo.name}' 
                                    data-student-id='${studentInfo.id}' 
                                    data-check-in-time='${studentInfo.time}'
                                    onclick='showSeatInfo("${seatLabel}", "${studentInfo.name}", "${studentInfo.id}", "${studentInfo.time}")'>
                                    ${seatLabel}
                                    <div class='seat-tooltip'>
                                        <div class='tooltip-name'>${studentInfo.name}</div>
                                        <div class='tooltip-id'>รหัส: ${studentInfo.id}</div>
                                        <div class='tooltip-time'>เข้าเรียน: ${studentInfo.time}</div>
                                    </div>
                                </div>`;
                            } else {
                                available_count++;
                                html += `<div class='seat ${className}' data-seat='${seatLabel}'>${seatLabel}</div>`;
                            }
                            total_seats_generated++;
                        }
                        html += `</div>`;

                        // Gap
                        if (colsToGenerate > numSeatsInLeftBlock) {
                            html += `<div class='seat-gap'></div>`;
                        }

                        // Right block
                        if (colsToGenerate > numSeatsInLeftBlock) {
                            html += `<div class='seat-block-right'>`;
                            for (let c = numSeatsInLeftBlock + 1; c <= colsToGenerate; c++) {
                                if (total_seats_generated >= max_total_seats) break;
                                const seatLabel = `${currentRow}${c}`;
                                const isBooked = bookedSeatsInfo.hasOwnProperty(seatLabel);
                                const className = isBooked ? 'booked' : 'available';
                                
                                if (isBooked) {
                                    booked_count++;
                                    const studentInfo = bookedSeatsInfo[seatLabel];
                                    html += `<div class='seat ${className}' data-seat='${seatLabel}' 
                                        data-student-name='${studentInfo.name}' 
                                        data-student-id='${studentInfo.id}' 
                                        data-check-in-time='${studentInfo.time}'
                                        onclick='showSeatInfo("${seatLabel}", "${studentInfo.name}", "${studentInfo.id}", "${studentInfo.time}")'>
                                        ${seatLabel}
                                        <div class='seat-tooltip'>
                                            <div class='tooltip-name'>${studentInfo.name}</div>
                                            <div class='tooltip-id'>รหัส: ${studentInfo.id}</div>
                                            <div class='tooltip-time'>เข้าเรียน: ${studentInfo.time}</div>
                                        </div>
                                    </div>`;
                                } else {
                                    available_count++;
                                    html += `<div class='seat ${className}' data-seat='${seatLabel}'>${seatLabel}</div>`;
                                }
                                total_seats_generated++;
                            }
                            html += `</div>`;
                        }

                        html += `</div>`;
                        html += `<div class='row-label'>${currentRow}</div>`;
                        html += `</div>`;

                        row_char_code++;
                    }
                    
                    mapDiv.innerHTML = html;

                    // เรียกใช้ tooltip control หลังจากสร้าง HTML ใหม่
                    handleSeatHover();

                    // Update statistics
                    const occupancyRate = total_seats_generated > 0 ? Math.round((booked_count / total_seats_generated) * 100) : 0;
                    document.getElementById('totalSeats').textContent = total_seats_generated;
                    document.getElementById('availableSeats').textContent = available_count;
                    document.getElementById('bookedSeats').textContent = booked_count;
                    document.getElementById('occupancyRate').textContent = occupancyRate + '%';

                    // Update page header with current course info
                    const currentCourse = <?php echo json_encode($current_course_info); ?>;
                    if (currentCourse) {
                        document.querySelector('.page-header h2').innerHTML = `<i class="fas fa-desktop"></i>ภาพรวม Lab: ${currentCourse.course_name}`;
                        document.querySelector('.page-header h3').textContent = `${currentCourse.course_code} - ${currentCourse.section} | ห้อง: ${currentCourse.room_name}`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('labSeatMapDisplay').innerHTML = `
                        <div class="no-data">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
                        </div>
                    `;
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadLabReservations();
            setInterval(loadLabReservations, 5000); // Update every 5 seconds

            // Handle course selection change
            document.getElementById('courseSelector').addEventListener('change', function() {
                const selectedCourseId = this.value;
                if (selectedCourseId && selectedCourseId != 0) {
                    window.location.href = `lab_reservations_overview.php?course_id=${selectedCourseId}`;
                }
            });
        });
    </script>
</body>
</html>