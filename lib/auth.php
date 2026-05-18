<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    if ($action == "login") {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            echo "<span class='text-success'>✅ เข้าสู่ระบบสำเร็จ! กำลังนำทาง...</span>";
        } else {
            echo "<span class='text-danger'>❌ ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง!</span>";
        }
    }

    if ($action == "register") {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];

        if ($password !== $password_confirm) {
            echo "<span class='text-danger'>❌ รหัสผ่านไม่ตรงกัน!</span>";
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
        if ($stmt->execute([$username, $hashed_password])) {
            echo "<span class='text-success'>✅ สมัครสมาชิกสำเร็จ! กำลังเปลี่ยนไปหน้าเข้าสู่ระบบ...</span>";
        } else {
            echo "<span class='text-danger'>❌ มีข้อผิดพลาด กรุณาลองอีกครั้ง!</span>";
        }
    }
}
?>
