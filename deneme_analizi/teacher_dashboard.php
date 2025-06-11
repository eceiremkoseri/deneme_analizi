<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['user_type'] !== 'ogretmen') {
    header("Location: login.php");
    exit;
}

include 'db.php'; 
$sql_ogrenci = "SELECT COUNT(*) AS toplam_ogrenci FROM ogrenci";
$result_ogrenci = $conn->query($sql_ogrenci);
$toplam_ogrenci = $result_ogrenci->fetch_assoc()['toplam_ogrenci'];
$sql_sinif = "SELECT COUNT(*) AS toplam_sinif FROM siniflar";
$result_sinif = $conn->query($sql_sinif);
$toplam_sinif = $result_sinif->fetch_assoc()['toplam_sinif'];
$sql_en_basarili = "SELECT s.sinif_adi, AVG(n.net) AS ortalama_net
                    FROM notlar n
                    INNER JOIN ogrenci o ON n.ogrenci_id = o.ogrenci_id
                    INNER JOIN siniflar s ON o.sinif_id = s.sinif_id
                    GROUP BY s.sinif_id
                    ORDER BY ortalama_net DESC
                    LIMIT 1";
$result_en_basarili = $conn->query($sql_en_basarili);
$en_basarili_sinif = $result_en_basarili->fetch_assoc();
$sql_en_zayif = "SELECT s.sinif_adi, AVG(n.net) AS ortalama_net
                 FROM notlar n
                 INNER JOIN ogrenci o ON n.ogrenci_id = o.ogrenci_id
                 INNER JOIN siniflar s ON o.sinif_id = s.sinif_id
                 GROUP BY s.sinif_id
                 ORDER BY ortalama_net ASC
                 LIMIT 1";
$result_en_zayif = $conn->query($sql_en_zayif);
$en_zayif_sinif = $result_en_zayif->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğretmen Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #8a3c8c;
            color: white;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            border-radius: 12px;
        }
        .menu {
            font-size: 1.5rem;
        }
        .nav-link {
            font-size: 1.8rem;
            color: white;
            transition: background-color 0.3s;
        }
        .nav-link:hover {
            background-color: #6a2c87;
        }
        .card-header {
            background-color: #4e54c8;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
        }
        .welcome-header {
            font-size: 3rem;
            font-weight: bold;
            color: white;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
        }
        .welcome-subtitle {
            text-align: center;
            color: #f1f1f1;
            font-size: 1.2rem;
            margin-bottom: 40px;
        }
        .info-card {
            border-radius: 15px;
            padding: 20px;
            color: white;
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 20px;
        }
        .info-card.blue { background-color: #1f72d1; }
        .info-card.green { background-color: #4caf50; }
        .info-card.orange { background-color: #ff9800; }
        .info-card.red { background-color: #f44336; }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="bg-dark text-white p-3" style="width: 250px; height: 100vh;">
            <h4 class="text-center">Deneme Analiz Sistemi</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white" href="teacher_dashboard.php">Anasayfa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="teacher_dersler.php">Dersler</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="teacher_denemeler.php">Denemeler</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="teacher_siniflar.php">Sınıflar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="teacher_iletisim.php">İletişim</a>
                </li>
            </ul>
        </div>
        <div class="p-4" style="flex: 1;">
            <h1 class="welcome-header">Öğretmen Paneline Hoş Geldiniz</h1>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card info-card blue">
                        <div class="card-body">
                            <h5 class="card-title">Toplam Öğrenci</h5>
                            <p class="card-text fs-3"><?= $toplam_ogrenci ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card info-card green">
                        <div class="card-body">
                            <h5 class="card-title">Toplam Sınıf</h5>
                            <p class="card-text fs-3"><?= $toplam_sinif ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card info-card orange">
                        <div class="card-body">
                            <h5 class="card-title">En Başarılı Sınıf</h5>
                            <p class="card-text fs-5"><?= $en_basarili_sinif['sinif_adi'] ?? 'Veri Yok' ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card info-card red">
                        <div class="card-body">
                            <h5 class="card-title">En Zayıf Sınıf</h5>
                            <p class="card-text fs-5"><?= $en_zayif_sinif['sinif_adi'] ?? 'Veri Yok' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
