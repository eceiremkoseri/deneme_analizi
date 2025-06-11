<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['user_type'] !== 'ogretmen') {
    header("Location: login.php");
    exit;
}
include 'db.php';

$sql_siniflar = "SELECT sinif_id, sinif_adi FROM siniflar ORDER BY sinif_adi";
$result_siniflar = $conn->query($sql_siniflar);

$siniflar = [];
if ($result_siniflar->num_rows > 0) {
    while ($row = $result_siniflar->fetch_assoc()) {
        $siniflar[] = $row;
    }
}

$sql_denemeler = "SELECT DISTINCT deneme_id FROM notlar ORDER BY deneme_id";
$result_denemeler = $conn->query($sql_denemeler);

$denemeler = [];
if ($result_denemeler->num_rows > 0) {
    while ($row = $result_denemeler->fetch_assoc()) {
        $denemeler[] = $row;
    }
}

$ogrenciler = [];
if (isset($_GET['deneme_id'])) {
    $deneme_id = $_GET['deneme_id'];
    $sinif_id = $_GET['sinif_id'] ?? null;

    if ($sinif_id === "all") {
        $sql_ogrenciler = "SELECT o.ogrenci_id, o.ad, o.soyad, s.sinif_adi, 
                                  SUM(n.net) AS toplam_net
                           FROM ogrenci o
                           INNER JOIN siniflar s ON o.sinif_id = s.sinif_id
                           INNER JOIN notlar n ON o.ogrenci_id = n.ogrenci_id
                           WHERE n.deneme_id = ?
                           GROUP BY o.ogrenci_id
                           ORDER BY toplam_net DESC";
        $stmt = $conn->prepare($sql_ogrenciler);
        $stmt->bind_param("i", $deneme_id);
    } else {
        $sql_ogrenciler = "SELECT o.ogrenci_id, o.ad, o.soyad, s.sinif_adi, 
                                  SUM(n.net) AS toplam_net
                           FROM ogrenci o
                           INNER JOIN siniflar s ON o.sinif_id = s.sinif_id
                           INNER JOIN notlar n ON o.ogrenci_id = n.ogrenci_id
                           WHERE s.sinif_id = ? AND n.deneme_id = ?
                           GROUP BY o.ogrenci_id
                           ORDER BY toplam_net DESC";
        $stmt = $conn->prepare($sql_ogrenciler);
        $stmt->bind_param("ii", $sinif_id, $deneme_id);
    }

    $stmt->execute();
    $result_ogrenciler = $stmt->get_result();

    while ($row = $result_ogrenciler->fetch_assoc()) {
        $ogrenciler[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sınıflar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #e6f2ff;">
    <div class="container my-5">
        <h1 class="text-center mb-4">Sınıflar</h1>

        <form method="GET" action="teacher_siniflar.php" class="mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="sinif_id" class="form-label">Sınıf Seç:</label>
                    <select name="sinif_id" id="sinif_id" class="form-select" required>
                        <option value="all" <?= isset($sinif_id) && $sinif_id === "all" ? 'selected' : '' ?>>Hepsi</option>
                        <?php foreach ($siniflar as $sinif): ?>
                            <option value="<?= $sinif['sinif_id'] ?>" <?= isset($sinif_id) && $sinif_id == $sinif['sinif_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sinif['sinif_adi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="deneme_id" class="form-label">Deneme Seç:</label>
                    <select name="deneme_id" id="deneme_id" class="form-select" required>
                        <option value="">Bir Deneme Seçin</option>
                        <?php foreach ($denemeler as $deneme): ?>
                            <option value="<?= $deneme['deneme_id'] ?>" <?= isset($deneme_id) && $deneme_id == $deneme['deneme_id'] ? 'selected' : '' ?>>
                                Deneme <?= htmlspecialchars($deneme['deneme_id']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Göster</button>
        </form>

        <?php if (!empty($ogrenciler)): ?>
            <h2 class="mb-3">
                <?= $sinif_id === "all" ? "Tüm Sınıflar" : htmlspecialchars($siniflar[array_search($sinif_id, array_column($siniflar, 'sinif_id'))]['sinif_adi']) ?>
                - Deneme <?= htmlspecialchars($deneme_id) ?> Sonuçları
            </h2>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Ad Soyad</th>
                        <th>Mevcut Sınıf</th>
                        <th>Toplam Net</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ogrenciler as $ogrenci): ?>
                        <tr>
                            <td><?= htmlspecialchars($ogrenci['ad'] . ' ' . $ogrenci['soyad']) ?></td>
                            <td><?= htmlspecialchars($ogrenci['sinif_adi']) ?></td>
                            <td><?= number_format($ogrenci['toplam_net'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">Henüz bu deneme için sonuç bulunamadı.</div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
