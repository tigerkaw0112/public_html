<?php
// teacher/dashboard.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once('../db_connect.php');

$teacher_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM courses WHERE created_by_user_id = ? ORDER BY created_at DESC");
$stmt->execute([$teacher_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// สถิติเพิ่มเติม
$stmt_stats = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT c.course_id) as total_courses,
        COUNT(DISTINCT cs.student_id) as total_students,
        COUNT(b.booking_id) as total_bookings,
        COUNT(CASE WHEN b.status = 'active' THEN 1 END) as active_bookings
    FROM courses c
    LEFT JOIN course_students cs ON c.course_id = cs.course_id
    LEFT JOIN bookings b ON c.course_id = b.course_id
    WHERE c.created_by_user_id = ?
");
$stmt_stats->execute([$teacher_id]);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard อาจารย์</title>
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
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 25px;
        }

        /* Enhanced Header */
        .header {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 50%, #ff4500 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .header::before {
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

        .header-content {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-left h2 {
            font-size: 2.2em;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header-left i {
            margin-right: 15px;
        }

        .header-subtitle {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .header-stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            min-width: 100px;
        }

        .stat-number {
            font-size: 1.8em;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
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
        }

        @keyframes slideInDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .success-message {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.9) 0%, rgba(40, 167, 69, 0.7) 100%);
            color: white;
            border-color: #28a745;
        }

        .error-message {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.9) 0%, rgba(220, 53, 69, 0.7) 100%);
            color: white;
            border-color: #dc3545;
        }

        /* Action Buttons */
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 18px 25px;
            text-decoration: none;
            border-radius: 15px;
            font-weight: 600;
            font-size: 1em;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            transform: translateY(-3px) scale(1.02);
        }

        .btn i {
            margin-right: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #404040 0%, #555 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(64, 64, 64, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
        }

        /* Courses Section */
        .courses-section {
            background: linear-gradient(145deg, #2d2d2d 0%, #404040 100%);
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border: 2px solid #555;
        }

        .courses-section h3 {
            color: #ff6b35;
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }

        .courses-section h3 i {
            margin-right: 15px;
        }

        .courses-section h3::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, #ff6b35, transparent);
            margin-left: 20px;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
            border-radius: 15px;
            border: 2px solid #555;
            background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 100%);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: linear-gradient(135deg, #404040 0%, #2d2d2d 100%);
            color: #ff8c42;
            padding: 20px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 1em;
            border-bottom: 2px solid #555;
        }

        th:first-child {
            border-radius: 13px 0 0 0;
        }

        th:last-child {
            border-radius: 0 13px 0 0;
        }

        td {
            padding: 20px 15px;
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

        /* Status Badge */
        .status-badge {
            padding: 6px 15px;
            border-radius: 25px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-visible {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .status-hidden {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        /* Table Actions */
        .table-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 8px 15px;
            font-size: 0.85em;
            border-radius: 8px;
        }

        /* Course Info */
        .course-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .course-name {
            font-weight: 600;
            color: #ff8c42;
            font-size: 1.1em;
        }

        .course-details {
            font-size: 0.9em;
            color: #ccc;
        }

        .room-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85em;
            color: #ff6b35;
            font-weight: 500;
        }

        /* No Courses */
        .no-courses {
            text-align: center;
            padding: 60px 30px;
            background: linear-gradient(145deg, #404040 0%, #2d2d2d 100%);
            border-radius: 20px;
            border: 2px dashed #666;
            color: #ccc;
        }

        .no-courses i {
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

        .no-courses p {
            font-size: 1.3em;
            margin-bottom: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .header-left h2 {
                font-size: 1.8em;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .courses-section {
                padding: 25px 20px;
            }
            
            .courses-section h3 {
                font-size: 1.6em;
                flex-direction: column;
                text-align: center;
            }
            
            .courses-section h3::after {
                display: none;
            }
            
            .table-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <h2><i class="fas fa-chalkboard-teacher"></i>Dashboard อาจารย์</h2>
                    <div class="header-subtitle">ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION['username']); ?></div>
                </div>
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['total_courses'] ?: '0'; ?></div>
                        <div class="stat-label">วิชาทั้งหมด</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['total_students'] ?: '0'; ?></div>
                        <div class="stat-label">นักศึกษา</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['active_bookings'] ?: '0'; ?></div>
                        <div class="stat-label">กำลังเรียน</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['total_bookings'] ?: '0'; ?></div>
                        <div class="stat-label">ทั้งหมด</div>
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

        <div class="action-buttons">
            <a href="add_course.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>เพิ่มวิชาใหม่
            </a>
            <a href="report_selection.php" class="btn btn-secondary">
                <i class="fas fa-chart-line"></i>สร้างรายงาน
            </a>
            <a href="generate_report.php?type=all&week=all" class="btn btn-secondary">
                <i class="fas fa-download"></i>ดาวน์โหลดรายงานด่วน
            </a>
            <a href="lab_reservations_overview.php" class="btn btn-success">
                <i class="fas fa-desktop"></i>ภาพรวม Lab
            </a>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i>ออกจากระบบ
            </a>
        </div>

        <div class="courses-section">
            <h3><i class="fas fa-graduation-cap"></i>วิชาที่คุณสอน</h3>
            
            <?php if (count($courses) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-book"></i> วิชา</th>
                                <th><i class="fas fa-door-open"></i> ห้องเรียน</th>
                                <th><i class="fas fa-eye"></i> สถานะ</th>
                                <th><i class="fas fa-cogs"></i> จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td>
                                        <div class="course-info">
                                            <div class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                            <div class="course-details"><?php echo htmlspecialchars($course['course_code'] . ' / เซคชั่น ' . $course['section']); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($course['room_name']); ?></div>
                                        <div class="room-type">
                                            <i class="fas fa-<?php echo $course['room_type'] == 'lab' ? 'desktop' : 'chalkboard'; ?>"></i>
                                            <?php echo htmlspecialchars($course['room_type'] == 'lab' ? 'ห้อง Lab' : 'ห้อง Lecture'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $course['is_hidden'] ? 'status-hidden' : 'status-visible'; ?>">
                                            <i class="fas fa-<?php echo $course['is_hidden'] ? 'eye-slash' : 'eye'; ?>"></i>
                                            <?php echo $course['is_hidden'] ? 'ซ่อนอยู่' : 'แสดงอยู่'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="view_bookings.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-users"></i>จัดการ
                                            </a>
                                            <a href="process_hide_show.php?course_id=<?php echo $course['course_id']; ?>&action=<?php echo $course['is_hidden'] ? 'show' : 'hide'; ?>" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-<?php echo $course['is_hidden'] ? 'eye' : 'eye-slash'; ?>"></i><?php echo $course['is_hidden'] ? 'แสดง' : 'ซ่อน'; ?>
                                            </a>
                                            <a href="generate_report.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-file-alt"></i>รายงาน
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $course['course_id']; ?>, '<?php echo htmlspecialchars($course['course_name']); ?>', this)">
                                                <i class="fas fa-trash"></i>ลบ
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-courses">
                    <i class="fas fa-book-open"></i>
                    <p>ยังไม่มีวิชาที่คุณสร้าง</p>
                    <a href="add_course.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>สร้างวิชาแรก
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmDelete(courseId, courseName, btn) {
            if (confirm(`คุณแน่ใจหรือไม่ที่จะลบวิชา "${courseName}"?\n\nการกระทำนี้ไม่สามารถย้อนกลับได้`)) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>ลบ...';
                btn.disabled = true;
                setTimeout(() => {
                    window.location.href = 'process_delete_course.php?course_id=' + courseId;
                }, 800);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const messageParam = urlParams.get('message');
            const detailParam = urlParams.get('detail');

            if (messageParam) {
                let messageText = '';
                let messageType = 'error';

                if (messageParam === 'add_course_success') {
                    messageText = 'เพิ่มวิชาสำเร็จแล้ว';
                    messageType = 'success';
                } else if (messageParam === 'delete_success') {
                    messageText = 'ลบวิชาสำเร็จแล้ว';
                    messageType = 'success';
                } else if (messageParam === 'status_updated_success') {
                    messageText = 'สถานะวิชาถูกอัปเดตสำเร็จแล้ว';
                    messageType = 'success';
                } else if (messageParam === 'add_students_success') {
                    const count = urlParams.get('count') || 0;
                    messageText = `เพิ่มนักศึกษาสำเร็จ ${count} คน`;
                    messageType = 'success';
                } else if (messageParam === 'add_course_error') {
                    messageText = 'เกิดข้อผิดพลาดในการเพิ่มวิชา';
                } else if (messageParam === 'delete_error') {
                    messageText = 'เกิดข้อผิดพลาดในการลบวิชา';
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