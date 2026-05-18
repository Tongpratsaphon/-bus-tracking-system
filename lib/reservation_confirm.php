<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start = $_POST['start'] ?? '';
    $end = $_POST['end'] ?? '';
    $period = $_POST['period'] ?? '';
    $time = $_POST['time'] ?? '';
    $seat_no = $_POST['selected_seat'] ?? '';
    $today = date('Y-m-d'); // ใช้วันที่ปัจจุบันจริง

    if (!$start || !$end || !$period || !$time || !$seat_no) {
        $_SESSION['error_message'] = "❌ ข้อมูลไม่ครบถ้วน กรุณากลับไปเลือกใหม่อีกครั้ง";
        header("Location: reservation_route.php");
        exit();
    }

    // ลำดับสถานี
    $stations = ['ตึกB', 'หอใน', 'ประทิวทอง', 'หอหรู'];
    $start_index = array_search($start, $stations);
    $end_index = array_search($end, $stations);

    if ($start_index === false || $end_index === false || $start_index === $end_index) {
        $_SESSION['error_message'] = "❌ เส้นทางไม่ถูกต้อง";
        header("Location: reservation_route.php");
        exit();
    }

    // จัดช่วงเส้นทาง
    $min = min($start_index, $end_index);
    $max = max($start_index, $end_index);
    $route_range = array_slice($stations, $min, $max - $min + 1);

    // ดึงข้อมูลผู้ใช้
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error_message'] = "❌ ไม่พบข้อมูลผู้ใช้";
        header("Location: reservation_route.php");
        exit();
    }

    // ตรวจสอบที่นั่งนี้ ถูกใช้ใน route ทับซ้อนหรือยัง
    $stmt_check = $conn->prepare("
        SELECT * FROM reservations 
        WHERE reservation_date = :date
        AND time = :time 
        AND period = :period
        AND seat_no = :seat_no
    ");
    $stmt_check->execute([
        ':date' => $today,
        ':time' => $time,
        ':period' => $period,
        ':seat_no' => $seat_no
    ]);

    $reservations = $stmt_check->fetchAll(PDO::FETCH_ASSOC);
    foreach ($reservations as $res) {
        $res_start = array_search($res['start_point'], $stations);
        $res_end = array_search($res['end_point'], $stations);

        $res_min = min($res_start, $res_end);
        $res_max = max($res_start, $res_end);
        $res_range = array_slice($stations, $res_min, $res_max - $res_min + 1);

        // ถ้ามีช่วงทับซ้อน
        if (count(array_intersect($route_range, $res_range)) > 1) {
            $_SESSION['error_message'] = "❌ ที่นั่งนี้ถูกจองในช่วงเส้นทางที่ทับซ้อนกันแล้ว";
            header("Location: reservation_route.php");
            exit();
        }
    }
} else {
    header("Location: reservation_route.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ยืนยันการจอง</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpg" href="/img/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #333;
            color: white;
            font-family: 'Poppins', sans-serif;
        }
        .container { margin-top: 60px; }
        .card {
            background-color: #444;
            border-radius: 10px;
            padding: 30px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
        }
        .btn-confirm {
            background-color: green;
            color: white;
            font-size: 18px;
            margin-top: 25px;
        }
        .detail {
            text-align: left;
            margin-bottom: 10px;
        }
        h2 { margin-bottom: 30px; }

        <>
            /* 🔹 ปรับพื้นหลังให้ไล่สีโทนม่วงสวยงาม */
        /* 🔹 พื้นหลังแบบไล่สีเคลื่อนไหว */
        /* 🔹 พื้นหลังแบบไล่สีเคลื่อนไหว */
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* 🔹 พื้นหลังไล่สีแบบฟ้า-ขาว */
        body {
            background: linear-gradient(135deg, #E0F7FA, #80D8FF);
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            color: #333;
        }

        .navbar {
            background: linear-gradient(to right, #0277BD, #039BE5);
            padding: 12px;
            max-width: 85%;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.3);
            margin-bottom: 3px; /* ✅ เพิ่มระยะห่างจาก Navigation Menu */
        }

        .navbar-brand {
            color: white;
            font-weight: bold;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            transition: 0.3s;
        }

        .navbar-brand:hover {
            color: #B3E5FC;
        }

        .nav-menu {
            background: linear-gradient(to right, #0288D1, #03A9F4);
            padding: 12px 15px; /* ✅ เพิ่ม padding ด้านบน-ล่างให้ดูสวยขึ้น */
            max-width: 85%;
            margin: 0px auto 30px auto; /* ✅ เพิ่มระยะห่างด้านบน */
            border-radius: 12px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.3);
        }
        .nav-menu .nav-link {
            color: white;
            font-weight: 600;
            padding: 12px 18px;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.2);
        }

        .nav-menu .nav-link:hover {
            background: rgba(255, 255, 255, 0.3);
            color: #FFC107;
            transform: scale(1.05);
        }

        /* ✅ ปรับระยะห่างระหว่างเมนูในแนวนอน */
        .nav-menu .nav {
            display: flex;
            justify-content: flex-start; /* ✅ ทำให้เมนูชิดซ้าย */
            gap: 15px; /* ✅ เพิ่มช่องว่างระหว่างเมนู */
        }

        .nav-menu .nav-item .nav-link {
            padding: 10px 18px;
            font-size: 1rem;
            min-width: 130px;
            text-align: center;
        }

        /* 🔹 กรอบข้อความทางซ้าย */
        .text-container {
            border: 2px solid #0288D1; /* ✅ กรอบสีน้ำเงิน */
            border-radius: 12px;
            padding: 15px;
            background: white; /* ✅ พื้นหลังขาว */
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.3); /* ✅ เงาสวยงาม */
        }

        /* 🔹 Content Area */
        .content-container {
            margin: 20px auto;
            max-width: 85%;
            padding: 25px;
            background: white;
            border-radius: 15px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* 🔹 กรอบรอบตารางเวลาฝั่งซ้าย */
        .table-container {
            border: 2px solid #0288D1; /* ✅ กรอบสีน้ำเงิน */
            border-radius: 12px;
            padding: 15px;
            background: #fff;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.3);
        }


        .carousel-container {
            border: none; /* ✅ เอากรอบออก */
            padding: 0; /* ✅ ลบ padding ออก */
            background: transparent; /* ✅ พื้นหลังโปร่งใส */
            box-shadow: none; /* ❌ เอาเงาออก */
        }

        /* 🔹 ทำให้ภาพในสไลด์แสดงผลเต็มพื้นที่ */
        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: contain; /* ✅ แสดงเต็มกรอบโดยไม่ถูกตัด */
            border-radius: 10px; /* ✅ ทำให้ขอบมน */
        }

        /* 🔹 ปรับ Card ให้ดูโดดเด่น */
        .card-custom {
            border-radius: 15px;
            background: white;
            color: #333;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
        }

        /* 🔹 ปรับหัวข้อ Card */
        .card-header {
            font-weight: bold;
            border-radius: 15px 15px 0 0;
            background: linear-gradient(to right, #0288D1, #03A9F4);
            color: white;
        }

        .btn-light {
            background: #ffffff;
            color: #0277BD;
            font-weight: bold;
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
        }

        .btn-light:hover {
            background: #B3E5FC;
            color: #0277BD;
        }

        /* 🔹 ชื่อผู้ใช้ */
        .username {
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 8px;
        }


        /* 🔹 ปุ่มออกจากระบบ */
        .btn-danger {
            background: #D32F2F;
            font-weight: bold;
            transition: all 0.3s ease-in-out;
        }

        .btn-danger:hover {
            background: #E53935;
            transform: scale(1.05);
        }

        /* 🔹 เปลี่ยนพื้นหลังของ Welcome Banner */
        .bg-warning {
            background: linear-gradient(to right, #03A9F4, #81D4FA);
            color: white !important;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            transition: 0.3s;
        }

        /* 🔹 เอฟเฟกต์ Hover ให้ Welcome Banner */
        .bg-warning:hover {
            background: linear-gradient(to right, #0288D1, #4FC3F7);
            transform: scale(1.02);
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.4);
        }

        /* 🔹 ปรับขนาดของ Layout สำหรับหน้าจอเล็ก */
        @media (max-width: 768px) {
            .navbar, .nav-menu, .content-container {
                max-width: 100%;
            }
        }

                /* 🔹 ฟอร์มเลือกเส้นทาง */
        form {
            background: linear-gradient(to right, #E1F5FE, #B3E5FC);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }

        /* 🔹 Dropdowns */
        select.form-select {
            font-size: 1.05rem;
            border-radius: 10px;
            border: 2px solid #0288D1;
            transition: 0.3s;
        }

        select.form-select:hover {
            border-color: #03A9F4;
            background-color: #E0F7FA;
        }

        /* 🔹 Label style */
        label.form-label {
            font-size: 1.1rem;
        }

        /* 🔹 ปุ่ม submit */
        .btn-light {
            background: linear-gradient(to right, #4FC3F7, #81D4FA);
            color: white;
            border: none;
            font-size: 1.1rem;
            border-radius: 12px;
            padding: 10px 30px;
            transition: 0.3s ease-in-out;
        }

        .btn-light:hover {
            background: linear-gradient(to right, #0288D1, #03A9F4);
            transform: scale(1.05);
            color: white;
        }
        .card {
            background: linear-gradient(135deg, #E1F5FE, #B3E5FC);
            border: 2px solid #0288D1;
            border-radius: 16px;
            padding: 20px;
            margin: 20px auto;
            max-width: 500px;
            box-shadow: 0px 6px 16px rgba(0, 0, 0, 0.2);
            transition: 0.3s;
            text-align: center;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.25);
        }

        .card h4 {
            font-weight: bold;
            color: #01579B;
            margin-bottom: 15px;
        }

        .btn-time {
            background: linear-gradient(to right, #4FC3F7, #81D4FA);
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
            padding: 10px 25px;
            border: none;
            border-radius: 12px;
            transition: 0.3s ease-in-out;
        }

        .btn-time:hover {
            background: linear-gradient(to right, #0288D1, #03A9F4);
            transform: scale(1.05);
            color: white;
        }

        .time-card {
            border-radius: 20px;
            background: linear-gradient(to right, #E1F5FE, #B3E5FC);
            transition: all 0.3s ease-in-out;
            border: 2px solid #0288D1;
        }

        .time-card:hover {
            transform: scale(1.03);
            box-shadow: 0px 12px 24px rgba(0, 0, 0, 0.2);
        }
            .confirm-card {
        background: linear-gradient(to right, #E1F5FE, #B3E5FC);
        border: 2px solid #0288D1;
        border-radius: 20px;
        padding: 30px;
        margin-top: 30px;
        box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.15);
        color: #01579B;
        font-size: 1.1rem;
        transition: 0.3s ease-in-out;
    }

    .confirm-card:hover {
        transform: translateY(-5px);
        box-shadow: 0px 12px 30px rgba(0, 0, 0, 0.25);
    }

    .confirm-card h2 {
        font-weight: bold;
        margin-bottom: 25px;
        color: #0277BD;
    }

    .confirm-card .detail {
        margin-bottom: 15px;
        text-align: left;
        font-weight: 500;
        background: #ffffffdd;
        padding: 12px 18px;
        border-radius: 12px;
        border-left: 5px solid #0288D1;
    }

    .confirm-card .btn-confirm {
        margin-top: 25px;
        padding: 12px 25px;
        font-size: 1.1rem;
        background: linear-gradient(to right, #4FC3F7, #0288D1);
        color: white;
        border: none;
        border-radius: 12px;
        transition: 0.3s ease-in-out;
    }

    .confirm-card .btn-confirm:hover {
        background: linear-gradient(to right, #0288D1, #01579B);
        transform: scale(1.05);
    }

    </style>
</head>
<body>
  <!-- 🔹 พื้นที่ข้างๆ (ใส่สีหรือพื้นหลังได้) -->
  <div class="side-space left"></div>
        <div class="side-space right"></div>

        <!-- 🔹 Navbar -->
        <nav class="navbar navbar-expand-lg">
            <div class="container d-flex justify-content-between align-items-center">
                <!-- 🔹 โลโก้ Navbar -->
                <a class="navbar-brand d-flex align-items-center" href="#">
                    <img src="/img/logo.jpg" alt="โลโก้รถบัส" width="40" height="40" class="me-2" style="border-radius: 50%;">
                    <span>ระบบติดตามรถราง</span>
                </a>


                <!-- 🔹 ปุ่มเปลี่ยนภาษา & ชื่อผู้ใช้ -->
                <div class="d-flex align-items-center">
                    <button class="btn btn-light me-2">ENG</button>
                    <button class="btn btn-light me-3">ไทย</button>

                    <span class="username text-white fw-bold me-3">
                        <i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION['username']); ?>
                    </span>

                    <a href="logout.php" class="btn btn-danger">
                        <i class="fa-solid fa-right-from-bracket"></i> ออกจากระบบ
                    </a>
                </div>
            </div>
        </nav>


        <!-- Horizontal Navigation Bar -->
        <div class="nav-menu">
            <div class="container">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link menu-item" href="user_dashboard.php" id="tram-tab">
                            <i class="fa-solid fa-clock"></i> หน้าเเรก
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-item" href="user_dashboard2.php" id="tram-tab">
                            <i class="fa-solid fa-clock"></i> เวลาการเดินรถ
                        </a>
                    </li>
                
                        <li class="nav-item">
                            <a class="nav-link menu-item" href="reservation_route.php" id="tram-tab">
                                <i class="fa-solid fa-clock"></i> จองคิวที่นั่ง
                            </a>
                        </li>
                </ul>
            </div>
        </div>


        <!-- 🔹 Content -->
        <div class="container-fluid">
            <div class="row">
                <main class="col-md-12 content-container">
                    <div id="content">
                        <div class="bg-warning p-4 text-center text-white rounded mt-3">
                            <h2>📢 ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['username']); ?></h2>
                            <p>ตรวจสอบข่าวสาร และตารางเดินรถที่นี่</p>
                        </div>
<div class="container text-center">
    <h2>ยืนยันการจอง</h2>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error_message']; ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="detail"><strong>ชื่อ:</strong> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
        <div class="detail"><strong>อีเมล:</strong> <?= htmlspecialchars($user['email']) ?></div>
        <div class="detail"><strong>ต้นทาง:</strong> <?= htmlspecialchars($start) ?></div>
        <div class="detail"><strong>ปลายทาง:</strong> <?= htmlspecialchars($end) ?></div>
        <div class="detail"><strong>ช่วงเวลา:</strong> <?= htmlspecialchars($period) ?></div>
        <div class="detail"><strong>เวลา:</strong> <?= htmlspecialchars($time) ?> น.</div>
        <div class="detail"><strong>ที่นั่ง:</strong> <?= htmlspecialchars($seat_no) ?></div>
        <div class="detail"><strong>วันที่:</strong> <?= $today ?></div>

        <form action="reservation_save.php" method="POST">
            <input type="hidden" name="start" value="<?= htmlspecialchars($start) ?>">
            <input type="hidden" name="end" value="<?= htmlspecialchars($end) ?>">
            <input type="hidden" name="period" value="<?= htmlspecialchars($period) ?>">
            <input type="hidden" name="time" value="<?= htmlspecialchars($time) ?>">
            <input type="hidden" name="seat_no" value="<?= htmlspecialchars($seat_no) ?>">
            <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
            <button type="submit" class="btn btn-success btn-confirm">ยืนยันการจอง</button>
        </form>
    </div>
</div>
</body>
</html>
