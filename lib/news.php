<?php
include 'db.php'; // เชื่อมต่อฐานข้อมูล

try {
    $stmt = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข่าวสารรถราง</title>
</head>
<body>
    <h2>📢 ข่าวสารรถราง</h2>
    <?php foreach ($news as $item): ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin: 10px 0;">
            <h3><?= htmlspecialchars($item['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($item['content'])) ?></p>
            <small>🕒 ประกาศเมื่อ: <?= $item['created_at'] ?></small>
        </div>
    <?php endforeach; ?>
</body>
</html>
