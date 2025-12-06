<?php
// teacher/add_course.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มวิชาใหม่</title>
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

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px 25px;
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Main Form Card */
        .form-card {
            background: linear-gradient(145deg, #2d2d2d 0%, #404040 100%);
            border-radius: 25px;
            padding: 40px;
            width: 100%;
            max-width: 700px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border: 2px solid #555;
            position: relative;
            overflow: hidden;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 107, 53, 0.1), transparent);
            transition: left 0.8s ease;
        }

        .form-card:hover::before {
            left: 100%;
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
                0 0 20px rgba(255, 107, 53, 0.2),
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

        .page-header p {
            font-size: 1.1em;
            opacity: 0.9;
            margin: 0;
        }

        .page-header i {
            margin-right: 15px;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.3));
        }

        /* Form Sections */
        .form-section {
            background: linear-gradient(145deg, #383838 0%, #2a2a2a 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #555;
            position: relative;
        }

        .form-section h3 {
            color: #ff6b35;
            font-size: 1.4em;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .form-section h3 i {
            margin-right: 12px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .form-section h3::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, #ff6b35, transparent);
            margin-left: 15px;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #ff8c42;
            font-size: 1em;
            position: relative;
        }

        label::before {
            content: '•';
            color: #ff6b35;
            margin-right: 8px;
            font-size: 1.2em;
        }

        label.required::after {
            content: ' *';
            color: #f44336;
            font-weight: 700;
        }

        /* Enhanced Input Styling */
        input[type="text"],
        select {
            width: 100%;
            padding: 15px 20px;
            background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 100%);
            border: 2px solid #555;
            border-radius: 12px;
            color: #ffffff;
            font-family: inherit;
            font-size: 1em;
            transition: all 0.3s ease;
            position: relative;
        }

        input[type="text"]:focus,
        select:focus {
            border-color: #ff6b35;
            outline: none;
            box-shadow: 
                0 0 0 3px rgba(255, 107, 53, 0.3),
                0 0 15px rgba(255, 107, 53, 0.1);
            background: linear-gradient(145deg, #000000 0%, #1a1a1a 100%);
        }

        /* Select styling */
        select option {
            background: #1a1a1a;
            color: #ffffff;
            padding: 10px;
        }

        select option:checked {
            background: #ff6b35;
            color: #ffffff;
        }

        /* Room Type Icons */
        .room-type-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
        }

        .room-type-option {
            display: none;
        }

        .room-type-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(145deg, #404040 0%, #2d2d2d 100%);
            border: 2px solid #555;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .room-type-label::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 107, 53, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .room-type-label:hover::before {
            left: 100%;
        }

        .room-type-label:hover {
            border-color: #ff6b35;
            background: linear-gradient(145deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.3);
        }

        .room-type-option:checked + .room-type-label {
            border-color: #ff6b35;
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
            box-shadow: 0 0 20px rgba(255, 107, 53, 0.4);
        }

        .room-type-icon {
            font-size: 2em;
            margin-bottom: 10px;
            display: block;
        }

        .room-type-text {
            font-weight: 600;
            font-size: 1.1em;
        }

        .room-type-desc {
            font-size: 0.9em;
            opacity: 0.8;
            margin-top: 5px;
        }

        /* Enhanced Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1em;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-family: inherit;
            position: relative;
            overflow: hidden;
            min-width: 180px;
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
            margin-right: 10px;
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

        .btn-secondary {
            background: linear-gradient(135deg, #404040 0%, #555 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #555 0%, #666 100%);
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.4);
        }

        /* Button Group */
        .button-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        /* Success Indicator */
        .success-indicator {
            display: none;
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .success-indicator.show {
            display: block;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Info Box */
        .info-box {
            background: linear-gradient(135deg, #2196f3 0%, #42a5f5 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-top: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
        }

        .info-box i {
            font-size: 1.5em;
            margin-bottom: 10px;
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            
            .form-card {
                padding: 25px;
            }
            
            .page-header {
                padding: 25px 20px;
            }
            
            .page-header h2 {
                font-size: 1.8em;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .room-type-selector {
                grid-template-columns: 1fr;
            }
            
            .button-group {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 300px;
            }
            
            .back-button {
                top: 15px;
                left: 15px;
                padding: 10px 15px;
                font-size: 0.9em;
            }
        }

        @media (max-width: 480px) {
            .page-header h2 {
                font-size: 1.5em;
            }
            
            .form-section {
                padding: 20px;
            }
            
            .form-section h3 {
                font-size: 1.2em;
            }
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i>กลับ Dashboard
    </a>

    <div class="container">
        <div class="form-card">
            <div class="page-header">
                <div class="page-header-content">
                    <h2><i class="fas fa-plus-circle"></i>เพิ่มวิชาใหม่</h2>
                    <p>สร้างห้องเรียนและจัดการนักศึกษาได้อย่างง่ายดาย</p>
                </div>
            </div>

            <div class="success-indicator" id="successMessage">
                <i class="fas fa-check-circle"></i>
                <span>บันทึกข้อมูลเรียบร้อยแล้ว!</span>
            </div>

            <form action="process_add_course.php" method="POST" id="courseForm">
                <div class="form-section">
                    <h3><i class="fas fa-book"></i>ข้อมูลพื้นฐานของวิชา</h3>
                    
                    <div class="form-group">
                        <label for="course_name" class="required">ชื่อวิชา</label>
                        <input type="text" id="course_name" name="course_name" required 
                               placeholder="เช่น การเขียนโปรแกรมคอมพิวเตอร์">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="course_code">รหัสวิชา</label>
                            <input type="text" id="course_code" name="course_code" 
                                   placeholder="เช่น CPE101">
                        </div>
                        <div class="form-group">
                            <label for="section">เซคชั่น</label>
                            <input type="text" id="section" name="section" 
                                   placeholder="เช่น 01">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="instructor_name" class="required">อาจารย์ผู้สอน</label>
                        <input type="text" id="instructor_name" name="instructor_name" 
                               value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-door-open"></i>ประเภทห้องเรียน</h3>
                    
                    <div class="form-group">
                        <label>เลือกประเภทห้องที่ต้องการ</label>
                        <div class="room-type-selector">
                            <div>
                                <input type="radio" id="room_lab" name="room_type" value="lab" 
                                       class="room-type-option" required>
                                <label for="room_lab" class="room-type-label">
                                    <div>
                                        <i class="fas fa-desktop room-type-icon"></i>
                                        <div class="room-type-text">ห้อง Lab</div>
                                        <div class="room-type-desc">ต้องเลือกที่นั่ง</div>
                                    </div>
                                </label>
                            </div>
                            <div>
                                <input type="radio" id="room_lecture" name="room_type" value="lecture" 
                                       class="room-type-option" required>
                                <label for="room_lecture" class="room-type-label">
                                    <div>
                                        <i class="fas fa-chalkboard-teacher room-type-icon"></i>
                                        <div class="room-type-text">ห้อง Lecture</div>
                                        <div class="room-type-desc">ไม่ต้องเลือกที่นั่ง</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="room_name" class="required">ชื่อห้องเรียน</label>
                        <input type="text" id="room_name" name="room_name" required 
                               placeholder="เช่น Lab Computer 1, Lecture Hall A">
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>บันทึกข้อมูลวิชา
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>ยกเลิก
                    </a>
                </div>
            </form>

            <div class="info-box">
                <i class="fas fa-lightbulb"></i>
                <strong>เคล็ดลับ:</strong> หลังจากสร้างวิชาแล้ว คุณสามารถกลับไปที่ Dashboard 
                เพื่อ "ดูตาราง/เพิ่มนักศึกษา" ในวิชานั้นได้ทันที
            </div>
        </div>
    </div>

    <script>
        // Form enhancement
        document.getElementById('courseForm').addEventListener('submit', function(e) {
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>กำลังบันทึก...';
            submitBtn.disabled = true;
            
            // Note: Remove this timeout in production, it's just for demo
            // setTimeout(() => {
            //     submitBtn.innerHTML = originalText;
            //     submitBtn.disabled = false;
            // }, 2000);
        });

        // Add some interactivity
        document.querySelectorAll('input[type="text"]').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Room type selection enhancement
        document.querySelectorAll('input[name="room_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'lab') {
                    document.getElementById('room_name').placeholder = 'เช่น Lab Computer 1, Lab Programming';
                } else {
                    document.getElementById('room_name').placeholder = 'เช่น Lecture Hall A, Room 301';
                }
            });
        });
    </script>
</body>
</html>