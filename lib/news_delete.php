<?php
session_start();
include 'db.php';

if (isset($_GET['id'])) {
    $news_id = $_GET['id'];

    try {
        $stmt = $conn->prepare("DELETE FROM news WHERE id = :id");
        $stmt->bindParam(':id', $news_id);
        $stmt->execute();

        $_SESSION['message'] = "ลบข่าวสำเร็จ!";
        $_SESSION['message_type'] = "success";

    } catch (PDOException $e) {
        $_SESSION['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
}

// ส่งกลับไปหน้าจัดการข่าวสาร
header("Location: admin_dashboard.php?page=news");
exit();
