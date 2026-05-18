<?php
$json = file_get_contents('https://pccbustracking-production.up.railway.app/passenger-logs');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    echo "Error loading data.";
    exit;
}

$range = $_GET['range'] ?? 'today';
$timezone = new DateTimeZone('Asia/Bangkok');
$now = new DateTime('now', $timezone);
$startDate = clone $now;

switch ($range) {
    case '7d':
        $startDate->modify('-6 days');
        break;
    case '30d':
        $startDate->modify('-29 days');
        break;
    case 'today':
    default:
        $startDate = (new DateTime('now', $timezone))->setTime(0, 0);
        break;
}

$labels = [];
$counts = [];

if ($range === 'today') {
    $entries_today = [];
    foreach ($data as $entry) {
        if (!isset($entry['timestamp'], $entry['passenger_count'])) continue;
        $entryTime = new DateTime($entry['timestamp'], new DateTimeZone('UTC'));
        $entryTime->setTimezone($timezone);
        if ($entryTime->format('Y-m-d') === $now->format('Y-m-d')) {
            $timeLabel = $entryTime->format('H:i');
            if (!isset($entries_today[$timeLabel])) {
                $entries_today[$timeLabel] = 0;
            }
            $entries_today[$timeLabel] += $entry['passenger_count'];
        }
    }
    ksort($entries_today);
    $labels = array_keys($entries_today);
    $counts = array_values($entries_today);
} else {
    $daily = [];
    foreach ($data as $entry) {
        if (!isset($entry['timestamp'], $entry['passenger_count'])) continue;
        $time = new DateTime($entry['timestamp'], new DateTimeZone('UTC'));
        $time->setTimezone($timezone);
        if ($time < $startDate || $time > $now) continue;
        $key = $time->format('Y-m-d');
        $daily[$key] = ($daily[$key] ?? 0) + $entry['passenger_count'];
    }

    $period = new DatePeriod($startDate, new DateInterval('P1D'), (clone $now)->modify('+1 day'));
    foreach ($period as $date) {
        $dateStr = $date->format('Y-m-d');
        $labels[] = $dateStr;
        $counts[] = $daily[$dateStr] ?? 0;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แดชบอร์ดผู้โดยสารย้อนหลัง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #121212;
            color: #e0ffe0;
            font-family: 'Poppins', sans-serif;
        }
        h2, h5 { color: #56f78f; }
        .card {
            background-color: #1e1e1e;
            border: 1px solid #28a74533;
            border-radius: 15px;
            box-shadow: 0 0 15px #28a74533;
            transition: transform 0.2s;
        }
        .card:hover { transform: scale(1.02); }
        .card h4 { color: #28f77a; }
        .btn-success {
            background-color: #28a745;
            border: none;
            font-weight: bold;
        }
        .btn-success:hover { background-color: #1e7e34; }
        canvas {
            background-color: #181818;
            border-radius: 10px;
        }
        .chart-container {
            padding: 20px;
            background-color: #1e1e1e;
            border-radius: 15px;
        }
        .toggle-btns .btn {
            margin: 5px;
            border: 1px solid #28a74599;
            color: #aaffaa;
        }
        .toggle-btns .btn.active {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <h2 class="text-center mb-5">แดชบอร์ดจำนวนผู้โดยสารย้อนหลัง</h2>

    <div class="text-center toggle-btns mb-4">
        <a href="admin_dashboard.php?page=dvdash&range=today" class="btn btn-outline-success <?= $range === 'today' ? 'active' : '' ?>">วันนี้</a>
        <a href="admin_dashboard.php?page=dvdash&range=7d" class="btn btn-outline-success <?= $range === '7d' ? 'active' : '' ?>">7 วันล่าสุด</a>
        <a href="admin_dashboard.php?page=dvdash&range=30d" class="btn btn-outline-success <?= $range === '30d' ? 'active' : '' ?>">30 วันล่าสุด</a>
    </div>

    <div class="chart-container mb-4">
        <h5 class="text-center mb-4">จำนวนผู้โดยสารช่วงเวลา <?= $range === 'today' ? 'วันนี้' : ($range === '7d' ? '7 วันล่าสุด' : '30 วันล่าสุด') ?></h5>
        <canvas id="passengerChart" height="100"></canvas>
    </div>

    <div class="text-center mt-4">
        <a href="admin_dashboard.php?page=tkpassenger" class="btn btn-success px-4 py-2">ดูข้อมูลทั้งหมด</a>
    </div>
</div>

<script>
    const ctx = document.getElementById('passengerChart').getContext('2d');
    const passengerChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels); ?>,
            datasets: [{
                label: 'จำนวนผู้โดยสาร',
                data: <?= json_encode($counts); ?>,
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                borderColor: '#28a745',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#28a745'
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    ticks: { color: '#aaffaa' },
                    grid: { color: '#333' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#aaffaa', stepSize: 1 },
                    grid: { color: '#333' }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#aaffaa'
                    }
                }
            }
        }
    });

    setInterval(function() {
        window.location.reload();
    }, 5000); // รีเฟรชทุก 5000 มิลลิวินาที = 5 วินาที

</script>

</body>
</html>