<?php
// student/confirmation.php
require_once('../db_connect.php');

// ตั้งค่า Time Zone ของ PHP Script ให้เป็นประเทศไทย
date_default_timezone_set('Asia/Bangkok');

$status = isset($_GET['status']) ? $_GET['status'] : '';
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$student_id = isset($_GET['student_id']) ? htmlspecialchars($_GET['student_id']) : '';
$seat_number = isset($_GET['seat_number']) ? htmlspecialchars($_GET['seat_number']) : '';

$course_info = null;
$student_name = '';
$check_in_time_display = date('Y-m-d H:i:s');

if ($course_id > 0) {
    $stmt_course = $pdo->prepare("SELECT course_name, course_code, section, room_name, room_type FROM courses WHERE course_id = ?");
    $stmt_course->execute([$course_id]);
    $course_info = $stmt_course->fetch(PDO::FETCH_ASSOC);

    $stmt_student = $pdo->prepare("SELECT full_name FROM students WHERE student_id = ?");
    $stmt_student->execute([$student_id]);
    $student_info = $stmt_student->fetch(PDO::FETCH_ASSOC);
    if ($student_info) {
        $student_name = $student_info['full_name'];
    }

    // ดึงเวลาที่บันทึกจริงๆ จากฐานข้อมูล
    $stmt_booking_time = $pdo->prepare("SELECT check_in_time FROM bookings WHERE course_id = ? AND student_id = ? AND status = 'active' ORDER BY check_in_time DESC LIMIT 1");
    $stmt_booking_time->execute([$course_id, $student_id]);
    $booking_time_info = $stmt_booking_time->fetch(PDO::FETCH_ASSOC);
    if ($booking_time_info) {
        $check_in_time_display = $booking_time_info['check_in_time'];
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันการจอง/เข้าเรียน</title>
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

        /* Animated background particles */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 25% 25%, rgba(255, 107, 53, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 75% 75%, rgba(255, 140, 66, 0.08) 0%, transparent 50%);
            animation: float 15s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-10px, -10px) scale(1.05); }
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        /* Enhanced Header */
        .page-header {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 40px;
            text-align: center;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.3),
                0 0 30px rgba(255, 107, 53, 0.2);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .page-header h2 {
            font-size: 2.2em;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }

        .page-header h3 {
            font-size: 1.3em;
            font-weight: 500;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .page-header i {
            margin-right: 12px;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.3));
        }

        /* Status Cards */
        .status-card {
            background: linear-gradient(145deg, #2d2d2d 0%, #404040 100%);
            border-radius: 20px;
            padding: 40px 30px;
            margin-bottom: 30px;
            box-shadow: 
                0 15px 35px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .status-card.success {
            border: 3px solid #28a745;
            background: linear-gradient(145deg, rgba(40, 167, 69, 0.2) 0%, #2d2d2d 100%);
        }

        .status-card.error {
            border: 3px solid #dc3545;
            background: linear-gradient(145deg, rgba(220, 53, 69, 0.2) 0%, #2d2d2d 100%);
        }

        .status-card.duplicate {
            border: 3px solid #ffc107;
            background: linear-gradient(145deg, rgba(255, 193, 7, 0.2) 0%, #2d2d2d 100%);
        }

        /* Status Icons */
        .status-icon {
            text-align: center;
            margin-bottom: 25px;
        }

        .status-icon i {
            font-size: 4em;
            margin-bottom: 15px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
        }

        .status-icon.success i {
            color: #28a745;
            animation: successPulse 2s ease-in-out infinite;
        }

        .status-icon.error i {
            color: #dc3545;
            animation: errorShake 0.5s ease-in-out;
        }

        .status-icon.duplicate i {
            color: #ffc107;
            animation: warningBlink 1.5s ease-in-out infinite;
        }

        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @keyframes warningBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .status-title {
            font-size: 1.8em;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .status-title.success { color: #28a745; }
        .status-title.error { color: #dc3545; }
        .status-title.duplicate { color: #ffc107; }

        /* Details Section */
        .details-section {
            background: linear-gradient(145deg, #2d2d2d 0%, #404040 100%);
            border: 2px solid #555;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 
                0 10px 25px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .details-title {
            color: #ff8c42;
            font-size: 1.4em;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
            border-bottom: 2px solid #ff8c42;
            padding-bottom: 10px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #555;
            transition: all 0.3s ease;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-item:hover {
            background: rgba(255, 140, 66, 0.1);
            border-radius: 8px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .detail-label {
            font-weight: 600;
            color: #ff8c42;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-value {
            font-weight: 500;
            color: #f5f5f5;
            text-align: right;
        }

        .detail-value.highlight {
            color: #ffd700;
            font-weight: 600;
        }

        /* Seat Badge */
        .seat-badge {
            background: linear-gradient(135deg, #ffd700 0%, #ffeb3b 100%);
            color: #1a1a1a;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
        }

        /* Room Type Badge */
        .room-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.85em;
            text-transform: uppercase;
        }

        .room-badge.lab {
            background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            color: white;
        }

        .room-badge.lecture {
            background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
            color: white;
        }

        /* Action Button */
        .action-section {
            text-align: center;
            margin-top: 40px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 18px 35px;
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.2em;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 
                0 8px 25px rgba(255, 107, 53, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 
                0 15px 35px rgba(255, 107, 53, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .btn i {
            margin-right: 10px;
            font-size: 1.1em;
        }

        /* Auto redirect countdown */
        .countdown {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: rgba(255, 140, 66, 0.1);
            border-radius: 10px;
            border: 1px solid rgba(255, 140, 66, 0.3);
        }

        .countdown-text {
            color: #ff8c42;
            font-weight: 500;
            font-size: 1em;
        }

        .countdown-number {
            color: #ffd700;
            font-weight: 700;
            font-size: 1.2em;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .page-header {
                padding: 30px 20px;
            }
            
            .page-header h2 {
                font-size: 1.8em;
            }
            
            .page-header h3 {
                font-size: 1.1em;
            }
            
            .status-card {
                padding: 30px 20px;
            }
            
            .status-icon i {
                font-size: 3em;
            }
            
            .status-title {
                font-size: 1.5em;
            }
            
            .detail-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .detail-value {
                text-align: left;
            }
            
            .btn {
                width: 100%;
                padding: 15px 25px;
                font-size: 1.1em;
            }
        }

        @media (max-width: 480px) {
            .page-header h2 {
                font-size: 1.5em;
            }
            
            .details-section {
                padding: 20px;
            }
            
            .status-card {
                padding: 25px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-clipboard-check"></i>ยืนยันการจอง/เข้าเรียน</h2>
            <?php if ($course_info): ?>
                <h3>สำหรับ: <?php echo htmlspecialchars($course_info['course_name']); ?></h3>
            <?php endif; ?>
        </div>

        <?php if ($status == 'success'): ?>
            <div class="status-card success">
                <div class="status-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="status-title success">การจอง/ยืนยันสำเร็จ!</div>
            </div>

            <?php if ($course_info): ?>
                <div class="details-section">
                    <div class="details-title">
                        <i class="fas fa-info-circle"></i> รายละเอียดการจอง/เข้าเรียน
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-book"></i> วิชา:
                        </span>
                        <span class="detail-value highlight">
                            <?php echo htmlspecialchars($course_info['course_name']); ?>
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-code"></i> รหัสวิชา - เซคชั่น:
                        </span>
                        <span class="detail-value">
                            <?php echo htmlspecialchars($course_info['course_code'] . ' - ' . $course_info['section']); ?>
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-door-open"></i> ห้อง:
                        </span>
                        <span class="detail-value">
                            <?php echo htmlspecialchars($course_info['room_name']); ?>
                            <span class="room-badge <?php echo $course_info['room_type']; ?>">
                                <?php echo htmlspecialchars($course_info['room_type'] == 'lab' ? 'ห้อง Lab' : 'ห้อง Lecture'); ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-id-card"></i> รหัสนักศึกษา:
                        </span>
                        <span class="detail-value highlight"><?php echo $student_id; ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-user"></i> ชื่อ-สกุล:
                        </span>
                        <span class="detail-value"><?php echo htmlspecialchars($student_name); ?></span>
                    </div>
                    
                    <?php if ($course_info && $course_info['room_type'] == 'lab' && !empty($seat_number)): ?>
                        <div class="detail-item">
                            <span class="detail-label">
                                <i class="fas fa-chair"></i> ที่นั่ง:
                            </span>
                            <span class="detail-value">
                                <span class="seat-badge">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo $seat_number; ?>
                                </span>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-clock"></i> เวลาที่บันทึก:
                        </span>
                        <span class="detail-value highlight"><?php echo $check_in_time_display; ?></span>
                    </div>
                </div>
            <?php endif; ?>

        <?php elseif ($status == 'duplicate'): ?>
            <div class="status-card duplicate">
                <div class="status-icon duplicate">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="status-title duplicate">ยืนยันแล้ว</div>
                <p style="text-align: center; margin-top: 15px; font-size: 1.1em;">
                    คุณได้ยืนยันการเข้าเรียน/จองที่นั่งสำหรับวิชานี้แล้วในคาบนี้!
                </p>
            </div>

            <div class="details-section">
                <div class="detail-item">
                    <span class="detail-label">
                        <i class="fas fa-id-card"></i> รหัสนักศึกษา:
                    </span>
                    <span class="detail-value highlight"><?php echo $student_id; ?></span>
                </div>
                
                <?php if ($course_info): ?>
                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fas fa-book"></i> วิชา:
                        </span>
                        <span class="detail-value"><?php echo htmlspecialchars($course_info['course_name']); ?></span>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="status-card error">
                <div class="status-icon error">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="status-title error">เกิดข้อผิดพลาด</div>
                <p style="text-align: center; margin-top: 15px; font-size: 1.1em;">
                    ไม่สามารถดำเนินการได้ หรือข้อมูลไม่ถูกต้อง
                </p>
            </div>
        <?php endif; ?>

        <div class="action-section">
            <a href="select_course.php" class="btn">
                <i class="fas fa-arrow-left"></i>กลับหน้าเลือกห้องเรียน
            </a>
            
            <?php if ($status == 'success'): ?>
                <div class="countdown">
                    <div class="countdown-text">
                        จะกลับหน้าเลือกวิชาอัตโนมัติใน 
                        <span class="countdown-number" id="countdown">10</span> วินาที
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto redirect for success status
        <?php if ($status == 'success'): ?>
        let countdown = 10;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = 'select_course.php';
            }
        }, 1000);
        
        // Click anywhere to cancel auto redirect
        document.addEventListener('click', () => {
            clearInterval(timer);
            const countdownDiv = document.querySelector('.countdown');
            if (countdownDiv) {
                countdownDiv.innerHTML = '<div class="countdown-text">การเปลี่ยนหน้าอัตโนมัติถูกยกเลิก</div>';
            }
        });
        <?php endif; ?>

        // Add page load animation
        document.addEventListener('DOMContentLoaded', function() {
            const statusCard = document.querySelector('.status-card');
            const detailsSection = document.querySelector('.details-section');
            const actionSection = document.querySelector('.action-section');
            
            // Animate elements in sequence
            setTimeout(() => {
                if (statusCard) {
                    statusCard.style.opacity = '0';
                    statusCard.style.transform = 'translateY(30px)';
                    statusCard.style.transition = 'all 0.6s ease';
                    statusCard.style.opacity = '1';
                    statusCard.style.transform = 'translateY(0)';
                }
            }, 100);
            
            setTimeout(() => {
                if (detailsSection) {
                    detailsSection.style.opacity = '0';
                    detailsSection.style.transform = 'translateY(30px)';
                    detailsSection.style.transition = 'all 0.6s ease';
                    detailsSection.style.opacity = '1';
                    detailsSection.style.transform = 'translateY(0)';
                }
            }, 300);
            
            setTimeout(() => {
                if (actionSection) {
                    actionSection.style.opacity = '0';
                    actionSection.style.transform = 'translateY(30px)';
                    actionSection.style.transition = 'all 0.6s ease';
                    actionSection.style.opacity = '1';
                    actionSection.style.transform = 'translateY(0)';
                }
            }, 500);
        });
    </script>
</body>
</html>