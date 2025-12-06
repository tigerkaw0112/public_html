<?php
// student/lecture_checkin.php
require_once('../db_connect.php');

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
if ($course_id == 0) {
    header('Location: select_course.php');
    exit();
}

$stmt_course = $pdo->prepare("SELECT * FROM courses WHERE course_id = ? AND room_type = 'lecture' AND is_hidden = 0");
$stmt_course->execute([$course_id]);
$course = $stmt_course->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo "ไม่พบห้อง Lecture นี้หรือห้องนี้ไม่พร้อมใช้งาน";
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันการเข้าเรียน: <?php echo htmlspecialchars($course['course_name']); ?></title>
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
                radial-gradient(circle at 80% 20%, rgba(255, 140, 66, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 107, 53, 0.1) 0%, transparent 50%);
            animation: float 25s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(-30px, -30px) rotate(1deg); }
            66% { transform: translate(30px, -20px) rotate(-1deg); }
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px 25px;
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Enhanced Header */
        .page-header {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 50%, #ff4500 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 
                0 15px 30px rgba(0, 0, 0, 0.3),
                0 0 20px rgba(255, 107, 53, 0.3),
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
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .page-header h2 {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }

        .page-header h3 {
            font-size: 1.2em;
            font-weight: 500;
            opacity: 0.9;
            margin: 0;
            position: relative;
            z-index: 2;
        }

        .page-header i {
            margin-right: 12px;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.3));
        }

        /* Messages */
        .message {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: none;
            border-left: 4px solid;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .success-message {
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            color: white;
            border-color: #2e7d32;
        }

        .error-message {
            background: linear-gradient(135deg, #f44336 0%, #e57373 100%);
            color: white;
            border-color: #c62828;
        }

        .message i {
            font-size: 1.2em;
        }

        /* Form Container */
        form {
            background: linear-gradient(145deg, #2d2d2d 0%, #404040 100%);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 
                0 15px 35px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border: 2px solid #555;
            position: relative;
            overflow: hidden;
        }

        form::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 107, 53, 0.1), transparent);
            transition: left 0.8s ease;
        }

        form:hover::before {
            left: 100%;
        }

        /* Form Heading */
        form h3 {
            color: #ff8c42;
            font-size: 1.4em;
            font-weight: 600;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
            text-align: center;
            position: relative;
            z-index: 2;
        }

        form h3 i {
            margin-right: 12px;
            animation: checkPulse 2s infinite;
        }

        @keyframes checkPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.1); }
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 30px;
            position: relative;
            z-index: 2;
        }

        label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            color: #ff8c42;
            font-size: 1.1em;
            position: relative;
        }

        label::before {
            content: '👤';
            margin-right: 10px;
            font-size: 1.2em;
        }

        /* Enhanced Input Styling */
        input[type="text"] {
            width: 100%;
            padding: 18px 24px;
            background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 100%);
            border: 2px solid #555;
            border-radius: 15px;
            color: #ffffff;
            font-family: inherit;
            font-size: 1.2em;
            transition: all 0.3s ease;
            position: relative;
            text-align: center;
            font-weight: 500;
        }

        input[type="text"]:focus {
            border-color: #ff6b35;
            outline: none;
            box-shadow: 
                0 0 0 3px rgba(255, 107, 53, 0.3),
                0 0 20px rgba(255, 107, 53, 0.1);
            background: linear-gradient(145deg, #000000 0%, #1a1a1a 100%);
            transform: scale(1.02);
        }

        input[type="text"]::placeholder {
            color: #888;
            font-style: italic;
        }

        /* Enhanced Buttons */
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 20px 30px;
            text-decoration: none;
            border-radius: 15px;
            font-family: inherit;
            font-size: 1.3em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
            z-index: 2;
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

        .btn i {
            margin-right: 12px;
            font-size: 1.1em;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #e55a2b 0%, #ff6b35 100%);
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(255, 107, 53, 0.4);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        /* Loading State */
        .btn.loading {
            opacity: 0.8;
            pointer-events: none;
        }

        .btn.loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            
            form {
                padding: 25px;
            }
            
            .page-header {
                padding: 25px 20px;
            }
            
            .page-header h2 {
                font-size: 1.6em;
            }
            
            input[type="text"] {
                font-size: 1.1em;
                padding: 16px 20px;
            }
            
            .btn {
                font-size: 1.2em;
                padding: 18px 25px;
            }
            
            form h3 {
                font-size: 1.2em;
            }
        }

        @media (max-width: 480px) {
            .page-header h2 {
                font-size: 1.4em;
            }
            
            form {
                padding: 20px;
            }
            
            form h3 {
                font-size: 1.1em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-chalkboard"></i>ยืนยันการเข้าเรียน: <?php echo htmlspecialchars($course['course_name']); ?></h2>
            <h3>ห้อง: <?php echo htmlspecialchars($course['room_name']); ?></h3>
        </div>

        <div class="message success-message" id="successMessage">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="message error-message" id="errorMessage">
            <i class="fas fa-exclamation-circle"></i>
        </div>

        <form action="process_booking.php" method="POST">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
            <input type="hidden" name="room_type" value="lecture">

            <h3><i class="fas fa-user-check"></i>กรอกรหัสนักศึกษาเพื่อยืนยันการเข้าเรียน</h3>

            <div class="form-group">
                <label for="student_id">รหัสนักศึกษา:</label>
                <input type="text" id="student_id" name="student_id" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i>ยืนยันการเข้าเรียน</button>
        </form>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function(event) {
            const submitBtn = this.querySelector('button[type="submit"]');
            
            if (!document.getElementById('student_id').value) {
                // แสดงข้อความ error โดยใช้ element ที่เตรียมไว้
                document.getElementById('errorMessage').innerHTML = '<i class="fas fa-exclamation-circle"></i> กรุณากรอกรหัสนักศึกษา';
                document.getElementById('errorMessage').style.display = 'flex'; // แสดง message box
                event.preventDefault(); // Stop form submission
                return;
            }
            
            // Show loading state
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner"></i>กำลังตรวจสอบ...';
            submitBtn.disabled = true;
        });

        // Input enhancements
        document.getElementById('student_id').addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });

        document.getElementById('student_id').addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });

        // เมื่อ DOM โหลดเสร็จสิ้น, ตรวจสอบ URL เพื่อแสดงข้อความแจ้งเตือน (คัดลอกมาจาก select_course.php)
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const messageParam = urlParams.get('message');
            const detailParam = urlParams.get('detail');

            if (messageParam) {
                let messageText = '';
                let messageType = 'error'; 

                // --- ERROR MESSAGES (จาก process_booking.php) ---
                if (messageParam === 'invalid_data') {
                    messageText = 'ข้อมูลที่ส่งมาไม่ครบถ้วน';
                } else if (messageParam === 'course_not_found_or_not_available') {
                    messageText = 'ไม่พบวิชาหรือวิชาไม่พร้อมใช้งาน';
                } else if (messageParam === 'student_not_registered') {
                    messageText = 'รหัสนักศึกษาไม่ถูกต้องสำหรับวิชานี้';
                } else if (messageParam === 'duplicate') { // กรณีนี้จะถูก redirect ไปที่ confirmation.php โดยตรง
                    messageText = 'คุณได้ยืนยันการเข้าเรียนสำหรับวิชานี้แล้วในคาบนี้!';
                } else if (messageParam === 'db_error') {
                    messageText = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' + (detailParam || 'ไม่ทราบสาเหตุ');
                } else if (messageParam === 'invalid_request') {
                    messageText = 'การร้องขอไม่ถูกต้อง';
                }
                
                const messageElement = document.getElementById(messageType + 'Message');
                if (messageElement) {
                    messageElement.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${messageText}`;
                    messageElement.style.display = 'flex';
                    setTimeout(() => {
                        messageElement.style.display = 'none';
                    }, 5000);
                }
            }
        });
    </script>
</body>
</html>