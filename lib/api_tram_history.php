<?php
include 'db.php';

header('Content-Type: application/json');

if (!isset($_GET['tram_number'])) {
    echo json_encode(["error" => "กรุณาระบุหมายเลขรถราง"]);
    exit();
}

$tram_number = $_GET['tram_number'];

try {
    $stmt = $conn->prepare("SELECT latitude, longitude, recorded_at FROM tram_logs WHERE tram_number = :tram_number ORDER BY recorded_at ASC");
    $stmt->bindParam(':tram_number', $tram_number);
    $stmt->execute();
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($history);
} catch (PDOException $e) {
    echo json_encode(["error" => "เกิดข้อผิดพลาด: " . $e->getMessage()]);
}
?>
