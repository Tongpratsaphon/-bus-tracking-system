<?php

include 'db.php';

// ฟังก์ชันนับการจองภายในช่วงวัน
function getBookingCount($days) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM reservations WHERE reservation_date >= CURRENT_DATE - INTERVAL '$days days'");
    $stmt->execute();
    return $stmt->fetchColumn();
}

// สถิติรวม
$todayCount = getBookingCount(1);
$weekCount = getBookingCount(7);
$monthCount = getBookingCount(30);

// สร้างข้อมูลสำหรับกราฟ
function getChartData($days) {
    global $conn;
    $labels = [];
    $counts = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $stmt = $conn->prepare("SELECT COUNT(*) FROM reservations WHERE reservation_date = :date");
        $stmt->execute(['date' => $date]);
        $labels[] = date('d M', strtotime($date));
        $counts[] = $stmt->fetchColumn();
    }
    return ['labels' => $labels, 'counts' => $counts];
}

$chartData = [
    '1' => getChartData(1),
    '7' => getChartData(7),
    '30' => getChartData(30),
];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แดชบอร์ดผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #121212;
            color: #e0ffe0;
            font-family: 'Poppins', sans-serif;
        }

        h2, h5 {
            color: #56f78f;
        }

        .card {
            background-color: #1e1e1e;
            border: 1px solid #28a74533;
            border-radius: 15px;
            box-shadow: 0 0 15px #28a74533;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: scale(1.02);
        }

        .card h4 {
            color: #28f77a;
        }

        .btn-success {
            background-color: #28a745;
            border: none;
            font-weight: bold;
        }

        .btn-success:hover {
            background-color: #1e7e34;
        }

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
    <h2 class="text-center mb-5">แดชบอร์ดสถิติการจอง</h2>

    <div class="row text-center g-4 mb-5">
        <div class="col-md-4">
            <div class="card p-4">
                <h5>วันนี้</h5>
                <h4><?= $todayCount ?> การจอง</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4">
                <h5>7 วันล่าสุด</h5>
                <h4><?= $weekCount ?> การจอง</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4">
                <h5>30 วันล่าสุด</h5>
                <h4><?= $monthCount ?> การจอง</h4>
            </div>
        </div>
    </div>

    <div class="text-center toggle-btns mb-3">
        <button class="btn btn-outline-success active" onclick="updateChart('1')">วันนี้</button>
        <button class="btn btn-outline-success" onclick="updateChart('7')">7 วันล่าสุด</button>
        <button class="btn btn-outline-success" onclick="updateChart('30')">30 วันล่าสุด</button>
    </div>

    <div class="chart-container mb-4">
        <h5 class="text-center mb-4">จำนวนการจอง (เลือกช่วงเวลา)</h5>
        <canvas id="bookingChart" height="100"></canvas>
    </div>

    <div class="text-center mt-4">
        <a href="admin_dashboard.php?page=tkadmin" class="btn btn-success px-4 py-2">ดูตารางการจองทั้งหมด</a>
    </div>
</div>

<script>
    const chartData = <?= json_encode($chartData) ?>;
    let currentChart;

    function createChart(labels, data) {
        const ctx = document.getElementById('bookingChart').getContext('2d');
        if (currentChart) currentChart.destroy();
        currentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'จำนวนการจอง',
                    data: data,
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
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#aaffaa',
                            stepSize: 1
                        },
                        grid: { color: '#333' }
                    },
                    x: {
                        ticks: { color: '#aaffaa' },
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
    }

    function updateChart(days) {
        const buttons = document.querySelectorAll('.toggle-btns .btn');
        buttons.forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');

        const labels = chartData[days].labels;
        const data = chartData[days].counts;
        createChart(labels, data);
    }

    // แสดงค่าเริ่มต้น
    window.onload = () => updateChart('1');
</script>

</body>
</html>
