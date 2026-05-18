<?php
include 'db.php'; // เชื่อมต่อฐานข้อมูล

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Pragma: no-cache');

try {
    $stmt = $conn->query("SELECT tram_number, current_latitude, current_longitude, status FROM trams");
    $trams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($trams === false) {
        echo json_encode(["error" => "ไม่พบข้อมูลรถราง"]);
        exit;
    }

    echo json_encode($trams, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}
?>
