<?php
// teacher/report_selection.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once('../db_connect.php');

// ดึงข้อมูลวิชาที่อาจารย์สอน
$teacher_id = $_SESSION['user_id'];
$stmt_courses = $pdo->prepare("SELECT course_id, course_name, course_code, section, room_name, room_type FROM courses WHERE created_by_user_id = ? ORDER BY course_name ASC");
$stmt_courses->execute([$teacher_id]);
$courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกรายงาน</title>
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
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: linear-gradient(145deg, #2d2d2d 0%, #404040 100%);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border: 2px solid #555;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ff6b35;
        }

        .page-header h1 {
            font-size: 2.2em;
            color: #ff6b35;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #ccc;
            font-size: 1.1em;
        }

        .form-section {
            margin-bottom: 30px;
            padding: 25px;
            background: linear-gradient(145deg, #404040 0%, #2d2d2d 100%);
            border-radius: 15px;
            border: 1px solid #555;
        }

        .section-title {
            font-size: 1.4em;
            color: #ff8c42;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #f5f5f5;
        }

        select, input[type="date"] {
            width: 100%;
            padding: 12px 15px;
            background: #2d2d2d;
            border: 2px solid #555;
            border-radius: 8px;
            color: #f5f5f5;
            font-family: inherit;
            font-size: 1em;
        }

        select:focus, input[type="date"]:focus {
            border-color: #ff6b35;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.3);
        }

        .date-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .date-range.hidden {
            display: none;
        }

        .option-card {
            background: linear-gradient(145deg, #505050 0%, #3a3a3a 100%);
            border: 2px solid #666;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .option-card:hover {
            border-color: #ff6b35;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 53, 0.2);
        }

        .option-card.selected {
            border-color: #ff6b35;
            background: linear-gradient(145deg, rgba(255, 107, 53, 0.2) 0%, #3a3a3a 100%);
        }

        .option-card input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .option-title {
            font-size: 1.2em;
            font-weight: 600;
            color: #ff8c42;
            margin-bottom: 5px;
        }

        .option-desc {
            color: #ccc;
            font-size: 0.95em;
        }

        .no-courses {
            text-align: center;
            padding: 30px;
            background: linear-gradient(145deg, #2d2d2d 0%, #404040 100%);
            border-radius: 12px;
            border: 2px dashed #666;
            color: #ccc;
        }

        .no-courses i {
            font-size: 3em;
            color: #ff6b35;
            margin-bottom: 15px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 53, 0.4);
        }

        .btn-secondary {
            background: #555;
            color: #f5f5f5;
        }

        .btn-secondary:hover {
            background: #666;
        }

        .preview-section {
            background: linear-gradient(145deg, #2d2d2d 0%, #404040 100%);
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #555;
        }

        .preview-title {
            color: #ff8c42;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .preview-content {
            color: #ccc;
            font-size: 0.95em;
        }

        @media (max-width: 768px) {
            .container {
                padding: 25px;
            }
            
            .date-range {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-chart-bar"></i> เลือกรายงาน</h1>
            <p>กำหนดประเภทและช่วงเวลาของรายงานที่ต้องการ</p>
        </div>

        <form id="reportForm" action="generate_report.php" method="GET">
            
            <!-- เลือกประเภทรายงาน -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-list"></i> ประเภทรายงาน
                </div>
                
                <div class="option-card" onclick="selectReportType('all')">
                    <input type="radio" name="type" value="all" id="type_all">
                    <div class="option-title">รายงานรวมทุกวิชา</div>
                    <div class="option-desc">ข้อมูลการเข้าเรียนของทุกวิชาที่คุณสอน (<?php echo count($courses); ?> วิชา)</div>
                </div>
                
                <div class="option-card" onclick="selectReportType('single')">
                    <input type="radio" name="type" value="single" id="type_single">
                    <div class="option-title">รายงานเฉพาะวิชา</div>
                    <div class="option-desc">ข้อมูลการเข้าเรียนของวิชาที่เลือก</div>
                </div>
                
                <div class="form-group" id="course_selection" style="display: none; margin-top: 20px;">
                    <label for="course_id">เลือกวิชา:</label>
                    <select name="course_id" id="course_id">
                        <option value="">-- เลือกวิชา --</option>
                        <?php if (count($courses) > 0): ?>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['course_id']; ?>">
                                    <?php echo htmlspecialchars($course['course_name']) . ' (' . htmlspecialchars($course['course_code']) . '-' . htmlspecialchars($course['section']) . ')'; ?>
                                    - <?php echo htmlspecialchars($course['room_name']); ?>
                                    (<?php echo htmlspecialchars($course['room_type'] == 'lab' ? 'Lab' : 'Lecture'); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">ไม่มีวิชาที่สอน</option>
                        <?php endif; ?>
                    </select>
                </div>

                <?php if (count($courses) == 0): ?>
                    <div class="no-courses">
                        <i class="fas fa-book-open"></i>
                        <p>คุณยังไม่มีวิชาที่สร้างไว้ในระบบ</p>
                        <p>กรุณาสร้างวิชาก่อนเพื่อสร้างรายงาน</p>
                        <a href="add_course.php" style="color: #ff6b35; text-decoration: none; margin-top: 10px; display: inline-block;">
                            <i class="fas fa-plus"></i> สร้างวิชาใหม่
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- เลือกช่วงเวลา -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-calendar"></i> ช่วงเวลา
                </div>
                
                <div class="option-card" onclick="selectTimeRange('all')">
                    <input type="radio" name="week" value="all" id="week_all" checked>
                    <div class="option-title">ทั้งหมด</div>
                    <div class="option-desc">ข้อมูลทั้งหมดตั้งแต่เริ่มใช้ระบบ</div>
                </div>
                
                <div class="option-card" onclick="selectTimeRange('current')">
                    <input type="radio" name="week" value="current" id="week_current">
                    <div class="option-title">สัปดาห์นี้</div>
                    <div class="option-desc">
                        <?php 
                        $start_of_week = date('d/m/Y', strtotime('monday this week'));
                        $end_of_week = date('d/m/Y', strtotime('sunday this week'));
                        echo "วันที่ {$start_of_week} - {$end_of_week}";
                        ?>
                    </div>
                </div>
                
                <div class="option-card" onclick="selectTimeRange('last')">
                    <input type="radio" name="week" value="last" id="week_last">
                    <div class="option-title">สัปดาห์ที่แล้ว</div>
                    <div class="option-desc">
                        <?php 
                        $start_of_last_week = date('d/m/Y', strtotime('monday last week'));
                        $end_of_last_week = date('d/m/Y', strtotime('sunday last week'));
                        echo "วันที่ {$start_of_last_week} - {$end_of_last_week}";
                        ?>
                    </div>
                </div>
                
                <div class="option-card" onclick="selectTimeRange('last_4_weeks')">
                    <input type="radio" name="week" value="last_4_weeks" id="week_last_4">
                    <div class="option-title">4 สัปดาห์ที่ผ่านมา</div>
                    <div class="option-desc">
                        <?php 
                        $start_of_4_weeks = date('d/m/Y', strtotime('-4 weeks monday'));
                        $end_of_this_week = date('d/m/Y', strtotime('sunday this week'));
                        echo "วันที่ {$start_of_4_weeks} - {$end_of_this_week}";
                        ?>
                    </div>
                </div>
                
                <div class="option-card" onclick="selectTimeRange('specific')">
                    <input type="radio" name="week" value="specific" id="week_specific">
                    <div class="option-title">กำหนดเอง</div>
                    <div class="option-desc">เลือกช่วงวันที่เริ่มต้นและสิ้นสุด</div>
                </div>
                
                <div class="date-range hidden" id="date_range">
                    <div class="form-group">
                        <label for="start_date">วันที่เริ่มต้น:</label>
                        <input type="date" name="start_date" id="start_date">
                    </div>
                    <div class="form-group">
                        <label for="end_date">วันที่สิ้นสุด:</label>
                        <input type="date" name="end_date" id="end_date">
                    </div>
                </div>
            </div>

            <!-- ตัวอย่างข้อมูลที่จะได้ -->
            <div class="preview-section">
                <div class="preview-title">
                    <i class="fas fa-eye"></i> รายงานจะประกอบด้วย:
                </div>
                <div class="preview-content">
                    ✅ สรุปสถิติตามสัปดาห์ (จำนวนนักศึกษา, จำนวนวิชา)<br>
                    ✅ สรุปสถิติตามวิชา (จำนวนนักศึกษารวม, จำนวนสัปดาห์)<br>
                    ✅ รายละเอียดการเข้าเรียนทั้งหมด จัดกลุ่มตามสัปดาห์<br>
                    ✅ ข้อมูลที่นั่ง, เวลาเข้าเรียน, สถานะ<br>
                    ✅ รูปแบบไฟล์ CSV เปิดได้ด้วย Excel
                </div>
            </div>

            <div class="action-buttons">
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> ยกเลิก
                </a>
                <?php if (count($courses) > 0): ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download"></i> ดาวน์โหลดรายงาน
                    </button>
                <?php else: ?>
                    <a href="add_course.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> สร้างวิชาก่อน
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script>
        function selectReportType(type) {
            // เคลียร์การเลือกก่อนหน้า
            document.querySelectorAll('input[name="type"]').forEach(radio => {
                radio.checked = false;
                radio.closest('.option-card').classList.remove('selected');
            });
            
            // เลือกตัวเลือกใหม่
            document.getElementById('type_' + type).checked = true;
            document.getElementById('type_' + type).closest('.option-card').classList.add('selected');
            
            // แสดง/ซ่อนการเลือกวิชา
            const courseSelection = document.getElementById('course_selection');
            if (type === 'single') {
                courseSelection.style.display = 'block';
                document.getElementById('course_id').required = true;
            } else {
                courseSelection.style.display = 'none';
                document.getElementById('course_id').required = false;
            }
        }

        function selectTimeRange(range) {
            // เคลียร์การเลือกก่อนหน้า
            document.querySelectorAll('input[name="week"]').forEach(radio => {
                radio.checked = false;
                radio.closest('.option-card').classList.remove('selected');
            });
            
            // เลือกตัวเลือกใหม่
            document.getElementById('week_' + range).checked = true;
            document.getElementById('week_' + range).closest('.option-card').classList.add('selected');
            
            // แสดง/ซ่อนช่วงวันที่
            const dateRange = document.getElementById('date_range');
            if (range === 'specific') {
                dateRange.classList.remove('hidden');
                document.getElementById('start_date').required = true;
                document.getElementById('end_date').required = true;
            } else {
                dateRange.classList.add('hidden');
                document.getElementById('start_date').required = false;
                document.getElementById('end_date').required = false;
            }
        }

        // ตั้งค่าเริ่มต้น
        document.addEventListener('DOMContentLoaded', function() {
            selectReportType('all');
            selectTimeRange('all');
            
            // ตั้งค่าวันที่เริ่มต้น
            const today = new Date();
            const oneWeekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            
            document.getElementById('end_date').value = today.toISOString().split('T')[0];
            document.getElementById('start_date').value = oneWeekAgo.toISOString().split('T')[0];
        });

        // Validation ก่อนส่งฟอร์ม
        document.getElementById('reportForm').addEventListener('submit', function(e) {
            const reportType = document.querySelector('input[name="type"]:checked').value;
            const timeRange = document.querySelector('input[name="week"]:checked').value;
            
            // ตรวจสอบว่ามีวิชาหรือไม่
            const totalCourses = <?php echo count($courses); ?>;
            if (totalCourses === 0) {
                alert('กรุณาสร้างวิชาก่อนเพื่อสร้างรายงาน');
                e.preventDefault();
                window.location.href = 'add_course.php';
                return;
            }
            
            // ตรวจสอบการเลือกวิชา
            if (reportType === 'single') {
                const courseId = document.getElementById('course_id').value;
                if (!courseId) {
                    alert('กรุณาเลือกวิชาที่ต้องการสร้างรายงาน');
                    e.preventDefault();
                    return;
                }
            }
            
            // ตรวจสอบช่วงวันที่
            if (timeRange === 'specific') {
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                
                if (!startDate || !endDate) {
                    alert('กรุณาเลือกวันที่เริ่มต้นและสิ้นสุด');
                    e.preventDefault();
                    return;
                }
                
                if (new Date(startDate) > new Date(endDate)) {
                    alert('วันที่เริ่มต้นต้องไม่เกินวันที่สิ้นสุด');
                    e.preventDefault();
                    return;
                }
            }
            
            // แสดง loading
            const submitBtn = document.querySelector('.btn-primary');
            if (submitBtn && submitBtn.type === 'submit') {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังสร้างรายงาน...';
                submitBtn.disabled = true;
            }
        });
    </script>
</body>
</html>