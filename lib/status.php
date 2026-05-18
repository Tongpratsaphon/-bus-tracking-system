<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "ผู้ใช้ทั่วไป";

try {
    include 'db.php';

    $stmt_news = $conn->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 5");
    $news = $stmt_news->fetchAll(PDO::FETCH_ASSOC);

    $stmt_trams = $conn->query("SELECT tram_number, status, expected_arrival FROM trams ORDER BY tram_number");
    $trams = $stmt_trams->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $news = [];
    $trams = [];
    error_log("Database error: " . $e->getMessage());
}

$stmt = $conn->prepare("SELECT bus_stop_name, status FROM bus_stops");
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ลำดับที่ต้องการให้แสดง
$order = ['หน้าตึกB', 'หอใน', 'แยกปะทิวทอง', 'หอหรูอพาร์ทเมนต์'];

// จัดเรียงข้อมูลใหม่ตาม $order
$orderedData = [];
foreach ($order as $name) {
    foreach ($data as $stop) {
        if (trim($stop['bus_stop_name']) === $name) {
            $orderedData[] = $stop;
            break;
        }
    }
}

// แยกข้อมูลจุดที่ status = true และ false
$visited = array_filter($orderedData, fn($s) => $s['status']);
$upcoming = array_filter($orderedData, fn($s) => !$s['status']);

// จุดล่าสุดคือตัวสุดท้ายของที่ไปแล้ว
$lastStop = end($visited);
// จุดถัดไปคือตัวแรกของที่ยังไม่ถึง
$nextStop = reset($upcoming);
?>

<!DOCTYPE html>

    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>สถานะรถปัจจุบัน</title>
        <link rel="icon" type="image/jpg" href="/img/logo.jpg">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
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
                
                    <li class="nav-item">
                        <a class="nav-link menu-item" href="status.php" id="tram-tab">
                            <i class="fa-solid fa-clock"></i> สถานะรถปัจจุบัน
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

                    <!-- 🔹 Row หลัก แบ่งซ้าย-ขวา -->
                    <div class="row mt-4">
                        
                        <!-- 🔹 คอลัมน์ซ้าย (ข้อมูลเพิ่มเติม + ข่าวสารล่าสุด) -->
                        <div class="col-md-7 d-flex flex-column gap-3">
                    </div>
                    
            
            <div class="card card-custom p-4 mx-auto" style="max-width: 600px; width: 100%;">
            <h2 class="text-center text-primary mb-4">
                <i class="fa-solid fa-bus fa-fw"></i> สถานะรถปัจจุบัน
            </h2>

            <div class="row mb-4">
                <div class="col-md-6 text-center">
                    <p class="fs-5">
                        <i class="fa-solid fa-location-dot text-success me-2"></i>
                        <strong>จุดรับส่งล่าสุด:</strong><br>
                        <span class="badge bg-success fs-6"><?= $lastStop['bus_stop_name'] ?? '-' ?></span>
                    </p>
                </div>
                <div class="col-md-6 text-center">
                    <p class="fs-5">
                        <i class="fa-solid fa-arrow-right text-warning me-2"></i>
                        <strong>จุดจอดถัดไป:</strong><br>
                        <span class="badge bg-warning text-dark fs-6"><?= $nextStop['bus_stop_name'] ?? '-' ?></span>
                    </p>
                </div>
            </div>

            <h4 class="text-center mt-4 mb-3">🚏 ประวัติการผ่านจุดจอด</h4>

            <ul class="list-group list-group-flush">
                <?php foreach ($orderedData as $stop): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($stop['bus_stop_name']) ?>
                        <span class="badge <?= $stop['status'] ? 'bg-success' : 'bg-danger' ?>">
                            <?= $stop['status'] ? '✔ ผ่านแล้ว' : '❌ ยังไม่ถึง' ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
```

<script>
            setInterval(function() {
        window.location.reload();
    }, 5000); // รีเฟรชทุก 5000 มิลลิวินาที = 5 วินาที

</script>    

</div>
</body>
</html> 