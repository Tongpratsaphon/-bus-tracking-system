<?php


include 'db.php';

try {
    $stmt = $conn->prepare("SELECT r.*, u.first_name, u.last_name, u.email
                            FROM reservations r
                            JOIN users u ON r.user_id = u.id
                            ORDER BY r.reservation_date DESC, r.time ASC");
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาด: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แดชบอร์ดผู้ดูแลระบบ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background-color: #222;
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            padding: 40px;
        }
        h2 {
            margin-bottom: 30px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: #333;
        }
        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #444;
        }
        th {
            background-color: #555;
        }
        tr:hover {
            background-color: #444;
        }
        .back-btn {
            margin-bottom: 20px;
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
        }
        .back-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
        <h2>ข้อมูลการจองทั้งหมดในระบบ</h2>

        <?php if (count($reservations) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ชื่อผู้จอง</th>
                    <th>อีเมล</th>
                    <th>ต้นทาง</th>
                    <th>ปลายทาง</th>
                    <th>ช่วงเวลา</th>
                    <th>เวลา</th>
                    <th>ที่นั่ง</th>
                    <th>วันที่จอง</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $res): ?>
                    <tr>
                        <td><?= htmlspecialchars($res['first_name'] . ' ' . $res['last_name']) ?></td>
                        <td><?= htmlspecialchars($res['email']) ?></td>
                        <td><?= htmlspecialchars($res['start_point']) ?></td>
                        <td><?= htmlspecialchars($res['end_point']) ?></td>
                        <td><?= htmlspecialchars($res['period']) ?></td>
                        <td><?= htmlspecialchars($res['time']) ?> น.</td>
                        <td><?= htmlspecialchars($res['seat_no']) ?></td>
                        <td><?= htmlspecialchars($res['reservation_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>ยังไม่มีข้อมูลการจองในระบบ</p>
        <?php endif; ?>
    </div>
</body>
</html>
