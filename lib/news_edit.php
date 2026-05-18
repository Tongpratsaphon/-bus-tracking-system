<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "ไม่พบข่าวที่ต้องการแก้ไข!";
    exit();
}

// ดึงข้อมูลข่าวจากฐานข้อมูล
$stmt = $conn->prepare("SELECT title, content FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    echo "ไม่พบข่าวที่ต้องการแก้ไข!";
    exit();
}

// บันทึกการแก้ไข
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("UPDATE news SET title = ?, content = ? WHERE id = ?");
    $stmt->execute([$title, $content, $id]);

    header("Location: admin_dashboard.php?page=news");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข่าวสาร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">📝 แก้ไขข่าวสาร</h2>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">หัวข้อข่าว:</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($news['title']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">เนื้อหาข่าว:</label>
            <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($news['content']); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">💾 บันทึกการแก้ไข</button>
    </form>

    <div class="mt-3 text-center">
            <a href="admin_dashboard.php?page=news" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>🔙 กลับไปหน้าจัดการข่าว</a>
        </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
