<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];

    try {
        $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, phone, role) 
                                VALUES (:username, :password, :first_name, :last_name, :phone, 'user')");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        echo "<p class='text-success text-center'>✅ สมัครสมาชิกสำเร็จ! <a href='login.php'>เข้าสู่ระบบ</a></p>";
    } catch (PDOException $e) {
        echo "<p class='text-danger text-center'>❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h2 class="text-center">📝 สมัครสมาชิก</h2>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">ชื่อ:</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">นามสกุล:</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">เบอร์โทรศัพท์:</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ชื่อผู้ใช้:</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">รหัสผ่าน:</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">➡️ สมัครสมาชิก</button>
                        </form>
                        <p class="text-center mt-3">มีบัญชีแล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
