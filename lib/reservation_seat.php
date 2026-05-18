<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "ผู้ใช้ทั่วไป";

// รับค่าจากฟอร์มก่อนหน้า
$start = $_POST['start'] ?? '';
$end = $_POST['end'] ?? '';
$period = $_POST['period'] ?? '';
$time = $_POST['time'] ?? '';

try {
    include 'db.php';
    $today = date('Y-m-d');

    // ✅ ลำดับสถานี
    $stations = ['ตึกB', 'หอใน', 'ประทิวทอง', 'หอหรู'];

    // หาตำแหน่งเริ่มและจบของผู้ใช้
    $user_start_index = array_search($start, $stations);
    $user_end_index = array_search($end, $stations);

    // ดึงข้อมูลการจองทั้งหมดในวันเดียวกันและช่วงเวลาเดียวกัน
    $stmt = $conn->prepare("
        SELECT seat_no, start_point, end_point FROM reservations 
        WHERE reservation_date = :today 
        AND time = :time 
        AND period = :period
    ");
    $stmt->execute([
        ':today' => $today,
        ':time' => $time,
        ':period' => $period
    ]);

    $reservedSeats = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $reserved_start_index = array_search($row['start_point'], $stations);
        $reserved_end_index = array_search($row['end_point'], $stations);

        // ✅ เช็กช่วงสถานีว่าทับกันไหม
        if (
            ($user_start_index < $reserved_end_index) &&
            ($user_end_index > $reserved_start_index)
        ) {
            $reservedSeats[] = $row['seat_no'];
        }
    }
} catch (PDOException $e) {
    $reservedSeats = [];
    error_log("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จองที่นั่ง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #333; color: white; font-family: 'Poppins', sans-serif; }
        .seat { width: 60px; height: 60px; margin: 5px; background-color: #ccc; border-radius: 8px; display: inline-block; text-align: center; line-height: 60px; font-size: 18px; cursor: pointer; }
        .selected { background-color: gold; }
        .occupied { background-color: #555; cursor: not-allowed; }
        .driver { background-color: black; color: white; font-weight: bold; }
        .seat-layout { display: flex; flex-wrap: wrap; justify-content: center; max-width: 400px; margin: 20px auto; }
        .action-button { margin-top: 20px; text-align: center; }
        .btn-reserve { background-color: green; color: white; font-size: 20px; padding: 10px 20px; }
        .legend { margin-top: 20px; text-align: center; }
        .legend span { margin: 5px; padding: 10px; border-radius: 5px; display: inline-block; }
        .vacant { background-color: #ccc; }
        .selected { background-color: gold; }
        .driver { background-color: black; color: white; }
    </style>
</head>
<body>
<div class="container text-center">
    <h2>เลือกที่นั่งสำหรับเดินทาง</h2>
    <form action="reservation_confirm.php" method="POST">
        <div class="seat-layout">
            <div class="seat driver">🚍</div>
            <?php for ($i = 1; $i <= 16; $i++): ?>
                <div class="seat <?php echo in_array($i, $reservedSeats) ? 'occupied' : ''; ?>" 
                     data-seat="<?= $i; ?>" id="seat-<?= $i; ?>" 
                     title="<?= in_array($i, $reservedSeats) ? 'ที่นั่งนี้ถูกจองแล้ว' : 'เลือกที่นั่งนี้' ?>">
                     <?= $i; ?>
                </div>
            <?php endfor; ?>
        </div>

        <input type="hidden" name="seat_no" id="selected_seat" value="">
        <input type="hidden" name="start" value="<?= htmlspecialchars($start) ?>">
        <input type="hidden" name="end" value="<?= htmlspecialchars($end) ?>">
        <input type="hidden" name="period" value="<?= htmlspecialchars($period) ?>">
        <input type="hidden" name="time" value="<?= htmlspecialchars($time) ?>">

        <div class="action-button">
            <button type="submit" class="btn btn-success btn-reserve" onclick="return validateSeat();">ยืนยันที่นั่ง</button>
        </div>
    </form>
    <div class="legend">
        <span class="vacant">ว่าง</span>
        <span class="selected">ที่เลือก</span>
        <span class="occupied">ไม่ว่าง</span>
        <span class="driver">คนขับ</span>
    </div>
</div>

<script>
    const seats = document.querySelectorAll('.seat');
    const selectedSeatInput = document.getElementById('selected_seat');

    seats.forEach(seat => {
        seat.addEventListener('click', function () {
            if (this.classList.contains('occupied') || this.classList.contains('driver')) {
                alert('❌ ที่นั่งนี้ไม่สามารถเลือกได้');
                return;
            }

            if (this.classList.contains('selected')) {
                this.classList.remove('selected');
                selectedSeatInput.value = '';
            } else {
                seats.forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');
                selectedSeatInput.value = this.dataset.seat;
            }
        });
    });

    function validateSeat() {
        if (!selectedSeatInput.value) {
            alert("กรุณาเลือกที่นั่งก่อนกดยืนยัน!");
            return false;
        }
        return true;
    }
</script>
</body>
</html>
