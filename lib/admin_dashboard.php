<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include 'db.php';

// กำหนดหน้าเริ่มต้น (default page)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แดชบอร์ดแอดมิน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #121212;
            color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            height: 100vh;
            background: #1f1f1f;
            color: white;
            position: fixed;
            width: 250px;
            padding-top: 20px;
            border-right: 1px solid #2c2c2c;
        }
        .sidebar a {
            color: #cfcfcf;
            padding: 12px 20px;
            display: block;
            text-decoration: none;
            transition: background 0.2s;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background: #198754;
            color: #fff;
            border-radius: 5px;
        }
        .content-area {
            background: #121212;
            margin-left: 260px;
            padding: 25px;
            width: calc(100% - 260px);
        }
        .navbar {
            background-color: #1c1c1c !important;
            border-bottom: 1px solid #2c2c2c;
        }
        .navbar-brand {
            font-weight: bold;
            color: #28a745 !important;
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark px-3">
        <a class="navbar-brand" href="#">🚋 ระบบติดตามรถราง</a>
        <div class="d-flex">
            <span class="navbar-text text-white me-3">👋 ยินดีต้อนรับ, <b><?= htmlspecialchars($_SESSION['username']); ?></b></span>
            <a href="logout.php" class="btn btn-danger">🚪 ออกจากระบบ</a>
        </div>
    </nav>

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar p-3">
            <h4 class="text-center text-success">📊 เมนูแอดมิน</h4>
            <hr style="border-color: #444;">
            <a href="admin_dashboard.php?page=tkadmindash" class="menu-link <?= ($page == 'tkadmindash') ? 'active' : ''; ?>">📊 แดชบอร์ดตารางจอง</a>
            <a href="admin_dashboard.php?page=dvdash" class="menu-link <?= ($page == 'dvdash') ? 'active' : ''; ?>">📊 แดชบอร์ดจำนวนคนขึ้น</a>
            <a href="admin_dashboard.php?page=news" class="menu-link <?= ($page == 'news') ? 'active' : ''; ?>">📰 จัดการข่าวสาร</a>
            <a href="admin_dashboard.php?page=users" class="menu-link <?= ($page == 'users') ? 'active' : ''; ?>">👥 จัดการผู้ใช้</a>
            <a href="admin_dashboard.php?page=tkadmin" class="menu-link <?= ($page == 'tkadmin') ? 'active' : ''; ?>">🚌 การจองรถ</a>
            <a href="admin_dashboard.php?page=images" class="menu-link <?= ($page == 'images') ? 'active' : ''; ?>">🖼️ จัดการรูปภาพ</a>
            <a href="admin_dashboard.php?page=schedule" class="menu-link <?= ($page == 'schedule') ? 'active' : ''; ?>">🕒 จัดการตารางเวลา</a>
        </div>

        <!-- Content -->
        <div class="content-area">
            <!-- แสดงแจ้งเตือนถ้ามี -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success'; ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <div id="content-area">
                <?php
                // โหลดเนื้อหาตาม page ที่เลือก
                if ($page == "tkadmindash") {
                    include "tkadmindash.php";
                } elseif ($page == "news") {
                    include "admin_news.php";
                } elseif ($page == "users") {
                    include "admin_users.php";
                } elseif ($page == "tkadmin") {
                    include "tkadmin.php";
                } elseif ($page == "images") {
                    include "admin_images.php"; 
                } elseif ($page == "schedule") {
                    include "admin_schedule.php"; 
                } elseif ($page == "dvdash") {
                    include "dvdash.php";               
                } else {
                
                    echo "<p class='text-center text-danger'>ไม่พบหน้าที่ร้องขอ</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
