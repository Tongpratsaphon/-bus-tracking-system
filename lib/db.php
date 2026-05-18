<?php
$host = "localhost"; // หรือใส่ IP ของเซิร์ฟเวอร์
$dbname = "tram_tracking"; // ชื่อฐานข้อมูล
$user = "postgres"; // ชื่อผู้ใช้ของ PostgreSQL
$password = "1234"; // รหัสผ่านของ PostgreSQL

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // เปิดโหมดแจ้งข้อผิดพลาด
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // ตั้งค่าการดึงข้อมูลให้ง่ายขึ้น
        PDO::ATTR_EMULATE_PREPARES => false // ป้องกัน SQL Injection
    ]);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage()); // บันทึกข้อผิดพลาดลงไฟล์ log
    die("❌ ไม่สามารถเชื่อมต่อฐานข้อมูลได้ โปรดติดต่อผู้ดูแลระบบ"); // ไม่แสดงรายละเอียดให้ผู้ใช้เห็น
}
?>
