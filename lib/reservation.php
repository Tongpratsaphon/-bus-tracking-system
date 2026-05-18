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
    $today = date('Y-m-d'); // ใช้วันที่ปัจจุบันจริง

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
    <link rel="icon" type="image/jpg" href="/img/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
    <h2>จองที่นั่ง</h2>
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
        <input type="hidden" name="selected_seat" id="selected_seat" value="">
        <input type="hidden" name="start" value="<?= htmlspecialchars($start) ?>">
        <input type="hidden" name="end" value="<?= htmlspecialchars($end) ?>">
        <input type="hidden" name="period" value="<?= htmlspecialchars($period) ?>">
        <input type="hidden" name="time" value="<?= htmlspecialchars($time) ?>">

        <div class="action-button">
            <button type="submit" class="btn btn-success btn-reserve">เลือกที่นั่ง</button>
        </div>
    </form>
    <div class="legend">
        <span class="vacant">ว่าง</span>
        <span class="selected">ที่นั่ง</span>
        <span class="driver">คนขับรถ</span>
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
</script>
</body>
</html>
