<?php
include 'db.php'; // เชื่อมต่อฐานข้อมูล
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // ตรวจสอบ session ว่าถูกเริ่มไปแล้วหรือยัง
}

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!isset($conn)) {
    die("<p class='text-danger text-center'>❌ ไม่สามารถเชื่อมต่อฐานข้อมูลได้</p>");
}

try {
    $stmt = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['message'] = "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
    $news = []; // ป้องกัน error เมื่อฐานข้อมูลมีปัญหา
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข่าวสาร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #fff;
        }
        .container {
            background-color: #1e1e1e;
            padding: 30px;
            border-radius: 10px;
            margin-top: 40px;
        }
        h2 {
            color: #00ff88;
            margin-bottom: 30px;
        }
        .btn-primary {
            background-color: #00c46f;
            border: none;
        }
        .btn-primary:hover {
            background-color: #00a35a;
        }
        .btn-warning {
            background-color: #ffc107;
            border: none;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }
        table {
            background-color: #222;
        }
        thead.table-dark th {
            background-color: #333;
            color: #00ff88;
        }
        .alert {
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">📢 จัดการข่าวสาร</h2>

        <!-- แสดงข้อความแจ้งเตือน -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?= $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <a href="news_add.php" class="btn btn-primary mb-3">➕ เพิ่มข่าวใหม่</a>

        <!-- ตรวจสอบว่ามีข่าวหรือไม่ -->
        <?php if (empty($news)): ?>
            <p class="text-center text-muted">⛔ ยังไม่มีข่าวสาร</p>
        <?php else: ?>
            <table class="table table-bordered text-center table-dark">
                <thead class="table-dark">
                    <tr>
                        <th>หัวข้อข่าว</th>
                        <th>วันที่ประกาศ</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($news as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['title']) ?></td>
                        <td><?= $item['created_at'] ?></td>
                        <td>
                            <a href="news_edit.php?id=<?= $item['id'] ?>" class="btn btn-warning">✏️ แก้ไข</a>
                            <a href="news_delete.php?id=<?= $item['id'] ?>" class="btn btn-danger" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบข่าวนี้?');">❌ ลบ</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
