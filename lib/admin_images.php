<?php
include 'db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ฟังก์ชันอัปโหลดรูป
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $uploadDir = 'image/';
    $filename = basename($_FILES['image']['name']);
    $targetFile = $uploadDir . $filename;
    $imageType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($imageType, $allowedTypes)) {
        $_SESSION['message'] = "❌ ไฟล์ไม่รองรับ (jpg, jpeg, png, gif เท่านั้น)";
        $_SESSION['message_type'] = "danger";
    } elseif (file_exists($targetFile)) {
        $_SESSION['message'] = "⚠️ มีไฟล์นี้อยู่แล้วในระบบ";
        $_SESSION['message_type'] = "warning";
    } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $stmt = $conn->prepare("INSERT INTO images (filename, uploaded_at) VALUES (:filename, CURRENT_TIMESTAMP)");
        $stmt->execute(['filename' => $filename]);

        $_SESSION['message'] = "✅ อัปโหลดเรียบร้อย";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "❌ อัปโหลดไม่สำเร็จ";
        $_SESSION['message_type'] = "danger";
    }

    header("Location: admin_dashboard.php?page=images");
    exit;
}

// ฟังก์ชันลบรูป
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    $stmt = $conn->prepare("SELECT filename FROM images WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($image) {
        $filePath = 'image/' . $image['filename'];
        $stmt = $conn->prepare("DELETE FROM images WHERE id = :id");
        $stmt->execute(['id' => $id]);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $_SESSION['message'] = "🗑️ ลบรูปเรียบร้อย";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "❌ ไม่พบรูปภาพ";
        $_SESSION['message_type'] = "danger";
    }

    header("Location: admin_dashboard.php?page=images");
    exit;
}

// ดึงข้อมูลรูปทั้งหมด
try {
    $stmt = $conn->query("SELECT * FROM images ORDER BY uploaded_at DESC");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['message'] = "❌ ดึงข้อมูลไม่สำเร็จ: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
    $images = [];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการรูปภาพ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            background-color: #1f1f1f;
            padding: 30px;
            border-radius: 12px;
            margin-top: 40px;
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.1);
        }

        h2, h4 {
            color: #00ff88;
            text-shadow: 1px 1px 2px #000;
        }

        .form-control, .form-select {
            background-color: #2b2b2b;
            border: 1px solid #444;
            color: #ffffff;
        }

        .form-control::placeholder {
            color: #aaa;
        }

        .form-control:focus, .form-select:focus {
            background-color: #333;
            color: #00ff88;
            border-color: #00c46f;
            box-shadow: 0 0 0 0.2rem rgba(0, 255, 136, 0.25);
        }

        .card {
            background-color: #2c2c2c;
            border: 1px solid #444;
        }

        .table {
            background-color: #1e1e1e;
            color: #ffffff;
        }

        .table-hover tbody tr:hover {
            background-color: #2a2a2a;
        }

        thead.table-dark th {
            background-color: #333;
            color: #00ff88;
        }

        .btn-primary {
            background-color: #00c46f;
            border: none;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #00a35a;
        }

        .btn-warning {
            background-color: #ffb300;
            border: none;
            color: #000;
        }

        .btn-warning:hover {
            background-color: #ffa000;
        }

        .btn-danger {
            background-color: #e53935;
            border: none;
            color: #fff;
        }

        .btn-danger:hover {
            background-color: #c62828;
        }

        .btn-success {
            background-color: #00e676;
            color: #000;
        }

        .btn-success:hover {
            background-color: #00c853;
        }

        .alert {
            background-color: #00e676;
            color: #000;
            font-weight: bold;
            border-radius: 8px;
            text-align: center;
        }

        /* Force all text elements to be readable on dark background */
        .text-muted,
        .card-text,
        p,
        a,
        label,
        input,
        button,
        .table,
        .alert,
        h2,
        h4 {
            color: #ffffff !important;
        }

        .text-muted {
            color: #cccccc !important;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">🖼️ จัดการรูปภาพ</h2>

    <!-- แจ้งเตือน -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <!-- ฟอร์มอัปโหลด -->
    <form action="admin_images.php" method="post" enctype="multipart/form-data" class="mb-4">
        <div class="input-group">
            <input type="file" name="image" class="form-control" required>
            <button type="submit" class="btn btn-success">📤 อัปโหลด</button>
        </div>
    </form>

    <!-- แสดงรูป -->
    <?php if (empty($images)): ?>
        <p class="text-center text-muted">⛔ ยังไม่มีรูปภาพ</p>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            <?php foreach ($images as $img): ?>
                <div class="col">
                    <div class="card shadow-sm">
                        <img src="image/<?= htmlspecialchars($img['filename']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="รูปภาพ">
                        <div class="card-body text-center">
                            <p class="card-text small text-muted">อัปโหลดเมื่อ: <?= $img['uploaded_at'] ?></p>
                            <a href="admin_images.php?delete_id=<?= $img['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบรูปภาพนี้?');">❌ ลบ</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
