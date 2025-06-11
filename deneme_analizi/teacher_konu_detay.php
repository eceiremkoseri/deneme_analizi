<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['user_type'] !== 'ogretmen') {
    header("Location: login.php");
    exit;
}
include 'db.php';

if (!isset($_GET['konu_id'])) {
    die("Konu ID'si belirtilmemiş.");
}
$konu_id = $_GET['konu_id'];
$sql_konu_ad = "SELECT konu_ad FROM konular WHERE konu_id = ?";
$stmt = $conn->prepare($sql_konu_ad);
$stmt->bind_param("i", $konu_id);
$stmt->execute();
$result = $stmt->get_result();
$konu = $result->fetch_assoc();

if (!$konu) {
    die("Konu bulunamadı.");
}
$konu_ad = $konu['konu_ad'];

$sql_analiz = "SELECT o.ad, o.soyad, 
                      SUM(n.dogru) AS toplam_dogru, 
                      SUM(n.yanlis) AS toplam_yanlis, 
                      SUM(n.bos) AS toplam_bos, 
                      SUM(n.net) AS toplam_net
               FROM notlar n
               INNER JOIN ogrenci o ON n.ogrenci_id = o.ogrenci_id
               WHERE n.konu_id = ?
               GROUP BY n.ogrenci_id";
$stmt = $conn->prepare($sql_analiz);
$stmt->bind_param("i", $konu_id);
$stmt->execute();
$result_analiz = $stmt->get_result();

$ogrenciler = [];
while ($row = $result_analiz->fetch_assoc()) {
    $ogrenciler[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konu: <?= htmlspecialchars($konu_ad) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            font-family: Arial, sans-serif;
        }
        h1, h2 {
            color: white;
        }
        .table {
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table thead {
            background-color: #6a11cb;
            color: white;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
        .btn {
            background-color: #007bff;
            color: white;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            transition: all 0.3s ease-in-out;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .container {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">Konu: <?= htmlspecialchars($konu_ad) ?></h1>
        <h2 class="text-center mb-4">Öğrenci Analizleri</h2>
        <?php if (!empty($ogrenciler)): ?>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Ad Soyad</th>
                        <th>Toplam Doğru</th>
                        <th>Toplam Yanlış</th>
                        <th>Toplam Boş</th>
                        <th>Toplam Net</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ogrenciler as $ogrenci): ?>
                        <tr>
                            <td><?= htmlspecialchars($ogrenci['ad'] . ' ' . $ogrenci['soyad']) ?></td>
                            <td><?= $ogrenci['toplam_dogru'] ?></td>
                            <td><?= $ogrenci['toplam_yanlis'] ?></td>
                            <td><?= $ogrenci['toplam_bos'] ?></td>
                            <td><?= number_format($ogrenci['toplam_net'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                Bu konuya ait analiz bulunamadı.
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
