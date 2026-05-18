<?php
include 'db.php'; // เชื่อมต่อฐานข้อมูล

try {
    // ดึงข้อมูลรถรางทั้งหมด
    $stmt = $conn->query("SELECT * FROM trams");
    $trams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลรถราง</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>รายการรถราง</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>หมายเลขรถ</th>
            <th>ละติจูด</th>
            <th>ลองจิจูด</th>
            <th>สถานะ</th>
            <th>อัปเดตล่าสุด</th>
        </tr>
        <?php foreach ($trams as $tram): ?>
        <tr>
            <td><?= $tram['id'] ?></td>
            <td><?= $tram['tram_number'] ?></td>
            <td><?= $tram['current_latitude'] ?></td>
            <td><?= $tram['current_longitude'] ?></td>
            <td><?= $tram['status'] ?></td>
            <td><?= $tram['updated_at'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
