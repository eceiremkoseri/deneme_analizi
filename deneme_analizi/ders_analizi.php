<?php
session_start();
if ($_SESSION['user_type'] !== 'ogrenci') {
    header("Location: login.php");
    exit;
}
include 'db.php'; 

$ogrenci_id = $_SESSION['user_id'];

$ders_kodu = $_GET['ders_kodu'] ?? '';

$sql = "SELECT n.deneme_id, 
               k.konu_ad AS konu, 
               SUM(n.dogru) AS dogru, 
               SUM(n.yanlis) AS yanlis, 
               SUM(n.bos) AS bos, 
               SUM(n.net) AS net
        FROM notlar n
        INNER JOIN konular k ON n.konu_id = k.konu_id
        WHERE n.ogrenci_id = ? AND n.ders_kodu = ?
        GROUP BY n.deneme_id, k.konu_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $ogrenci_id, $ders_kodu);
$stmt->execute();
$result = $stmt->get_result();

$analiz = [];
$grafik_data = [];
while ($row = $result->fetch_assoc()) {
    $analiz[$row['deneme_id']][] = $row;


    if (!isset($grafik_data[$row['konu']])) {
        $grafik_data[$row['konu']] = [
            'dogru' => 0,
            'yanlis' => 0,
            'bos' => 0,
            'net' => 0
        ];
    }
    $grafik_data[$row['konu']]['dogru'] += $row['dogru'];
    $grafik_data[$row['konu']]['yanlis'] += $row['yanlis'];
    $grafik_data[$row['konu']]['bos'] += $row['bos'];
    $grafik_data[$row['konu']]['net'] += $row['net'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ders Analizi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #8a63c7;
        }

        .card {
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            font-size: 1.1rem;
            font-weight: bold;
        }

        .table {
            margin: 0 auto;
            width: 90%;
        }

        .tabs {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .tabs button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
        }

        .tabs button.active {
            background-color: #0056b3;
        }

        canvas {
            max-height: 300px;
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <h1 class="text-center mb-5">Ders Analizi: <?= htmlspecialchars($ders_kodu) ?></h1>

        <div class="tabs">
            <?php foreach (array_keys($analiz) as $deneme_id): ?>
                <button class="tab-btn" data-tab="tab-<?= $deneme_id ?>">Deneme <?= $deneme_id ?></button>
            <?php endforeach; ?>
        </div>

    
        <?php foreach ($analiz as $deneme_id => $konular): ?>
            <div class="card tab-content d-none" id="tab-<?= $deneme_id ?>">
                <div class="card-header bg-primary text-white text-center">
                    Deneme <?= $deneme_id ?> Analizi
                </div>
                <div class="card-body">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Konu</th>
                                <th>Doğru</th>
                                <th>Yanlış</th>
                                <th>Boş</th>
                                <th>Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($konular as $detay): ?>
                                <tr>
                                    <td><?= htmlspecialchars($detay['konu']) ?></td>
                                    <td><?= htmlspecialchars($detay['dogru']) ?></td>
                                    <td><?= htmlspecialchars($detay['yanlis']) ?></td>
                                    <td><?= htmlspecialchars($detay['bos']) ?></td>
                                    <td><?= number_format($detay['net'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="card">
            <div class="card-header bg-success text-white text-center">
                Konu Bazında Performans Grafiği
            </div>
            <div class="card-body">
                <canvas id="konuGrafik"></canvas>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-info text-white text-center">
                Deneme Bazında Net Değişimi Grafiği
            </div>
            <div class="card-body">
                <canvas id="denemeGrafik"></canvas>
            </div>
        </div>
    </div>

 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
       
        document.querySelectorAll('.tab-btn').forEach((button) => {
            button.addEventListener('click', function () {
                document.querySelectorAll('.tab-btn').forEach((btn) => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach((content) => content.classList.add('d-none'));

                this.classList.add('active');
                document.getElementById(this.dataset.tab).classList.remove('d-none');
            });
        });
        document.querySelector('.tab-btn').click(); 

       
        const konuCtx = document.getElementById('konuGrafik').getContext('2d');
        const konuGrafik = new Chart(konuCtx, {
            type: 'bar',
            data: {
                labels: [<?= '"' . implode('","', array_keys($grafik_data)) . '"' ?>],
                datasets: [
                    {
                        label: 'Doğru',
                        data: [<?= implode(',', array_column($grafik_data, 'dogru')) ?>],
                        backgroundColor: '#4caf50'
                    },
                    {
                        label: 'Yanlış',
                        data: [<?= implode(',', array_column($grafik_data, 'yanlis')) ?>],
                        backgroundColor: '#f44336'
                    },
                    {
                        label: 'Boş',
                        data: [<?= implode(',', array_column($grafik_data, 'bos')) ?>],
                        backgroundColor: '#ffc107'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Konu Bazında Toplam Performans' }
                }
            }
        });

        
        const denemeNetData = <?= json_encode(array_map(function ($deneme) {
            return array_sum(array_column($deneme, 'net'));
        }, $analiz)) ?>;
        const denemeCtx = document.getElementById('denemeGrafik').getContext('2d');
        const denemeGrafik = new Chart(denemeCtx, {
            type: 'line',
            data: {
                labels: [<?= implode(',', array_keys($analiz)) ?>],
                datasets: [
                    {
                        label: 'Net',
                        data: denemeNetData,
                        borderColor: '#42a5f5',
                        backgroundColor: 'rgba(66, 165, 245, 0.2)',
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Deneme Bazında Net Değişimi' }
                }
            }
        });
    </script>
</body>
</html>
