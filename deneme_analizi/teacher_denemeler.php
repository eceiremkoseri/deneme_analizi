<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['user_type'] !== 'ogretmen') {
    header("Location: login.php");
    exit;
}

include 'db.php';
$sql_ogrenciler = "SELECT ogrenci_id, ad, soyad FROM ogrenci";
$result = $conn->query($sql_ogrenciler);

$ogrenciler = [];
while ($row = $result->fetch_assoc()) {
    $ogrenciler[] = $row;
}
$denemeler = [];
if (isset($_GET['ogrenci_id'])) {
    $ogrenci_id = $_GET['ogrenci_id'];
    $sql_ogrenci = "SELECT ad, soyad FROM ogrenci WHERE ogrenci_id = ?";
    $stmt = $conn->prepare($sql_ogrenci);
    $stmt->bind_param("i", $ogrenci_id);
    $stmt->execute();
    $result_ogrenci = $stmt->get_result();
    $ogrenci = $result_ogrenci->fetch_assoc();

    $sql_denemeler = "SELECT deneme_id, 
                             SUM(dogru) AS toplam_dogru, 
                             SUM(yanlis) AS toplam_yanlis, 
                             SUM(bos) AS toplam_bos, 
                             SUM(net) AS toplam_net
                      FROM notlar
                      WHERE ogrenci_id = ?
                      GROUP BY deneme_id
                      ORDER BY deneme_id";
    $stmt = $conn->prepare($sql_denemeler);
    $stmt->bind_param("i", $ogrenci_id);
    $stmt->execute();
    $result_denemeler = $stmt->get_result();

    while ($row = $result_denemeler->fetch_assoc()) {
        $denemeler[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deneme Analizleri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #e6f2ff; 
            font-family: Arial, sans-serif;
        }
        h1, h2 {
            color: #333;
        }
        .btn {
            font-size: 14px;
            padding: 12px;
            background-color: #007bff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease-in-out;
        }
        .btn:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        .row .col-md-3 {
            margin-bottom: 15px;
        }
       
        canvas {
            max-width: 80%;
            max-height: 500px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center">Deneme Analizleri</h1>

       
        <form method="GET" action="teacher_denemeler.php" class="my-4">
            <div class="row">
                <div class="col-md-8">
                    <select name="ogrenci_id" id="ogrenci_id" class="form-select" required>
                        <option value="">Bir Öğrenci Seçin</option>
                        <?php foreach ($ogrenciler as $ogrenci): ?>
                            <option value="<?= $ogrenci['ogrenci_id'] ?>" <?= isset($ogrenci_id) && $ogrenci_id == $ogrenci['ogrenci_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ogrenci['ad'] . ' ' . $ogrenci['soyad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Analizi Görüntüle</button>
                </div>
            </div>
        </form>

        <?php if (!empty($denemeler)): ?>
            <h2 class="text-center">Deneme Sonuçları</h2> 
            <div class="row g-3">
                <?php foreach ($denemeler as $deneme): ?>
                    <div class="col-md-3">
                        <a href="teacher_deneme_detay.php?deneme_id=<?= $deneme['deneme_id'] ?>&ogrenci_id=<?= $ogrenci_id ?>" class="btn btn-secondary w-100 py-3">
                            Deneme <?= htmlspecialchars($deneme['deneme_id']) ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="my-5">
                <canvas id="denemeBarGrafik"></canvas>
            </div>
            <div class="my-5">
                <canvas id="denemeCizgiGrafik"></canvas>
            </div>

            <script>
                const denemeLabels = <?= json_encode(array_column($denemeler, 'deneme_id')) ?>;
                const netData = <?= json_encode(array_column($denemeler, 'toplam_net')) ?>;
                const denemeBarGrafik = document.getElementById('denemeBarGrafik').getContext('2d');
                new Chart(denemeBarGrafik, {
                    type: 'bar',
                    data: {
                        labels: denemeLabels,
                        datasets: [{
                            label: 'Net',
                            data: netData,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            title: { display: true, text: 'Deneme Bazında Netler' }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });

            
                const denemeCizgiGrafik = document.getElementById('denemeCizgiGrafik').getContext('2d');
                new Chart(denemeCizgiGrafik, {
                    type: 'line',
                    data: {
                        labels: denemeLabels,
                        datasets: [{
                            label: 'Net',
                            data: netData,
                            fill: false,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            title: { display: true, text: 'Deneme Bazında Başarı Değişimi' }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            </script>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
