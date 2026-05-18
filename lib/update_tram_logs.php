<?php
include 'db.php';

// อัปเดตพิกัดรถรางและบันทึกลง `tram_logs`
try {
    $stmt = $conn->query("SELECT tram_number, current_latitude, current_longitude FROM trams");
    $trams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($trams as $tram) {
        $tram_number = $tram['tram_number'];
        $latitude = $tram['current_latitude'];
        $longitude = $tram['current_longitude'];

        // บันทึกพิกัดลง `tram_logs`
        $logStmt = $conn->prepare("INSERT INTO tram_logs (tram_number, latitude, longitude) VALUES (:tram_number, :latitude, :longitude)");
        $logStmt->bindParam(':tram_number', $tram_number);
        $logStmt->bindParam(':latitude', $latitude);
        $logStmt->bindParam(':longitude', $longitude);
        $logStmt->execute();
    }

    echo "✅ บันทึกพิกัดสำเร็จ!";
} catch (PDOException $e) {
    echo "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>
