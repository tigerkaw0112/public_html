<?php
// db_connect.php
// รองรับทั้ง Docker และ XAMPP

// สำหรับ Docker: ใช้ 'mysql' (ชื่อ service ใน docker-compose.yml)
// สำหรับ XAMPP: ใช้ 'localhost' หรือ '127.0.0.1'
$host = getenv('DB_HOST') ?: (getenv('DOCKER_ENV') ? 'mysql' : 'localhost');
$dbname = getenv('DB_NAME') ?: '4509882_tigerlion';
$port = getenv('DB_PORT') ?: '3306';

// ตรวจสอบว่าเป็น Docker หรือ XAMPP
if ($host === 'mysql' || getenv('DOCKER_ENV')) {
    // Docker: ใช้ environment variables
    $username = getenv('DB_USER') ?: 'tigerlion';
    $password = getenv('DB_PASSWORD') ?: 'Tiger1234';
} else {
    // XAMPP: ใช้ root user (default)
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+07:00'"
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
