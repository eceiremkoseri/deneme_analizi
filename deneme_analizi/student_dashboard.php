<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['user_type'] !== 'ogrenci') {
    header("Location: login.php");
    exit;
}

include 'db.php'; 

$ogrenci_id = $_SESSION['user_id'];


$sql = "SELECT deneme_id, SUM(net) AS toplam_net FROM notlar WHERE ogrenci_id = ? GROUP BY deneme_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ogrenci_id);
$stmt->execute();
$result = $stmt->get_result();

$deneme_ids = [];
$toplam_netler = [];
while ($row = $result->fetch_assoc()) {
    $deneme_ids[] = "Deneme " . $row['deneme_id'];
    $toplam_netler[] = $row['toplam_net'];
}
$sql = "SELECT d.ders_adi, k.konu_ad, SUM(n.net) AS toplam_net 
        FROM notlar n
        INNER JOIN ders d ON n.ders_kodu = d.ders_kodu
        INNER JOIN konular k ON n.konu_id = k.konu_id
        WHERE n.ogrenci_id = ?
        GROUP BY n.ders_kodu, n.konu_id
        ORDER BY toplam_net DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ogrenci_id);
$stmt->execute();
$result = $stmt->get_result();

$konular = [];
while ($row = $result->fetch_assoc()) {
    $konular[] = $row;
}


$en_iyi_konular = array_slice($konular, 0, 3);
$gelistirilmesi_gereken_konular = array_slice($konular, -3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Paneli</title>
  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .welcome-header {
            font-size: 3rem;
            font-weight: bold;
            color: #4e54c8;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
            animation: fadeIn 1.5s ease-in-out;
        }

        .welcome-subtitle {
            text-align: center;
            color: #555;
            font-size: 1.2rem;
            margin-bottom: 40px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar">
            <h3 class="text-center">Öğrenci Paneli</h3>
            <ul class="nav flex-column">
                <li><a href="student_dashboard.php" class="nav-link">Anasayfa</a></li>
                <li><a href="deneme.php" class="nav-link">Deneme</a></li>
                <li>
                    <a href="#" class="nav-link">Dersler</a>
                    <ul class="nav flex-column ms-3">
                        <?php
                        $sql = "SELECT * FROM ders WHERE ders_adi != 'Rehberlik (danışman)'"; 
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo '<li><a href="ders_analizi.php?ders_kodu=' . $row['ders_kodu'] . '" class="nav-link">' . $row['ders_adi'] . '</a></li>';
                        }
                        ?>
                    </ul>
                </li>
                <li><a href="iletisim.php" class="nav-link">İletişim</a></li>
            </ul>
        </div>
        <div class="container p-4">
            <h1 class="welcome-header">Hoş Geldiniz</h1>
            <p class="welcome-subtitle">Burada analizlerinizi ve performansınızı görüntüleyebilirsiniz.</p>
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">Genel Performans Grafiği</div>
                        <div class="card-body">
                            <canvas id="genelPerformansGrafik"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">En İyi Konular</div>
                        <div class="card-body">
                            <ul>
                                <?php foreach ($en_iyi_konular as $konu): ?>
                                    <li><strong><?= htmlspecialchars($konu['ders_adi']) ?>:</strong> <?= htmlspecialchars($konu['konu_ad']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-header">Geliştirilmesi Gereken Konular</div>
                        <div class="card-body">
                            <ul>
                                <?php foreach ($gelistirilmesi_gereken_konular as $konu): ?>
                                    <li><strong><?= htmlspecialchars($konu['ders_adi']) ?>:</strong> <?= htmlspecialchars($konu['konu_ad']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('genelPerformansGrafik').getContext('2d');
        const genelPerformansGrafik = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($deneme_ids) ?>,
                datasets: [{
                    label: 'Toplam Net',
                    data: <?= json_encode($toplam_netler) ?>,
                    borderColor: '#42a5f5',
                    backgroundColor: 'rgba(66, 165, 245, 0.2)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Genel Performans Değişimi' }
                }
            }
        });
    </script>
</body>
</html>   
