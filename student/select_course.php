<?php
// student/select_course.php
require_once('../db_connect.php');

$stmt = $pdo->prepare("SELECT * FROM courses WHERE is_hidden = 0 ORDER BY course_name ASC");
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกห้องเรียน/วิชา</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-orange: #ff6b35;
            --secondary-orange: #ff8c42;
            --accent-orange: #ff4500;
            --dark-black: #1a1a1a;
            --medium-black: #2d2d2d;
            --light-black: #404040;
            --text-light: #f5f5f5;
            --text-dark: #333;
            --border-color: #555;
            --hover-glow: rgba(255, 107, 53, 0.3);
            --success-green: #28a745;
            --error-red: #dc3545;
        }

        body {
            font-family: 'Kanit', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--dark-black) 0%, var(--medium-black) 50%, var(--dark-black) 100%);
            color: var(--text-light);
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 50%, rgba(255, 107, 53, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(255, 140, 66, 0.08) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(-20px, -20px) rotate(1deg); }
            66% { transform: translate(20px, -10px) rotate(-1deg); }
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
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 50%, var(--accent-orange) 100%);
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
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .page-header h2 {
            font-size: 2.5em;
            font-weight: 700;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }

        .page-header i {
            margin-right: 15px;
            font-size: 1.2em;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.3));
        }

        .page-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(255, 255, 255, 0.3) 25%, 
                rgba(255, 255, 255, 0.6) 50%, 
                rgba(255, 255, 255, 0.3) 75%, 
                transparent 100%);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        /* Enhanced Messages */
        .message {
            padding: 18px 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: none;
            border-left: 5px solid;
            align-items: center;
            gap: 15px;
            backdrop-filter: blur(10px);
            animation: slideInDown 0.5s ease;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        @keyframes slideInDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .success-message {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.9) 0%, rgba(40, 167, 69, 0.7) 100%);
            color: white;
            border-color: var(--success-green);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
        }

        .error-message {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.9) 0%, rgba(220, 53, 69, 0.7) 100%);
            color: white;
            border-color: var(--error-red);
            box-shadow: 0 10px 25px rgba(220, 53, 69, 0.3);
        }

        /* Enhanced Course List Header */
        .course-list-header {
            color: var(--primary-orange);
            font-size: 2em;
            font-weight: 600;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .course-list-header i {
            margin-right: 15px;
            animation: rotate 4s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .course-list-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--primary-orange), transparent);
            border-radius: 2px;
        }

        /* Enhanced Course Grid */
        .course-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .course-item {
            background: linear-gradient(145deg, var(--medium-black) 0%, var(--light-black) 100%);
            border: 2px solid var(--border-color);
            border-radius: 20px;
            padding: 30px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 
                0 10px 30px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .course-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 107, 53, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .course-item:hover::before {
            left: 100%;
        }

        .course-item:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: var(--primary-orange);
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.4),
                0 0 30px var(--hover-glow),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .course-item h3 {
            color: var(--primary-orange);
            font-size: 1.5em;
            font-weight: 600;
            margin-bottom: 20px;
            line-height: 1.3;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .course-item p {
            margin: 12px 0;
            font-size: 1em;
            color: var(--text-light);
            opacity: 0.9;
            line-height: 1.5;
        }

        .course-item p strong {
            color: var(--secondary-orange);
            font-weight: 500;
        }

        /* Enhanced Buttons */
        .course-item .btn {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 15px 20px;
            margin-top: 25px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1em;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 
                0 8px 20px rgba(255, 107, 53, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .course-item .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .course-item .btn:hover::before {
            left: 100%;
        }

        .course-item .btn:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 15px 30px rgba(255, 107, 53, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .course-item .btn:active {
            transform: translateY(-1px);
        }

        .course-item .btn i {
            margin-right: 10px;
            font-size: 1.1em;
        }

        /* Enhanced No Courses Message */
        .no-courses {
            text-align: center;
            padding: 60px 30px;
            background: linear-gradient(145deg, var(--medium-black) 0%, var(--light-black) 100%);
            border-radius: 20px;
            border: 2px dashed var(--border-color);
            color: var(--text-light);
            margin-top: 40px;
            box-shadow: 
                0 10px 30px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .no-courses i {
            font-size: 4em;
            color: var(--primary-orange);
            margin-bottom: 20px;
            animation: bounce 2s infinite;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .no-courses p {
            font-size: 1.3em;
            font-weight: 400;
            opacity: 0.8;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            
            .page-header {
                padding: 30px 20px;
                margin-bottom: 30px;
            }
            
            .page-header h2 {
                font-size: 1.8em;
            }
            
            .course-list {
                grid-template-columns: 1fr;
                gap: 25px;
            }
            
            .course-item {
                padding: 25px;
            }
            
            .course-item h3 {
                font-size: 1.3em;
            }
            
            .course-item .btn {
                padding: 12px 18px;
                font-size: 1em;
            }
            
            .course-list-header {
                font-size: 1.6em;
            }
        }

        @media (max-width: 480px) {
            .page-header h2 {
                font-size: 1.5em;
            }
            
            .course-list {
                gap: 20px;
            }
            
            .course-item {
                padding: 20px;
            }
            
            .no-courses {
                padding: 40px 20px;
            }
            
            .no-courses i {
                font-size: 3em;
            }
            
            .no-courses p {
                font-size: 1.1em;
            }
        }

        /* Loading animation for dynamic content */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .course-item {
            animation: fadeIn 0.6s ease forwards;
        }

        .course-item:nth-child(1) { animation-delay: 0.1s; }
        .course-item:nth-child(2) { animation-delay: 0.2s; }
        .course-item:nth-child(3) { animation-delay: 0.3s; }
        .course-item:nth-child(4) { animation-delay: 0.4s; }
        .course-item:nth-child(5) { animation-delay: 0.5s; }
        .course-item:nth-child(6) { animation-delay: 0.6s; }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-graduation-cap"></i>เลือกห้องเรียน / วิชา</h2>
        </div>

        <div class="message success-message" id="successMessage">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="message error-message" id="errorMessage">
            <i class="fas fa-exclamation-circle"></i>
        </div>

        <h3 class="course-list-header"><i class="fas fa-book-open"></i>วิชาที่เปิดสอนในขณะนี้</h3>

        <?php if (count($courses) > 0): ?>
            <div class="course-list">
                <?php foreach ($courses as $course): ?>
                    <div class="course-item">
                        <div>
                            <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                            <p><strong>รหัสวิชา:</strong> <?php echo htmlspecialchars($course['course_code']); ?></p>
                            <p><strong>เซคชั่น:</strong> <?php echo htmlspecialchars($course['section']); ?></p>
                            <p><strong>อาจารย์ผู้สอน:</strong> <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                            <p><strong>ห้อง:</strong> <?php echo htmlspecialchars($course['room_name']); ?> (<?php echo htmlspecialchars($course['room_type'] == 'lab' ? 'ห้อง Lab' : 'ห้อง Lecture'); ?>)</p>
                        </div>
                        <?php if ($course['room_type'] == 'lab'): ?>
                            <a href="lab_booking.php?course_id=<?php echo $course['course_id']; ?>" class="btn">
                                <i class="fas fa-chair"></i>จองที่นั่ง (ห้อง Lab)
                            </a>
                        <?php else: ?>
                            <a href="lecture_checkin.php?course_id=<?php echo $course['course_id']; ?>" class="btn">
                                <i class="fas fa-check-circle"></i>ยืนยันการเข้าเรียน (ห้อง Lecture)
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-courses">
                <i class="fas fa-book-open"></i>
                <p>ไม่มีห้องเรียน/วิชาที่เปิดให้ใช้งานในขณะนี้</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // เมื่อ DOM โหลดเสร็จสิ้น, ตรวจสอบ URL เพื่อแสดงข้อความแจ้งเตือน
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const messageParam = urlParams.get('message');
            const detailParam = urlParams.get('detail');

            if (messageParam) {
                let messageText = '';
                let messageType = 'error';

                // --- SUCCESS MESSAGES ---
                if (messageParam === 'success') {
                    messageText = 'การจอง/ยืนยันสำเร็จ!';
                    messageType = 'success';
                }
                // --- ERROR MESSAGES ---
                else if (messageParam === 'invalid_data') {
                    messageText = 'ข้อมูลที่ส่งมาไม่ครบถ้วน';
                } else if (messageParam === 'course_not_found_or_not_available') {
                    messageText = 'ไม่พบวิชาหรือวิชาไม่พร้อมใช้งาน';
                } else if (messageParam === 'student_not_registered') {
                    messageText = 'รหัสนักศึกษาไม่ถูกต้องสำหรับวิชานี้';
                } else if (messageParam === 'duplicate') {
                    messageText = 'คุณได้ยืนยันการเข้าเรียน/จองที่นั่งสำหรับวิชานี้แล้วในคาบนี้!';
                } else if (messageParam === 'no_seat_selected') {
                    messageText = 'กรุณาเลือกที่นั่ง';
                } else if (messageParam === 'seat_already_booked') {
                    messageText = 'ที่นั่งนี้ถูกจองไปแล้ว กรุณาเลือกที่นั่งอื่น';
                } else if (messageParam === 'db_error') {
                    messageText = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' + (detailParam || 'ไม่ทราบสาเหตุ');
                } else if (messageParam === 'invalid_request') {
                    messageText = 'การร้องขอไม่ถูกต้อง';
                }
                
                const messageElement = document.getElementById(messageType + 'Message');
                if (messageElement) {
                    messageElement.innerHTML = `<i class="fas fa-${messageType === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${messageText}`;
                    messageElement.style.display = 'flex';
                    setTimeout(() => {
                        messageElement.style.display = 'none';
                    }, 5000);
                }
            }

            // Add staggered animation to course items
            const courseItems = document.querySelectorAll('.course-item');
            courseItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    item.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>