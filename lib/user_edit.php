<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "❌ ไม่พบผู้ใช้ที่ต้องการแก้ไข";
    header("Location: admin_dashboard.php?page=users");
    exit();
}

$id = $_GET['id'];

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = "❌ ไม่พบผู้ใช้";
        header("Location: admin_dashboard.php?page=users");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
    header("Location: admin_dashboard.php?page=users");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);

    try {
        // ตรวจสอบว่าอีเมลซ้ำกับผู้ใช้อื่นหรือไม่
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "❌ อีเมลนี้ถูกใช้งานแล้วโดยผู้ใช้อื่น";
            header("Location: admin_dashboard.php?page=users");
            exit();
        }

        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = :username, email = :email, first_name = :first_name, last_name = :last_name, phone = :phone, role = :role, password = :password WHERE id = :id");
            $stmt->bindParam(':password', $hashedPassword);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = :username, email = :email, first_name = :first_name, last_name = :last_name, phone = :phone, role = :role WHERE id = :id");
        }

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $_SESSION['success'] = "✅ ข้อมูลผู้ใช้ถูกอัปเดตเรียบร้อยแล้ว!";
        header("Location: admin_dashboard.php?page=users");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
        header("Location: admin_dashboard.php?page=users");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลผู้ใช้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">✏️ แก้ไขข้อมูลผู้ใช้</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">ชื่อผู้ใช้:</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">อีเมล:</label>
                <input type="text" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">ชื่อ:</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">นามสกุล:</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">เบอร์โทรศัพท์:</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">บทบาท (Role):</label>
                <select name="role" class="form-control">
                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">รหัสผ่านใหม่ (หากต้องการเปลี่ยน):</label>
                <input type="password" name="password" class="form-control">
            </div>
            <button type="submit" name="submit" class="btn btn-success w-100">💾 บันทึกการเปลี่ยนแปลง</button>
        </form>
        <br>
        <a href="admin_dashboard.php?page=users" class="btn btn-secondary">🔙 กลับไปหน้าจัดการผู้ใช้</a>
    </div>
</body>
</html>
