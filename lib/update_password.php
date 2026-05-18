<?php
include 'db.php';

// เข้ารหัสรหัสผ่านใหม่ (ใช้ password_hash)
$newAdminPass = password_hash('admin123', PASSWORD_DEFAULT);
$newUserPass = password_hash('user123', PASSWORD_DEFAULT);

$conn->query("UPDATE users SET password = '$newAdminPass' WHERE username = 'admin'");
$conn->query("UPDATE users SET password = '$newUserPass' WHERE username = 'user1'");

echo "✅ อัปเดตรหัสผ่านเรียบร้อย!";
?>
