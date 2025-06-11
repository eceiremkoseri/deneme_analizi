<?php
session_start();
if ($_SESSION['user_type'] !== 'ogrenci') {
    header("Location: login.php");
    exit;
}
include 'db.php';
$konu_adı = $_GET['konu_adı'] ?? '';
$ders_kodu = $_GET['ders_kodu'] ?? '';
$ogrenci_id = $_SESSION['user_id'];

$sql = "SELECT n.deneme_id, 
               SUM(n.dogru) AS dogru, 
               SUM(n.yanlis) AS yanlis, 
               SUM(n.net) AS net
        FROM notlar n
        INNER JOIN konular k ON n.konu_id = k.konu_id
        WHERE k.konu_adı = ? AND n.ogrenci_id = ? AND n.ders_kodu = ?
        GROUP BY n.deneme_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sis", $konu_adı, $ogrenci_id, $ders_kodu);
$stmt->execute();
$result = $stmt->get_result();

$deneme_analiz = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $deneme_analiz[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konu Detayları: <?= htmlspecialchars($konu_adı) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            max-width: 800px;
            margin: 30px auto;
        }
        .card {
            margin-top: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">Konu Analizi: <?= htmlspecialchars($konu_adı) ?></h1>

        
        <div class="card">
            <div class="card-header bg-primary text-white">
                Denemeler Arası Performans
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="progressChart"></canvas>
                </div>
            </div>
        </div>

    
        <div class="card">
            <div class="card-header bg-secondary text-white">
                Performans Tablosu
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Deneme ID</th>
                            <th>Doğru</th>
                            <th>Yanlış</th>
                            <th>Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deneme_analiz as $row): ?>
                            <tr>
                                <td>Deneme <?= htmlspecialchars($row['deneme_id']) ?></td>
                                <td><?= htmlspecialchars($row['dogru']) ?></td>
                                <td><?= htmlspecialchars($row['yanlis']) ?></td>
                                <td><?= number_format($row['net'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

   
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        
        const ctx = document.getElementById('progressChart').getContext('2d');
        const progressChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php foreach ($deneme_analiz as $row) echo "'Deneme " . $row['deneme_id'] . "',"; ?>],
                datasets: [
                    {
                        label: 'Doğru',
                        data: [<?php foreach ($deneme_analiz as $row) echo $row['dogru'] . ","; ?>],
                        backgroundColor: '#4caf50',
                    },
                    {
                        label: 'Yanlış',
                        data: [<?php foreach ($deneme_analiz as $row) echo $row['yanlis'] . ","; ?>],
                        backgroundColor: '#f44336',
                    },
                    {
                        label: 'Net',
                        data: [<?php foreach ($deneme_analiz as $row) echo $row['net'] . ","; ?>],
                        backgroundColor: '#ffc107',
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Denemeler Arası Performans Değişimi'
                    }
                }
            }
        });
    </script>
</body>
</html>
