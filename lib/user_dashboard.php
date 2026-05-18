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
        ?>

        <!DOCTYPE html>
        <html lang="th">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>หน้าหลักผู้ใช้งาน</title>
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
                                
                                <!-- 🔹 กรอบข้อมูลเพิ่มเติม -->
                                <div class="text-container p-3">
                                    <h5 class="text-primary"><i class="fa-solid fa-info-circle"></i> ข้อมูลเกี่ยวกับรถราง</h5>
                                    <p>    ในสังคมเมืองที่การเดินทางมีความสำคัญอย่างยิ่ง "รถรางภายในมหาวิทยาลัย" ถือเป็นโครงสร้างพื้นฐานที่ช่วยอำนวยความสะดวกให้กับนักศึกษา อาจารย์ และบุคลากรในการเดินทางระหว่างอาคารเรียน หอพัก ศูนย์อาหาร และสถานที่สำคัญต่างๆ ภายในมหาวิทยาลัย โดยเฉพาะอย่างยิ่งสำหรับนักศึกษาที่ไม่มีรถส่วนตัว การมีระบบขนส่งที่มีประสิทธิภาพสามารถช่วยลดภาระค่าใช้จ่ายและเพิ่มความสะดวกสบายในการเดินทางได้</p>
                                
                                </div>

                                <!-- 🔹 ข่าวสารล่าสุด -->
                                <div class="card card-custom">
                                    <div class="card-header bg-primary text-white">
                                        <h5><i class="fa-solid fa-bullhorn"></i> ข่าวสารล่าสุด</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="accordion" id="newsAccordion">
                                            <?php foreach ($news as $index => $item): ?>
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="heading<?= $index; ?>">
                                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index; ?>">
                                                            <?= htmlspecialchars($item['title']); ?>
                                                        </button>
                                                    </h2>
                                                    <div id="collapse<?= $index; ?>" class="accordion-collapse collapse" data-bs-parent="#newsAccordion">
                                                        <div class="accordion-body">
                                                            <?= nl2br(htmlspecialchars($item['content'])); ?>
                                                            <br><small class="text-muted"><i class="fa-solid fa-calendar"></i> <?= $item['created_at']; ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <!-- 🔹 คอลัมน์ขวา (ตารางเวลาการเดินรถ) -->
        <div class="col-md-5">
            <div class="carousel-container">
                <div class="card card-custom">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fa-solid fa-images"></i> รูปประชาสัมพันธ์</h5>
                    </div>
                    <div class="card-body p-3">
                        <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                        <div class="carousel-inner">
                            <?php
                            $imageDir = __DIR__ . '/image'; // path จริงใน server
                            $imageWebPath = '/bus1/image'; // path สำหรับแสดงใน <img src=...>

                            $images = glob($imageDir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
                            if (!empty($images)):
                                foreach ($images as $index => $imagePath):
                                    $imageName = basename($imagePath);
                                    $active = ($index === 0) ? 'active' : '';
                            ?>
                                <div class="carousel-item <?= $active ?>">
                                    <img src="<?= $imageWebPath . '/' . $imageName ?>" class="d-block w-100">
                                </div>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <div class="carousel-item active">
                                    <img src="/bus1/image/default.jpg" class="d-block w-100" alt="No image found">
                                </div>
                            <?php endif; ?>
                        </div>

                            <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


                        </div> <!-- 🔹 จบแถว -->
                    </div>
                </main>
            </div>
        </div>

            


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
