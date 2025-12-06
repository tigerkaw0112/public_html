<?php
// student/lab_booking.php
require_once('../db_connect.php');

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
if ($course_id == 0) {
    header('Location: select_course.php');
    exit();
}

$stmt_course = $pdo->prepare("SELECT * FROM courses WHERE course_id = ? AND room_type = 'lab' AND is_hidden = 0");
$stmt_course->execute([$course_id]);
$course = $stmt_course->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo "ไม่พบห้อง Lab นี้หรือห้องนี้ไม่พร้อมใช้งาน";
    exit();
}

// ดึงที่นั่งที่ถูกจองแล้วสำหรับวิชานี้ (สถานะ active)
$stmt_booked_seats = $pdo->prepare("SELECT seat_number FROM bookings WHERE course_id = ? AND status = 'active'");
$stmt_booked_seats->execute([$course_id]);
$booked_seats = $stmt_booked_seats->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จองที่นั่ง: <?php echo htmlspecialchars($course['course_name']); ?></title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .page-header {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .page-header h2 {
            font-size: 2em;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .page-header h3 {
            font-size: 1.2em;
            opacity: 0.9;
        }

        /* Messages */
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            gap: 10px;
        }

        .success-message {
            background: rgba(40, 167, 69, 0.9);
            color: white;
            border-left: 4px solid #28a745;
        }

        .error-message {
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border-left: 4px solid #dc3545;
        }

        /* Form Section */
        .form-section {
            background: #2d2d2d;
            border: 2px solid #555;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #ff8c42;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            background: #404040;
            border: 2px solid #555;
            border-radius: 8px;
            color: #f5f5f5;
            font-size: 1em;
            font-family: inherit;
        }

        input[type="text"]:focus {
            border-color: #ff6b35;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.3);
        }

        /* Seat Map */
        .seat-map-section {
            background: #2d2d2d;
            border: 2px solid #555;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .seat-map-title {
            color: #ff6b35;
            font-size: 1.5em;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Lab Map Wrapper */
        .lab-map-wrapper {
            background: #1a1a1a;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        .map-boundary {
            text-align: center;
            padding: 10px;
            background: #ff6b35;
            color: white;
            font-weight: 500;
            border-radius: 8px;
            margin: 0 auto 20px auto;
            max-width: 250px;
        }

        .bottom-boundary {
            margin: 20px auto 0 auto;
        }

        /* Seat Map */
        .seat-map {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 900px;
            margin: 0 auto;
        }

        .seat-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .row-label {
            background: #ff8c42;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
            min-width: 70px;
            text-align: center;
            font-size: 0.9em;
        }

        .seat-block-left,
        .seat-block-right {
            display: flex;
            gap: 6px;
        }

        .seat-gap {
            width: 25px;
        }

        /* Seats */
        .seat {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8em;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .seat.available {
            background: #4caf50;
            color: white;
        }

        .seat.available:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
        }

        .seat.booked {
            background: #f44336;
            color: white;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .seat.selected {
            background: #ffd700;
            color: #1a1a1a;
            transform: translateY(-2px);
            border-color: #ffeb3b;
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.5);
        }

        /* Legend */
        .seat-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #404040;
            border-radius: 8px;
        }

        .legend-seat {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            font-size: 0.7em;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .legend-seat.available { background: #4caf50; color: white; }
        .legend-seat.booked { background: #f44336; color: white; }
        .legend-seat.selected { background: #ffd700; color: #1a1a1a; }

        /* Selection Display */
        .selection-display {
            background: #404040;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
        }

        .selection-text {
            font-size: 1.1em;
            margin-bottom: 15px;
            color: #f5f5f5;
        }

        .selection-text.has-selection {
            color: #ffd700;
            font-weight: 600;
        }

        /* Button */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px 30px;
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            width: 100%;
            max-width: 300px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 53, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn i {
            margin-right: 8px;
        }

        /* Back Button */
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #2d2d2d;
            color: #f5f5f5;
            border: 2px solid #555;
            border-radius: 8px;
            padding: 10px 15px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .back-button:hover {
            border-color: #ff6b35;
            color: #ff6b35;
        }

        .back-button i {
            margin-right: 6px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .page-header {
                padding: 20px;
            }
            
            .page-header h2 {
                font-size: 1.6em;
            }
            
            .seat {
                width: 32px;
                height: 32px;
                font-size: 0.7em;
            }
            
            .row-label {
                min-width: 55px;
                font-size: 0.8em;
                padding: 5px 8px;
            }
            
            .seat-legend {
                gap: 15px;
            }
            
            .back-button {
                top: 10px;
                left: 10px;
                padding: 8px 12px;
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <a href="select_course.php" class="back-button">
        <i class="fas fa-arrow-left"></i>กลับ
    </a>

    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-chair"></i>จองที่นั่ง: <?php echo htmlspecialchars($course['course_name']); ?></h2>
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
            <input type="hidden" id="selected_seat_input" name="seat_number">

            <div class="form-section">
                <div class="form-group">
                    <label for="student_id"><i class="fas fa-user"></i> รหัสนักศึกษา:</label>
                    <input type="text" id="student_id" name="student_id" placeholder="กรอกรหัสนักศึกษา" required>
                </div>
            </div>

            <div class="seat-map-section">
                <h3 class="seat-map-title">
                    <i class="fas fa-mouse-pointer"></i>คลิกเลือกที่นั่ง
                </h3>

                <div class="seat-legend">
                    <div class="legend-item">
                        <div class="legend-seat available">A1</div>
                        <span>ว่าง</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat booked">B2</div>
                        <span>จอง</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat selected">C3</div>
                        <span>เลือก</span>
                    </div>
                </div>
                
                <div class="lab-map-wrapper">
                    <div class="map-boundary">หน้าสุด / Front</div>

                    <div class="seat-map" id="seatMap">
                        <?php
                        $total_seats_generated = 0;
                        $max_total_seats = 170;
                        $row_char_code = 65; // 'A'

                        while ($total_seats_generated < $max_total_seats) {
                            $current_row = chr($row_char_code);

                            if ($current_row == 'E' || $current_row == 'K') {
                                $cols_in_row = 10;
                            } else {
                                $cols_in_row = 12;
                            }

                            $remaining_seats = $max_total_seats - $total_seats_generated;
                            $cols_to_generate = min($cols_in_row, $remaining_seats);

                            if ($cols_to_generate <= 0) break;

                            echo "<div class='seat-row'>";
                            echo "<div class='row-label'>แถว {$current_row}</div>";
                            
                            echo "<div class='seat-block-left'>";
                            $num_seats_in_left_block = ($cols_in_row == 10) ? 5 : 6;
                            for ($c = 1; $c <= $num_seats_in_left_block; $c++) {
                                if ($total_seats_generated >= $max_total_seats) break;
                                $seat_label = "{$current_row}{$c}";
                                $is_booked = in_array($seat_label, $booked_seats);
                                $class = $is_booked ? 'booked' : 'available';
                                echo "<div class='seat {$class}' data-seat='{$seat_label}'>{$seat_label}</div>";
                                $total_seats_generated++;
                            }
                            echo "</div>";

                            if ($cols_to_generate > $num_seats_in_left_block) {
                                echo "<div class='seat-gap'></div>";
                                echo "<div class='seat-block-right'>";
                                for ($c = $num_seats_in_left_block + 1; $c <= $cols_to_generate; $c++) {
                                    if ($total_seats_generated >= $max_total_seats) break;
                                    $seat_label = "{$current_row}{$c}";
                                    $is_booked = in_array($seat_label, $booked_seats);
                                    $class = $is_booked ? 'booked' : 'available';
                                    echo "<div class='seat {$class}' data-seat='{$seat_label}'>{$seat_label}</div>";
                                    $total_seats_generated++;
                                }
                                echo "</div>";
                            }
                            
                            echo "<div class='row-label'>แถว {$current_row}</div>";
                            echo "</div>";

                            $row_char_code++;
                            if ($total_seats_generated >= $max_total_seats) break;
                        }
                        ?>
                    </div>
                    
                    <div class="map-boundary bottom-boundary">หลังสุด / Back</div>
                </div>

                <div class="selection-display">
                    <div class="selection-text" id="selectedSeatDisplay">ยังไม่ได้เลือกที่นั่ง</div>
                    <button type="submit" class="btn" id="confirmBookingBtn" disabled>
                        <i class="fas fa-check-circle"></i>ยืนยันการจอง
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        const seatMap = document.getElementById('seatMap');
        const selectedSeatInput = document.getElementById('selected_seat_input');
        const selectedSeatDisplay = document.getElementById('selectedSeatDisplay');
        const confirmBookingBtn = document.getElementById('confirmBookingBtn');
        const errorMessage = document.getElementById('errorMessage');
        let currentSelectedSeat = null;

        // Seat selection handler
        seatMap.addEventListener('click', function(event) {
            const clickedSeat = event.target.closest('.seat');
            
            if (clickedSeat && !clickedSeat.classList.contains('booked')) {
                // Remove previous selection
                if (currentSelectedSeat) {
                    currentSelectedSeat.classList.remove('selected');
                }

                // Select new seat
                clickedSeat.classList.add('selected');
                currentSelectedSeat = clickedSeat;
                selectedSeatInput.value = clickedSeat.dataset.seat;
                
                // Update display
                selectedSeatDisplay.textContent = `คุณเลือกที่นั่ง: ${clickedSeat.dataset.seat}`;
                selectedSeatDisplay.classList.add('has-selection');
                
                confirmBookingBtn.disabled = false;
                errorMessage.style.display = 'none';
                
            } else if (clickedSeat && clickedSeat.classList.contains('booked')) {
                showError('ที่นั่งนี้ถูกจองแล้ว กรุณาเลือกที่นั่งอื่น');
            }
        });

        // Form submission handler
        document.querySelector('form').addEventListener('submit', function(event) {
            const studentId = document.getElementById('student_id').value;
            const seatNumber = selectedSeatInput.value;
            
            if (!studentId.trim()) {
                showError('กรุณากรอกรหัสนักศึกษา');
                event.preventDefault();
                return;
            }
            
            if (!seatNumber) {
                showError('กรุณาเลือกที่นั่ง');
                event.preventDefault();
                return;
            }
            
            // Show loading
            confirmBookingBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>กำลังดำเนินการ...';
            confirmBookingBtn.disabled = true;
        });

        // Error display function
        function showError(message) {
            errorMessage.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            errorMessage.style.display = 'flex';
            
            setTimeout(() => {
                errorMessage.style.display = 'none';
            }, 5000);
        }

        // Handle URL messages
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const messageParam = urlParams.get('message');
            const detailParam = urlParams.get('detail');

            if (messageParam) {
                let messageText = '';
                let messageType = 'error';

                if (messageParam === 'success') {
                    messageText = 'การจอง/ยืนยันสำเร็จ!';
                    messageType = 'success';
                } else if (messageParam === 'invalid_data') {
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
        });
    </script>
</body>
</html>