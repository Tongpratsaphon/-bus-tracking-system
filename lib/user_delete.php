<?php
include 'db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามีการส่งค่า user_id มาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['success'] = "✅ ลบผู้ใช้สำเร็จ!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ ไม่สามารถลบผู้ใช้ได้: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "❌ ข้อมูลไม่ถูกต้อง!";
}

// กลับไปที่หน้าจัดการผู้ใช้
header("Location: admin_dashboard.php?page=users");
exit();
