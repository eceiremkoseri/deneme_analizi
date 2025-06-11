<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['user_type'] !== 'ogretmen') {
    header("Location: login.php");
    exit;
}

include 'db.php';

if (!isset($_GET['deneme_id']) || !isset($_GET['ogrenci_id'])) {
    die("Deneme ID veya Öğrenci ID belirtilmemiş.");
}
$deneme_id = $_GET['deneme_id'];
$ogrenci_id = $_GET['ogrenci_id'];
$ogretmen_id = $_SESSION['user_id'];
$sql_ders = "SELECT o.ders_kodu, d.ders_adi 
             FROM ogretmen o
             INNER JOIN ders d ON o.ders_kodu = d.ders_kodu
             WHERE o.ogretmen_id = ?";
$stmt = $conn->prepare($sql_ders);
$stmt->bind_param("i", $ogretmen_id);
$stmt->execute();
$result = $stmt->get_result();
$ders = $result->fetch_assoc();

if (!$ders) {
    die("Ders bilgisi bulunamadı.");
}
$ders_kodu = $ders['ders_kodu'];
$ders_adi = $ders['ders_adi'];

$sql_dersler = "SELECT d.ders_adi, 
                       SUM(n.dogru) AS toplam_dogru, 
                       SUM(n.yanlis) AS toplam_yanlis, 
                       SUM(n.bos) AS toplam_bos, 
                       SUM(n.net) AS toplam_net
                FROM notlar n
                INNER JOIN ders d ON n.ders_kodu = d.ders_kodu
                WHERE n.deneme_id = ? AND n.ogrenci_id = ?
                GROUP BY d.ders_kodu";
$stmt = $conn->prepare($sql_dersler);
$stmt->bind_param("ii", $deneme_id, $ogrenci_id);
$stmt->execute();
$result_dersler = $stmt->get_result();

$ders_analizleri = [];
while ($row = $result_dersler->fetch_assoc()) {
    $ders_analizleri[] = $row;
}

$sql_konular = "SELECT k.konu_ad, 
                       SUM(n.dogru) AS toplam_dogru, 
                       SUM(n.yanlis) AS toplam_yanlis, 
                       SUM(n.bos) AS toplam_bos, 
                       SUM(n.net) AS toplam_net
                FROM notlar n
                INNER JOIN konular k ON n.konu_id = k.konu_id
                WHERE n.deneme_id = ? AND n.ogrenci_id = ? AND n.ders_kodu = ?
                GROUP BY k.konu_id";
$stmt = $conn->prepare($sql_konular);
$stmt->bind_param("iis", $deneme_id, $ogrenci_id, $ders_kodu);
$stmt->execute();
$result_konular = $stmt->get_result();

$konu_analizleri = [];
while ($row = $result_konular->fetch_assoc()) {
    $konu_analizleri[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deneme <?= htmlspecialchars($deneme_id) ?> Detayları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center">Deneme <?= htmlspecialchars($deneme_id) ?> Detayları</h1>
        <div class="card my-4">
            <div class="card-header bg-primary text-white">
                Ders Bazında Analiz
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Ders</th>
                            <th>Toplam Doğru</th>
                            <th>Toplam Yanlış</th>
                            <th>Toplam Boş</th>
                            <th>Toplam Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ders_analizleri as $ders): ?>
                            <tr>
                                <td><?= htmlspecialchars($ders['ders_adi']) ?></td>
                                <td><?= $ders['toplam_dogru'] ?></td>
                                <td><?= $ders['toplam_yanlis'] ?></td>
                                <td><?= $ders['toplam_bos'] ?></td>
                                <td><?= number_format($ders['toplam_net'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-success text-white">
                <?= htmlspecialchars($ders_adi) ?> Konu Analizi
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php foreach ($konu_analizleri as $konu): ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($konu['konu_ad']) ?></strong><br>
                            Doğru: <?= $konu['toplam_dogru'] ?>, Yanlış: <?= $konu['toplam_yanlis'] ?>, 
                            Boş: <?= $konu['toplam_bos'] ?>, Net: <?= number_format($konu['toplam_net'], 2) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
