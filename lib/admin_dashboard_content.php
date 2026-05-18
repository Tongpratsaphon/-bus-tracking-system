<?php
include 'db.php';

// ดึงข้อมูลระยะทางรวมของรถราง
$stmt = $conn->query("SELECT tram_number, COALESCE(SUM(distance_traveled), 0) AS total_distance FROM tram_logs GROUP BY tram_number ORDER BY tram_number");
$tram_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tram_labels = [];
$tram_distances = [];

foreach ($tram_data as $tram) {
    $tram_labels[] = $tram['tram_number'];
    $tram_distances[] = $tram['total_distance'];
}
?>

<h2 class="mt-4">📊 แดชบอร์ดแอดมิน</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card bg-primary text-white p-3">
            <h5>🚋 จำนวนรถรางทั้งหมด</h5>
            <h2 class="fw-bold"><?= count($tram_labels); ?> คัน</h2>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card bg-warning text-white p-3">
            <h5>📍 ระยะทางเฉลี่ย</h5>
            <h2 class="fw-bold">
                <?= round(array_sum($tram_distances) / (count($tram_labels) ?: 1), 2); ?> กม.
            </h2>
        </div>
    </div>
</div>

<div class="mt-4">
    <h3>📊 สถิติระยะทางรถราง</h3>
    <canvas id="tramChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('tramChart').getContext('2d');
    var tramChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($tram_labels); ?>,
            datasets: [{
                label: 'ระยะทางรวม (กม.)',
                data: <?= json_encode($tram_distances); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
