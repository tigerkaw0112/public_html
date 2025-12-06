<?php
// teacher/view_bookings.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once('../db_connect.php');

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
if ($course_id == 0) {
    header('Location: dashboard.php');
    exit();
}

// ดึงข้อมูลวิชา
$stmt_course = $pdo->prepare("SELECT * FROM courses WHERE course_id = ? AND created_by_user_id = ?");
$stmt_course->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt_course->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo "ไม่พบวิชาหรือคุณไม่มีสิทธิ์เข้าถึงวิชานี้";
    exit();
}

// ดึงรายชื่อนักศึกษาที่ลงทะเบียนในวิชานี้
$stmt_registered_students = $pdo->prepare("
    SELECT s.student_id, s.full_name
    FROM course_students cs
    JOIN students s ON cs.student_id = s.student_id
    WHERE cs.course_id = ?
    ORDER BY s.student_id
");
$stmt_registered_students->execute([$course_id]);
$registered_students = $stmt_registered_students->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ดูตารางนักศึกษา: <?php echo htmlspecialchars($course['course_name']); ?></title>
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
            text-align: center;
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
            margin-bottom: 15px;
        }

        .course-details {
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
            margin-top: 25px;
        }

        .course-detail-item {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
            padding: 15px 25px;
            border-radius: 50px;
            backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 8px 20px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 1em;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .course-detail-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .course-detail-item:hover::before {
            left: 100%;
        }

        .course-detail-item:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 
                0 15px 30px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .course-detail-item i {
            font-size: 1.2em;
            filter: drop-shadow(1px 1px 2px rgba(0, 0, 0, 0.3));
        }

        .page-header i {
            margin-right: 12px;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.3));
        }

        /* Messages */
        .message {
            padding: 18px 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: none;
            border-left: 5px solid;
            align-items: center;
            gap: 15px;
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
            border-color: #28a745;
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
        }

        .error-message {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.9) 0%, rgba(220, 53, 69, 0.7) 100%);
            color: white;
            border-color: #dc3545;
            box-shadow: 0 10px 25px rgba(220, 53, 69, 0.3);
        }

        /* Action Buttons Top */
        .action-buttons-top {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px 25px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1em;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            transform: translateY(-3px);
        }

        .btn i {
            margin-right: 10px;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #404040 0%, #555 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(64, 64, 64, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
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

        textarea {
            width: 100%;
            padding: 15px 20px;
            background: linear-gradient(145deg, #404040 0%, #2d2d2d 100%);
            border: 2px solid #555;
            border-radius: 12px;
            color: #f5f5f5;
            font-family: inherit;
            font-size: 1em;
            min-height: 150px;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        textarea:focus {
            border-color: #ff6b35;
            outline: none;
            box-shadow: 
                0 0 0 3px rgba(255, 107, 53, 0.3),
                inset 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        textarea::placeholder {
            color: #999;
            font-style: italic;
        }

        /* Table Styling */
        .table-container {
            overflow-x: auto;
            border-radius: 15px;
            border: 2px solid #555;
            background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 100%);
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: linear-gradient(135deg, #404040 0%, #2d2d2d 100%);
            color: #ff8c42;
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 1em;
            border-bottom: 2px solid #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        th:first-child {
            border-radius: 13px 0 0 0;
        }

        th:last-child {
            border-radius: 0 13px 0 0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #444;
            vertical-align: middle;
            color: #f5f5f5;
        }

        tr:hover {
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.1) 0%, rgba(255, 140, 66, 0.05) 100%);
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Real-time Section */
        .realtime-section {
            position: relative;
        }

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

        /* No Data Message */
        .no-data {
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(145deg, #404040 0%, #2d2d2d 100%);
            border-radius: 15px;
            border: 2px dashed #666;
            color: #ccc;
        }

        .no-data i {
            font-size: 3em;
            color: #ff6b35;
            margin-bottom: 15px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-8px); }
            60% { transform: translateY(-4px); }
        }

        .no-data p {
            font-size: 1.1em;
        }

        /* Student Count Badge */
        .student-count {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            margin-left: 10px;
        }

        /* Loading Animation */
        .loading {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #ff8c42;
            font-style: italic;
        }

        .loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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
            
            .page-header h3 {
                font-size: 1.1em;
            }
            
            .course-details {
                gap: 15px;
            }
            
            .course-detail-item {
                padding: 8px 15px;
                font-size: 0.9em;
            }
            
            .action-buttons-top {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .section-box {
                padding: 20px;
            }
            
            .section-box h3 {
                font-size: 1.5em;
                flex-direction: column;
                text-align: center;
            }
            
            .section-box h3::after {
                display: none;
            }
            
            textarea {
                min-height: 120px;
            }
            
            .table-container {
                font-size: 0.9em;
            }
            
            th, td {
                padding: 12px 8px;
            }
        }

        @media (max-width: 480px) {
            .page-header h2 {
                font-size: 1.5em;
            }
            
            .section-box h3 {
                font-size: 1.3em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div class="page-header-content">
                <h2><i class="fas fa-chalkboard"></i><?php echo htmlspecialchars($course['course_name']); ?></h2>
                <h3><?php echo htmlspecialchars($course['course_code']); ?> - เซคชั่น <?php echo htmlspecialchars($course['section']); ?></h3>
                
                <div class="course-details">
                    <div class="course-detail-item">
                        <i class="fas fa-door-open"></i>
                        ห้อง: <?php echo htmlspecialchars($course['room_name']); ?>
                    </div>
                    <div class="course-detail-item">
                        <i class="fas fa-<?php echo $course['room_type'] == 'lab' ? 'desktop' : 'chalkboard'; ?>"></i>
                        <?php echo htmlspecialchars($course['room_type'] == 'lab' ? 'ห้อง Lab' : 'ห้อง Lecture'); ?>
                    </div>
                    <div class="course-detail-item">
                        <i class="fas fa-users"></i>
                        นักศึกษา: <?php echo count($registered_students); ?> คน
                    </div>
                </div>
            </div>
        </div>

        <div class="message success-message" id="successMessage">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="message error-message" id="errorMessage">
            <i class="fas fa-exclamation-circle"></i>
        </div>

        <div class="action-buttons-top">
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>กลับ Dashboard
            </a>
            <button class="btn btn-primary" onclick="confirmClearRoom(<?php echo $course_id; ?>, this)">
                <i class="fas fa-redo"></i>เคลียร์ห้องเรียนสำหรับคาบถัดไป
            </button>
            <a href="generate_report.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">
                <i class="fas fa-file-alt"></i>ดาวน์โหลดรายงาน
            </a>
        </div>

        <div class="section-box">
            <h3><i class="fas fa-user-plus"></i>เพิ่มนักศึกษาในวิชานี้</h3>
            <form id="addStudentForm" action="process_add_students.php" method="POST">
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                <div class="form-group">
                    <label for="student_data">
                        <i class="fas fa-file-excel"></i> วางข้อมูลนักศึกษาจาก Excel (รหัส &emsp; ชื่อ-สกุล):
                    </label>
                    <textarea id="student_data" name="student_data" placeholder="ตัวอย่าง:
6512345    นายสมชาย ใจดี
6512346    นางสาวสุดา สวยงาม
6512347    นายวิชัย เก่งกาจ

(แต่ละบรรทัดคือ 1 คน, คั่นด้วยช่องว่างหรือแท็บ)"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-check"></i>เพิ่มนักศึกษา
                </button>
            </form>
        </div>

        <div class="section-box realtime-section">
            <h3>
                <i class="fas fa-user-friends"></i>นักศึกษาที่เข้าเรียน/จองที่นั่ง
                <span class="realtime-indicator">
                    <span class="realtime-dot"></span>
                    Real-time
                </span>
            </h3>
            <div id="activeBookingsList" class="table-container">
                <div class="no-data">
                    <div class="loading">
                        <i class="fas fa-spinner"></i>
                        กำลังโหลดข้อมูล...
                    </div>
                </div>
            </div>
        </div>

        <div class="section-box">
            <h3>
                <i class="fas fa-list-alt"></i>นักศึกษาที่ลงทะเบียนในวิชานี้
                <span class="student-count"><?php echo count($registered_students); ?> คน</span>
            </h3>
            <?php if (count($registered_students) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-id-card"></i> รหัสนักศึกษา</th>
                                <th><i class="fas fa-user"></i> ชื่อ-สกุล</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registered_students as $s): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($s['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-users-slash"></i>
                    <p>ยังไม่มีนักศึกษาลงทะเบียนในวิชานี้</p>
                    <p>กรุณาเพิ่มนักศึกษาในส่วนด้านบน</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmClearRoom(courseId, btn) {
            if (confirm('คุณแน่ใจหรือไม่ที่จะเคลียร์ห้องเรียน?\n\nการกระทำนี้จะบันทึกข้อมูลปัจจุบันและล้างข้อมูลการจองสำหรับคาบถัดไป')) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>กำลังเคลียร์...';
                btn.disabled = true;
                
                setTimeout(() => {
                    window.location.href = 'clear_room.php?course_id=' + courseId;
                }, 500);
            }
        }

        function loadActiveBookings() {
            fetch('get_active_bookings.php?course_id=<?php echo $course_id; ?>')
                .then(response => response.json())
                .then(data => {
                    const listDiv = document.getElementById('activeBookingsList');
                    if (data.length > 0) {
                        let html = '<table><thead><tr><th><i class="fas fa-id-card"></i> รหัสนักศึกษา</th><th><i class="fas fa-user"></i> ชื่อ-สกุล</th><th><i class="fas fa-clock"></i> เวลาเข้าเรียน</th>';
                        if ('<?php echo $course['room_type']; ?>' === 'lab') {
                            html += '<th><i class="fas fa-chair"></i> ที่นั่ง</th>';
                        }
                        html += '</tr></thead><tbody>';
                        data.forEach(booking => {
                            html += `<tr>
                                <td>${booking.student_id}</td>
                                <td>${booking.full_name}</td>
                                <td>${booking.check_in_time}</td>`;
                            if ('<?php echo $course['room_type']; ?>' === 'lab') {
                                html += `<td>${booking.seat_number || '-'}</td>`;
                            }
                            html += `</tr>`;
                        });
                        html += '</tbody></table>';
                        listDiv.innerHTML = html;
                    } else {
                        listDiv.innerHTML = `
                            <div class="no-data">
                                <i class="fas fa-user-slash"></i>
                                <p>ยังไม่มีนักศึกษาเข้าเรียน/จองที่นั่งในขณะนี้</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    const listDiv = document.getElementById('activeBookingsList');
                    listDiv.innerHTML = `
                        <div class="no-data">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
                        </div>
                    `;
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadActiveBookings();
            setInterval(loadActiveBookings, 5000);

            const urlParams = new URLSearchParams(window.location.search);
            const messageParam = urlParams.get('message');
            const detailParam = urlParams.get('detail');

            if (messageParam) {
                let messageText = '';
                let messageType = 'error';

                if (messageParam === 'cleared_success') {
                    messageText = 'เคลียร์ห้องเรียนสำเร็จแล้ว ข้อมูลถูกบันทึกในรายงาน';
                    messageType = 'success';
                } else if (messageParam === 'add_students_success') {
                    const count = urlParams.get('count') || 0;
                    messageText = `เพิ่มนักศึกษาสำเร็จ ${count} คน`;
                    messageType = 'success';
                } else if (messageParam === 'clear_error') {
                    messageText = 'เกิดข้อผิดพลาดในการเคลียร์ห้องเรียน';
                } else if (messageParam === 'add_students_error') {
                    messageText = 'เกิดข้อผิดพลาดในการเพิ่มนักศึกษา';
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
        });
    </script>
</body>
</html>