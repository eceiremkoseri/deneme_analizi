<?php
session_start();
if ($_SESSION['user_type'] !== 'ogrenci') {
    header("Location: login.php");
    exit;
}
include 'db.php';
$ogrenci_id = $_SESSION['user_id'];
$deneme_id = $_GET['deneme_id'] ?? 0;

$sql = "SELECT d.ders_adi, 
               SUM(n.dogru) AS dogru, 
               SUM(n.yanlis) AS yanlis, 
               SUM(n.bos) AS bos, 
               SUM(n.net) AS net
        FROM notlar n
        INNER JOIN ders d ON n.ders_kodu = d.ders_kodu
        WHERE n.deneme_id = ? AND n.ogrenci_id = ?
        GROUP BY d.ders_adi";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $deneme_id, $ogrenci_id);
$stmt->execute();
$result = $stmt->get_result();

$analiz = [];
$toplam_net = 0;
$dogru_toplam = 0;
$yanlis_toplam = 0;
$bos_toplam = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $analiz[] = $row;
        $toplam_net += $row['net'];
        $dogru_toplam += $row['dogru'];
        $yanlis_toplam += $row['yanlis'];
        $bos_toplam += $row['bos'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deneme <?= $deneme_id ?> Detayları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: linear-gradient(to bottom, #4e54c8, #8f94fb);
            color: #fff;
            font-family: Arial, sans-serif;
        }
        h1 {
            font-size: 2.5rem;
            font-weight: bold;
            color: #fff;
            text-align: center;
        }
        .table {
            background-color: #fff;
            color: #333;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }
        .card {
            border: none;
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }
        .card-header {
            font-weight: bold;
            font-size: 1.2rem;
        }
        canvas {
            max-width: 500px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1>Deneme <?= $deneme_id ?> Detayları</h1>
        <table class="table table-striped mt-4">
            <thead class="table-dark">
                <tr>
                    <th>Ders</th>
                    <th>Doğru</th>
                    <th>Yanlış</th>
                    <th>Boş</th>
                    <th>Net</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($analiz as $detay): ?>
                    <tr>
                        <td><?= $detay['ders_adi'] ?></td>
                        <td><?= $detay['dogru'] ?></td>
                        <td><?= $detay['yanlis'] ?></td>
                        <td><?= $detay['bos'] ?></td>
                        <td><?= number_format($detay['net'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="card mt-5">
            <div class="card-header bg-primary text-white text-center">
                Başarı Analizi
            </div>
            <div class="card-body text-center">
                <canvas id="successChart"></canvas>
                <p class="mt-3 fs-5">Toplam Net: <?= number_format($toplam_net, 2) ?></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ctx = document.getElementById('successChart').getContext('2d');
        const successChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Doğru', 'Yanlış', 'Boş'],
                datasets: [{
                    data: [
                        <?= $dogru_toplam ?>,
                        <?= $yanlis_toplam ?>,
                        <?= $bos_toplam ?>
                    ],
                    backgroundColor: ['#4caf50', '#f44336', '#ffc107']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Başarı Analizi (Doğru, Yanlış ve Boş)'
                    }
                }
            }
        });
    </script>
</body>
</html>
