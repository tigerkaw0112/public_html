<?php
// teacher/generate_report.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once('../db_connect.php');

// ตั้งค่า Time Zone
date_default_timezone_set('Asia/Bangkok');

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$report_type = isset($_GET['type']) ? $_GET['type'] : 'single';
$week_filter = isset($_GET['week']) ? $_GET['week'] : 'all';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$filename = "report_";
$sql_where = "WHERE 1=1";
$params = [];

// กำหนดช่วงวันที่ตามตัวเลือก
$date_condition = "";
$date_params = [];

switch ($week_filter) {
    case 'current':
        $start_of_week = date('Y-m-d', strtotime('monday this week'));
        $end_of_week = date('Y-m-d', strtotime('sunday this week'));
        $date_condition = " AND DATE(b.session_date) BETWEEN ? AND ?";
        $date_params = [$start_of_week, $end_of_week];
        $filename .= "current_week_";
        break;
        
    case 'last':
        $start_of_week = date('Y-m-d', strtotime('monday last week'));
        $end_of_week = date('Y-m-d', strtotime('sunday last week'));
        $date_condition = " AND DATE(b.session_date) BETWEEN ? AND ?";
        $date_params = [$start_of_week, $end_of_week];
        $filename .= "last_week_";
        break;
        
    case 'specific':
        if ($start_date && $end_date) {
            $date_condition = " AND DATE(b.session_date) BETWEEN ? AND ?";
            $date_params = [$start_date, $end_date];
            $filename .= "custom_" . str_replace('-', '', $start_date) . "_to_" . str_replace('-', '', $end_date) . "_";
        }
        break;
        
    case 'last_4_weeks':
        $start_of_period = date('Y-m-d', strtotime('-4 weeks monday'));
        $end_of_period = date('Y-m-d', strtotime('sunday this week'));
        $date_condition = " AND DATE(b.session_date) BETWEEN ? AND ?";
        $date_params = [$start_of_period, $end_of_period];
        $filename .= "last_4_weeks_";
        break;
        
    default: // 'all'
        $filename .= "all_time_";
        break;
}

if ($report_type == 'single' && $course_id > 0) {
    $stmt_course_info = $pdo->prepare("SELECT course_name, course_code, section, room_name FROM courses WHERE course_id = ? AND created_by_user_id = ?");
    $stmt_course_info->execute([$course_id, $_SESSION['user_id']]);
    $course_info = $stmt_course_info->fetch(PDO::FETCH_ASSOC);

    if (!$course_info) {
        die("ไม่พบวิชาหรือคุณไม่มีสิทธิ์ดาวน์โหลดรายงานนี้");
    }
    $filename .= "{$course_info['course_code']}_{$course_info['section']}_";
    $sql_where .= " AND b.course_id = ?";
    $params[] = $course_id;
} else {
    $sql_where .= " AND c.created_by_user_id = ?";
    $params[] = $_SESSION['user_id'];
    $filename .= "all_courses_";
}

// เพิ่มเงื่อนไขวันที่
$sql_where .= $date_condition;
$params = array_merge($params, $date_params);

$filename .= date('Ymd_His') . ".csv";

// Query เพื่อดึงข้อมูลพร้อมข้อมูลสัปดาห์
$sql = "
    SELECT
        c.course_name,
        c.course_code,
        c.section,
        c.room_name,
        b.session_date,
        b.check_in_time,
        b.session_cleared_at,
        b.student_id,
        s.full_name,
        b.seat_number,
        b.status,
        YEAR(b.session_date) as year,
        WEEK(b.session_date, 1) as week_number,
        DATE_FORMAT(b.session_date, '%Y-%u') as year_week,
        DATE_FORMAT(DATE_SUB(b.session_date, INTERVAL WEEKDAY(b.session_date) DAY), '%Y-%m-%d') as week_start,
        DATE_FORMAT(DATE_ADD(DATE_SUB(b.session_date, INTERVAL WEEKDAY(b.session_date) DAY), INTERVAL 6 DAY), '%Y-%m-%d') as week_end,
        CONCAT('สัปดาห์ที่ ', WEEK(b.session_date, 1), '/', YEAR(b.session_date)) as week_label,
        CASE DAYOFWEEK(b.session_date)
            WHEN 1 THEN 'อาทิตย์'
            WHEN 2 THEN 'จันทร์'
            WHEN 3 THEN 'อังคาร'
            WHEN 4 THEN 'พุธ'
            WHEN 5 THEN 'พฤหัสบดี'
            WHEN 6 THEN 'ศุกร์'
            WHEN 7 THEN 'เสาร์'
        END as day_name_th
    FROM bookings b
    JOIN courses c ON b.course_id = c.course_id
    LEFT JOIN students s ON b.student_id = s.student_id
    {$sql_where}
    ORDER BY b.session_date DESC, c.course_name, b.check_in_time ASC
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($data) == 0) {
        die("ไม่มีข้อมูลการจอง/เข้าเรียนในช่วงเวลาที่เลือก");
    }

    // สร้างสถิติตามสัปดาห์
    $weekly_stats = [];
    $course_stats = [];
    
    foreach ($data as $row) {
        $week_key = $row['year_week'];
        $course_key = $row['course_code'] . '-' . $row['section'];
        
        // สถิติตามสัปดาห์
        if (!isset($weekly_stats[$week_key])) {
            $weekly_stats[$week_key] = [
                'week_label' => $row['week_label'],
                'week_start' => $row['week_start'],
                'week_end' => $row['week_end'],
                'total_students' => 0,
                'courses' => []
            ];
        }
        
        $weekly_stats[$week_key]['total_students']++;
        
        if (!in_array($course_key, $weekly_stats[$week_key]['courses'])) {
            $weekly_stats[$week_key]['courses'][] = $course_key;
        }
        
        // สถิติตามวิชา
        if (!isset($course_stats[$course_key])) {
            $course_stats[$course_key] = [
                'course_name' => $row['course_name'],
                'total_students' => 0,
                'weeks' => []
            ];
        }
        
        $course_stats[$course_key]['total_students']++;
        if (!in_array($week_key, $course_stats[$course_key]['weeks'])) {
            $course_stats[$course_key]['weeks'][] = $week_key;
        }
    }

    // ส่งออกไฟล์ CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // BOM for UTF-8 in Excel
    fputs($output, "\xEF\xBB\xBF");

    // === รายงานสรุป ===
    fputcsv($output, ['รายงานการเข้าเรียน']);
    fputcsv($output, ['วันที่สร้างรายงาน', date('d/m/Y H:i:s')]);
    fputcsv($output, ['']);

    // === สรุปสถิติตามสัปดาห์ ===
    fputcsv($output, ['สรุปสถิติตามสัปดาห์']);
    fputcsv($output, ['สัปดาห์', 'ช่วงวันที่', 'จำนวนนักศึกษา', 'จำนวนวิชา']);
    
    foreach ($weekly_stats as $week_data) {
        fputcsv($output, [
            $week_data['week_label'],
            date('d/m/Y', strtotime($week_data['week_start'])) . ' - ' . date('d/m/Y', strtotime($week_data['week_end'])),
            $week_data['total_students'],
            count($week_data['courses'])
        ]);
    }
    
    fputcsv($output, ['']);
    
    // === สรุปสถิติตามวิชา ===
    fputcsv($output, ['สรุปสถิติตามวิชา']);
    fputcsv($output, ['วิชา', 'จำนวนนักศึกษารวม', 'จำนวนสัปดาห์ที่มีการเรียน']);
    
    foreach ($course_stats as $course_data) {
        fputcsv($output, [
            $course_data['course_name'],
            $course_data['total_students'],
            count($course_data['weeks'])
        ]);
    }
    
    fputcsv($output, ['']);
    fputcsv($output, ['']);
    
    // === รายละเอียดทั้งหมด ===
    fputcsv($output, ['รายละเอียดการเข้าเรียนทั้งหมด']);
    fputcsv($output, ['']);

    // จัดกลุ่มข้อมูลตามสัปดาห์
    $grouped_data = [];
    foreach ($data as $row) {
        $week_key = $row['year_week'];
        if (!isset($grouped_data[$week_key])) {
            $grouped_data[$week_key] = [];
        }
        $grouped_data[$week_key][] = $row;
    }
    
    // ส่งออกข้อมูลตามสัปดาห์
    foreach ($grouped_data as $week_key => $week_data) {
        $first_row = $week_data[0];
        
        // หัวข้อสัปดาห์
        fputcsv($output, ['']);
        fputcsv($output, [$first_row['week_label'] . ' (' . date('d/m/Y', strtotime($first_row['week_start'])) . ' - ' . date('d/m/Y', strtotime($first_row['week_end'])) . ')']);
        
        // หัวตาราง
        fputcsv($output, [
            'วันที่',
            'วัน',
            'ชื่อวิชา',
            'รหัสวิชา',
            'เซคชั่น',
            'ห้องเรียน',
            'เวลาเข้าเรียน',
            'รหัสนักศึกษา',
            'ชื่อ-สกุล',
            'ที่นั่ง',
            'สถานะ',
            'เวลาเคลียร์ห้อง'
        ]);
        
        // ข้อมูลในสัปดาห์นั้น
        foreach ($week_data as $row) {
            fputcsv($output, [
                date('d/m/Y', strtotime($row['session_date'])),
                $row['day_name_th'],
                $row['course_name'],
                $row['course_code'],
                $row['section'],
                $row['room_name'],
                $row['check_in_time'] ? date('H:i:s', strtotime($row['check_in_time'])) : '-',
                $row['student_id'],
                $row['full_name'],
                $row['seat_number'] ?: '-',
                $row['status'],
                $row['session_cleared_at'] ? date('d/m/Y H:i:s', strtotime($row['session_cleared_at'])) : '-'
            ]);
        }
    }

    fclose($output);
    exit();

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}
?>