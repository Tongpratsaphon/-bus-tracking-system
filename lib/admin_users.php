<?php
include 'db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

try {
    $stmt = $conn->query("SELECT * FROM users ORDER BY id ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #fff;
        }
        .container {
            background-color: #1e1e1e;
            padding: 30px;
            border-radius: 10px;
            margin-top: 40px;
        }
        h2 {
            color: #00ff88;
            margin-bottom: 30px;
        }
        .btn-primary {
            background-color: #00c46f;
            border: none;
        }
        .btn-primary:hover {
            background-color: #00a35a;
        }
        .btn-warning {
            background-color: #ffc107;
            border: none;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }
        table {
            background-color: #222;
        }
        thead.table-dark th {
            background-color: #333;
            color: #00ff88;
        }
        .alert {
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">👥 จัดการผู้ใช้</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <table class="table table-bordered text-center table-hover">
            <thead>
                <tr>
                    <th>ชื่อผู้ใช้</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>เบอร์โทร</th>
                    <th>อีเมล</th>
                    <th>Role</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                    <td><?= htmlspecialchars($user['phone']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <a href="user_edit.php?id=<?= $user['id'] ?>" class="btn btn-warning btn-sm">✏️ แก้ไข</a>
                        <form action="user_delete.php" method="POST" class="d-inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้นี้?');">
                            <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">❌ ลบ</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
