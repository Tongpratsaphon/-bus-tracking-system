<?php
include 'db.php'; // เชื่อมต่อฐานข้อมูล

header('Content-Type: text/html; charset=UTF-8');

try {
    $stmt = $conn->query("SELECT tram_number, current_latitude, current_longitude, status FROM trams");
    $trams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แผนที่รถราง</title>

    <!-- เรียกใช้ Leaflet.js -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        #map { height: 500px; width: 100%; }
    </style>
</head>
<body>
    <h2>แผนที่แสดงตำแหน่งรถราง</h2>

    <label for="tramSelector">เลือกหมายเลขรถราง:</label>
    <select id="tramSelector">
        <?php foreach ($trams as $tram): ?>
            <option value="<?= $tram['tram_number'] ?>"><?= $tram['tram_number'] ?></option>
        <?php endforeach; ?>
    </select>
    <button onclick="showTramHistory()">📍 ดูเส้นทางย้อนหลัง</button>

    <div id="map"></div>

    <script>
        // สร้างแผนที่ Leaflet
        var map = L.map('map').setView([13.736717, 100.523186], 13);

        // เพิ่มแผนที่จาก OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var tramMarkers = {}; // เก็บ marker ของแต่ละรถราง
        var tramPath = null; // เก็บเส้นทางย้อนหลังของรถราง

        // ✅ โหลดหมุดเริ่มต้นจาก PHP เพื่อให้หมุดแสดงทันที
        var tramData = <?= json_encode($trams); ?>;
        tramData.forEach(tram => {
            var lat = parseFloat(tram.current_latitude);
            var lon = parseFloat(tram.current_longitude);
            var tramId = tram.tram_number;

            tramMarkers[tramId] = L.marker([lat, lon])
                .addTo(map)
                .bindPopup(`<b>รถราง: ${tram.tram_number}</b><br>
                            สถานะ: ${tram.status}<br>
                            <a href="tram_detail.php?tram_number=${tram.tram_number}" target="_blank">📄 ดูรายละเอียด</a>`);
        });

        // ✅ ฟังก์ชันโหลดข้อมูลรถรางแบบ AJAX
        function loadTramData() {
            fetch('api_trams.php') // เรียก API
                .then(response => response.json())
                .then(data => {
                    console.log("📌 อัปเดตข้อมูล:", data);

                    data.forEach(tram => {
                        var lat = parseFloat(tram.current_latitude);
                        var lon = parseFloat(tram.current_longitude);
                        var tramId = tram.tram_number;

                        if (tramMarkers[tramId]) {
                            tramMarkers[tramId].setLatLng([lat, lon]);
                        } else {
                            tramMarkers[tramId] = L.marker([lat, lon])
                                .addTo(map)
                                .bindPopup(`<b>รถราง: ${tram.tram_number}</b><br>
                                            สถานะ: ${tram.status}<br>
                                            <a href="tram_detail.php?tram_number=${tram.tram_number}" target="_blank">📄 ดูรายละเอียด</a>`);
                        }
                    });
                })
                .catch(error => console.error("🚨 เกิดข้อผิดพลาดในการโหลดข้อมูล:", error));
        }

        // ✅ โหลดข้อมูลทุก 5 วินาที
        setInterval(loadTramData, 5000);

        // ✅ ฟังก์ชันแสดงเส้นทางย้อนหลังของรถราง
        function showTramHistory() {
            var tramNumber = document.getElementById('tramSelector').value;
            fetch(`api_tram_history.php?tram_number=${tramNumber}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    let latlngs = data.map(point => [parseFloat(point.latitude), parseFloat(point.longitude)]);

                    // ลบเส้นเก่าถ้ามี
                    if (tramPath) {
                        map.removeLayer(tramPath);
                    }

                    // วาดเส้นเส้นทาง
                    tramPath = L.polyline(latlngs, {color: 'blue'}).addTo(map);
                    map.fitBounds(tramPath.getBounds());
                })
                .catch(error => console.error("🚨 เกิดข้อผิดพลาดในการโหลดประวัติเดินทาง:", error));
        }
    </script>

</body>
</html>
