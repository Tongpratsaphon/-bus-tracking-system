<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ตรวจสอบการเพิ่มข่าวสาร
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (!empty($title) && !empty($content)) {
        try {
            $stmt = $conn->prepare("INSERT INTO news (title, content, created_at) VALUES (?, ?, NOW())");
            if ($stmt->execute([$title, $content])) {
                $_SESSION['message'] = "✅ เพิ่มข่าวสำเร็จ!";
                $_SESSION['message_type'] = "success";
                header("Location: admin_dashboard.php?page=news");
                exit();
            } else {
                $_SESSION['message'] = "❌ ไม่สามารถเพิ่มข่าวได้!";
                $_SESSION['message_type'] = "danger";
            }
        } catch (PDOException $e) {
            $_SESSION['message'] = "❌ ข้อผิดพลาด: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "⚠️ กรุณากรอกข้อมูลให้ครบ!";
        $_SESSION['message_type'] = "warning";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มข่าวสาร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-lg p-4 mx-auto" style="max-width: 600px;">
        <h3 class="text-center"><i class="bi bi-plus-circle"></i> เพิ่มข่าวสาร</h3>

        <!-- แสดงข้อความแจ้งเตือน -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?= $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">หัวข้อข่าว:</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">เนื้อหาข่าว:</label>
                <textarea name="content" class="form-control" rows="4" required></textarea>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary"><i class="bi bi-megaphone-fill"></i> เพิ่มข่าว</button>
            </div>
        </form>

        <div class="mt-3 text-center">
            <a href="admin_dashboard.php?page=news" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> กลับไปที่หน้าจัดการข่าว</a>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
