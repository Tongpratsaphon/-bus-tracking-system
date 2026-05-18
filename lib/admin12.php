<?php
try {
    include 'db.php';

    $stmt = $conn->query("SELECT reservations.id, users.username, users.first_name, users.last_name, users.phone, reservations.seat_no, reservations.reservation_time 
                          FROM reservations 
                          JOIN users ON reservations.user_id = users.id 
                          ORDER BY reservations.id ASC");
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $reservations = [];
    error_log("Database error: " . $e->getMessage());
}
?>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>หมายเลขจอง</th>
            <th>ชื่อผู้ใช้</th>
            <th>ชื่อ-นามสกุล</th>
            <th>เบอร์โทร</th>
            <th>หมายเลขที่นั่ง</th>
            <th>เวลาจอง</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reservations as $reservation): ?>
            <tr>
                <td><?= $reservation['id'] ?></td>
                <td><?= $reservation['username'] ?></td>
                <td><?= $reservation['first_name'] ?> <?= $reservation['last_name'] ?></td>
                <td><?= $reservation['phone'] ?></td>
                <td><?= $reservation['seat_no'] ?></td>
                <td><?= $reservation['reservation_time'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
