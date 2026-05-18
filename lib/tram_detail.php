<?php
include 'db.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่ามีค่าหมายเลขรถรางที่ส่งมาหรือไม่
if (!isset($_GET['tram_number'])) {
    echo "ไม่พบข้อมูลรถราง";
    exit;
}

$tram_number = $_GET['tram_number'];

try {
    $stmt = $conn->prepare("SELECT * FROM trams WHERE tram_number = :tram_number");
    $stmt->bindParam(':tram_number', $tram_number);
    $stmt->execute();
    $tram = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tram) {
        echo "ไม่พบข้อมูลรถรางหมายเลขนี้";
        exit;
    }
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดรถราง <?= htmlspecialchars($tram_number) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>รายละเอียดรถราง: <?= htmlspecialchars($tram['tram_number']) ?></h2>
    <table>
        <tr><th>หมายเลขรถราง</th><td><?= htmlspecialchars($tram['tram_number']) ?></td></tr>
        <tr><th>ละติจูด</th><td><?= htmlspecialchars($tram['current_latitude']) ?></td></tr>
        <tr><th>ลองจิจูด</th><td><?= htmlspecialchars($tram['current_longitude']) ?></td></tr>
        <tr><th>สถานะ</th><td><?= htmlspecialchars($tram['status']) ?></td></tr>
        <tr><th>อัปเดตล่าสุด</th><td><?= htmlspecialchars($tram['updated_at']) ?></td></tr>
    </table>
    <br>
    <a href="map.php">🔙 กลับไปที่แผนที่</a>
</body>
</html>
