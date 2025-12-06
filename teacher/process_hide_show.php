<?php
// teacher/process_hide_show.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once('../db_connect.php');

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($course_id == 0 || !in_array($action, ['hide', 'show'])) {
    header('Location: dashboard.php?message=status_update_invalid');
    exit();
}

$is_hidden = ($action == 'hide') ? 1 : 0;

try {
    $stmt = $pdo->prepare("UPDATE courses SET is_hidden = ? WHERE course_id = ? AND created_by_user_id = ?");
    $stmt->execute([$is_hidden, $course_id, $_SESSION['user_id']]);
    header('Location: dashboard.php?message=status_updated_success');
    exit();
} catch (PDOException $e) {
    header('Location: dashboard.php?message=status_update_error&detail=' . urlencode($e->getMessage()));
    exit();
}
?>