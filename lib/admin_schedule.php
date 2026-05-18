<?php
include 'db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// เพิ่มหรือแก้ไขข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $round = $_POST['round'];
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $start_station = $_POST['start_station'];
    $end_station = $_POST['end_station'];
    $time_period = $_POST['time_period'];

    if ($id) {
        $stmt = $conn->prepare("UPDATE tram_schedule SET round = :round, departure_time = :departure_time, arrival_time = :arrival_time, start_station = :start_station, end_station = :end_station, time_period = :time_period WHERE id = :id");
        $stmt->execute(compact('round', 'departure_time', 'arrival_time', 'start_station', 'end_station', 'time_period', 'id'));
        $_SESSION['message'] = "✅ แก้ไขเรียบร้อยแล้ว";
    } else {
        $stmt = $conn->prepare("INSERT INTO tram_schedule (round, departure_time, arrival_time, start_station, end_station, time_period) VALUES (:round, :departure_time, :arrival_time, :start_station, :end_station, :time_period)");
        $stmt->execute(compact('round', 'departure_time', 'arrival_time', 'start_station', 'end_station', 'time_period'));
        $_SESSION['message'] = "✅ เพิ่มตารางเรียบร้อย";
    }
    header("Location: admin_dashboard.php?page=schedule&time_period=" . urlencode($time_period));
    exit;
}

// ดึงข้อมูลสำหรับแก้ไข
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM tram_schedule WHERE id = :id");
    $stmt->execute(['id' => $_GET['edit']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ลบข้อมูล
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $time_period = $_GET['time_period'] ?? 'morning';
    $stmt = $conn->prepare("DELETE FROM tram_schedule WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $_SESSION['message'] = "🗑️ ลบข้อมูลเรียบร้อย";
    header("Location: admin_dashboard.php?page=schedule&time_period=" . urlencode($time_period));
    exit;
}

// ดึงตารางตามช่วงเวลา
$time_period = $_GET['time_period'] ?? 'morning';
$stmt = $conn->prepare("SELECT * FROM tram_schedule WHERE time_period = :time_period ORDER BY departure_time");
$stmt->execute(['time_period' => $time_period]);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>📋 จัดการตารางรถราง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }

        .container {
            background-color: #1f1f1f;
            padding: 30px;
            border-radius: 12px;
            margin-top: 40px;
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.1);
        }

        .form-control,
        .form-select {
            background-color: #2a2a2a;
            border: 1px solid #555;
            color: #ffffff;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #333;
            border-color: #00c46f;
            box-shadow: 0 0 0 0.2rem rgba(0, 255, 136, 0.25);
            color: #00ff88;
        }

        .card {
            background-color: #181818;
            border: 1px solid #333;
        }

        .table {
            background-color: #1a1a1a;
            color: #ffffff;
        }

        thead th {
            background-color: #333;
            color: #00ff88;
        }

        .table-hover tbody tr:hover {
            background-color: #2c2c2c;
        }

        .btn-primary {
            background-color: #00c46f;
            border: none;
        }

        .btn-primary:hover {
            background-color: #00a35a;
        }

        .btn-warning {
            background-color: #ffb300;
            border: none;
            color: #000;
        }

        .btn-danger {
            background-color: #e53935;
            border: none;
        }

        .alert {
            background-color: #00e676;
            color: #000;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">🚋 ตารางเดินรถ</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert text-center"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <form method="get" action="admin_dashboard.php" class="mb-3 text-center">
        <input type="hidden" name="page" value="schedule">
        <label for="time_period">เลือกช่วงเวลา:</label>
        <select name="time_period" onchange="this.form.submit()" class="form-select w-auto d-inline ms-2">
            <option value="morning" <?= $time_period === 'morning' ? 'selected' : '' ?>>เช้า</option>
            <option value="afternoon" <?= $time_period === 'afternoon' ? 'selected' : '' ?>>บ่าย</option>
            <option value="evening" <?= $time_period === 'evening' ? 'selected' : '' ?>>เย็น</option>
        </select>
    </form>

    <form action="admin_schedule.php" method="post" class="card card-body mb-4">
        <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
        <div class="row g-2">
            <div class="col-md-2">
                <input type="text" name="round" class="form-control" placeholder="รอบ" required value="<?= $editData['round'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <input type="time" name="departure_time" class="form-control" required value="<?= $editData['departure_time'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <input type="time" name="arrival_time" class="form-control" required value="<?= $editData['arrival_time'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <input type="text" name="start_station" class="form-control" placeholder="สถานีเริ่มต้น" required value="<?= $editData['start_station'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <input type="text" name="end_station" class="form-control" placeholder="สถานีปลายทาง" required value="<?= $editData['end_station'] ?? '' ?>">
            </div>
            <input type="hidden" name="time_period" value="<?= htmlspecialchars($time_period) ?>">
            <div class="col-md-2">
                <button type="submit" class="btn btn-<?= $editData ? 'warning' : 'primary' ?> w-100">
                    <?= $editData ? 'อัปเดต' : 'เพิ่ม' ?>
                </button>
            </div>
        </div>
    </form>

    <h4 class="text-center mb-3">🕒 ตารางรถรางช่วง <?= $time_period === 'morning' ? 'เช้า' : ($time_period === 'afternoon' ? 'บ่าย' : 'เย็น') ?></h4>

    <table class="table table-bordered table-striped table-hover text-center">
        <thead>
        <tr>
            <th>รอบ</th>
            <th>เวลาออก</th>
            <th>เวลาถึง</th>
            <th>สถานีเริ่ม</th>
            <th>สถานีปลายทาง</th>
            <th>จัดการ</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($schedules as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['round']) ?></td>
                <td><?= htmlspecialchars($s['departure_time']) ?></td>
                <td><?= htmlspecialchars($s['arrival_time']) ?></td>
                <td><?= htmlspecialchars($s['start_station']) ?></td>
                <td><?= htmlspecialchars($s['end_station']) ?></td>
                <td>
                    <a href="admin_dashboard.php?page=schedule&edit=<?= $s['id'] ?>&time_period=<?= $time_period ?>" class="btn btn-sm btn-warning">แก้ไข</a>
                    <a href="admin_schedule.php?delete=<?= $s['id'] ?>&time_period=<?= $time_period ?>" onclick="return confirm('ลบข้อมูลนี้?')" class="btn btn-sm btn-danger">ลบ</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
