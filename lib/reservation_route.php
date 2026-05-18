<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "ผู้ใช้ทั่วไป";

$locations = ['ตึกB', 'หอใน', 'ประทิวทอง', 'หอหรู'];
$periods = ['morning' => 'เช้า', 'noon' => 'กลางวัน', 'evening' => 'เย็น'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เลือกเส้นทาง</title>
    <link rel="icon" type="image/jpg" href="/img/logo.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
</head>
<body>
<div class="content-container">
    <h2 class="text-center mb-4 fw-bold text-primary">🚋 เลือกเส้นทางเดินรถ</h2>
    <form action="reservation_time.php" method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="start" class="form-label fw-semibold text-primary">ต้นทาง:</label>
                <select class="form-select shadow-sm" name="start" id="start" required>
                    <option value="">-- เลือกต้นทาง --</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= $loc ?>"><?= $loc ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="end" class="form-label fw-semibold text-primary">ปลายทาง:</label>
                <select class="form-select shadow-sm" name="end" id="end" required>
                    <option value="">-- เลือกปลายทาง --</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= $loc ?>"><?= $loc ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label for="period" class="form-label fw-semibold text-primary">ช่วงเวลา:</label>
            <select class="form-select shadow-sm" name="period" id="period" required>
                <option value="">-- เลือกช่วงเวลา --</option>
                <?php foreach ($periods as $key => $label): ?>
                    <option value="<?= $key ?>"><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-light px-4 py-2 fw-bold shadow-sm">
                ถัดไป <i class="fa-solid fa-arrow-right ms-1"></i>
            </button>
        </div>
    </form>
</div>
</body>
</html>
