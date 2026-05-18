<?php
session_start();

// ตรวจสอบสิทธิ์ผู้ใช้
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'db.php';

    $user_id = $_SESSION['user_id'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $period = $_POST['period'];
    $time = $_POST['time'];
    $seat_no = $_POST['seat_no'];
    $today = date('Y-m-d'); // ใช้วันที่ปัจจุบันจริง

    try {
        $stations = ['ตึกB', 'หอใน', 'ประทิวทอง', 'หอหรู'];
        $start_index = array_search($start, $stations);
        $end_index = array_search($end, $stations);

        if ($start_index === false || $end_index === false || $start_index === $end_index) {
            throw new Exception("ข้อมูลสถานีไม่ถูกต้อง");
        }

        $min = min($start_index, $end_index);
        $max = max($start_index, $end_index);
        $selected_range = array_slice($stations, $min, $max - $min + 1);

        // ดึงการจองทั้งหมดของวันนี้ในช่วงเวลาเดียวกัน
        $stmt = $conn->prepare("
            SELECT seat_no, start_point, end_point 
            FROM reservations 
            WHERE reservation_date = :date AND period = :period AND time = :time
        ");
        $stmt->execute([
            ':date' => $today,
            ':period' => $period,
            ':time' => $time
        ]);

        $conflict = false;

        while ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($res['seat_no'] == $seat_no) {
                $res_start_index = array_search($res['start_point'], $stations);
                $res_end_index = array_search($res['end_point'], $stations);

                $res_min = min($res_start_index, $res_end_index);
                $res_max = max($res_start_index, $res_end_index);
                $res_range = array_slice($stations, $res_min, $res_max - $res_min + 1);

                if (count(array_intersect($selected_range, $res_range)) > 1) {
                    $conflict = true;
                    break;
                }
            }
        }

        if ($conflict) {
            echo "<script>alert('❌ ที่นั่งนี้ถูกจองแล้วในเส้นทางที่ทับกัน'); window.location.href = 'reservation_route.php';</script>";
            exit();
        }

        // บันทึกข้อมูลการจอง
        $insert = $conn->prepare("INSERT INTO reservations 
            (user_id, start_point, end_point, period, time, seat_no, reservation_date)
            VALUES (:user_id, :start, :end, :period, :time, :seat_no, :date)");

        $insert->execute([
            ':user_id' => $user_id,
            ':start' => $start,
            ':end' => $end,
            ':period' => $period,
            ':time' => $time,
            ':seat_no' => $seat_no,
            ':date' => $today
        ]);

        echo "<script>alert('✅ จองที่นั่งเรียบร้อยแล้ว!'); window.location.href = 'reservation_route.php';</script>";
        exit();
    } catch (Exception $e) {
        echo "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
        exit();
    }
} else {
    header("Location: reservation_route.php");
    exit();
}
?>
