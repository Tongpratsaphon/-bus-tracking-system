<?php
session_start();
include 'db.php';

$error = '';

// ตรวจสอบการเข้าสู่ระบบ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php?page=tkadmindash");
        } else {
            header("Location: user_dashboard.php");
        }
        exit();
    } else {
        $error = "อีเมลหรือรหัสผ่านไม่ถูกต้อง!";
    }
}

// ตรวจสอบการสมัครสมาชิก
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $_POST['reg_username'];
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    if ($password !== $confirm_password) {
        $error = "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน!";
    }  else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "ชื่อผู้ใช้นี้ถูกใช้ไปแล้ว!";
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "อีเมลนี้ถูกใช้งานไปแล้ว กรุณาใช้อีเมลอื่น!";
            } else {
                $password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, phone, email, role) VALUES (?, ?, ?, ?, ?, ?, 'user')");
                $stmt->execute([$username, $password, $first_name, $last_name, $phone, $email]);
                header("Location: login.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background: linear-gradient(to right, #667eea, #764ba2);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            max-width: 1200px;
            flex-wrap: wrap;
        }
        .welcome-text {
            color: white;
            font-size: 2.5rem;
            font-weight: 600;
            text-align: center;
            line-height: 1.3;
            flex: 1 1 300px;
            margin-bottom: 20px;
        }
        .form-container {
            max-width: 600px;
            width: 100%;
            padding: 30px;
            border-radius: 12px;
            background: white;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.6s ease-in-out;
            font-size: 0.95rem;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .nav-tabs .nav-link {
            color: #555;
            font-weight: bold;
        }
        .nav-tabs .nav-link.active {
            background: #667eea;
            color: white;
            border-radius: 8px;
        }
        .btn-custom {
            background: #667eea;
            border: none;
            transition: 0.3s;
            font-weight: 600;
        }
        .btn-custom:hover {
            background: #564aa3;
        }
        .form-control {
            font-size: 0.9rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.5);
        }
        .error-msg {
            color: red;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-text">Welcome to <br> PCC Bus Tracking</div>
        <div class="form-container">
            <ul class="nav nav-tabs justify-content-center" id="authTabs">
                <li class="nav-item">
                    <a class="nav-link active" id="login-tab" data-bs-toggle="tab" href="#login">เข้าสู่ระบบ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="register-tab" data-bs-toggle="tab" href="#register">สมัครสมาชิก</a>
                </li>
            </ul>
            <div class="tab-content mt-3">
                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?= $error ?></div>
                <?php endif; ?>

                <!-- ฟอร์มเข้าสู่ระบบ -->
                <div id="login" class="tab-pane fade show active">
                    <h4 class="text-center text-primary">เข้าสู่ระบบ</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <label>อีเมล</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>รหัสผ่าน</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-custom w-100">เข้าสู่ระบบ</button>
                    </form>
                </div>

                <!-- ฟอร์มสมัครสมาชิก -->
                <div id="register" class="tab-pane fade">
                    <h4 class="text-center text-success">สมัครสมาชิก</h4>
                    <form method="POST" id="registerForm">
                        <div class="mb-3">
                            <label>ชื่อ</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>นามสกุล</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>ชื่อผู้ใช้</label>
                            <input type="text" name="reg_username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>อีเมล (เช่น 64200123@kmitl.ac.th)</label>
                            <input type="email" name="email" class="form-control" required pattern="[0-9]{8}@kmitl\.ac\.th" title="กรุณาใส่อีเมลตามรูปแบบ 8 ตัวเลขตามด้วย @kmitl.ac.th">
                        </div>
                        <div class="mb-3">
                            <label>รหัสผ่าน</label>
                            <input type="password" name="reg_password" id="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>ยืนยันรหัสผ่าน</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            <small id="password_error" class="text-danger"></small>
                        </div>
                        <button type="submit" name="register" class="btn btn-success w-100" id="registerBtn" disabled>สมัครสมาชิก</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JS ตรวจสอบรหัสผ่านตรงกัน -->
    <script>
        $(document).ready(function () {
            $("#confirm_password, #password").on("keyup", function () {
                let password = $("#password").val();
                let confirmPassword = $("#confirm_password").val();
                if (password === confirmPassword && password.length > 0) {
                    $("#password_error").text("");
                    $("#registerBtn").prop("disabled", false);
                } else {
                    $("#password_error").text("รหัสผ่านไม่ตรงกัน!");
                    $("#registerBtn").prop("disabled", true);
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
