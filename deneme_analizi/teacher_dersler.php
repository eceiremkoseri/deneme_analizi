<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['user_type'] !== 'ogretmen') {
    header("Location: login.php");
    exit;
}

include 'db.php';

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

$sql_konular = "SELECT konu_id, konu_ad FROM konular WHERE ders_kodu = ?";
$stmt = $conn->prepare($sql_konular);
$stmt->bind_param("s", $ders_kodu);
$stmt->execute();
$result_konular = $stmt->get_result();

$konular = [];
while ($row = $result_konular->fetch_assoc()) {
    $konular[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($ders_adi) ?> - Konular</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            font-family: Arial, sans-serif;
        }

        h1 {
            color: white;
            margin-bottom: 40px;
        }

        .btn.konu-btn {
            background-color: white;
            color: #007bff;
            font-size: 14px;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease-in-out;
            width: 100%;
        }

        .btn.konu-btn:hover {
            background-color: #007bff;
            color: white;
            transform: scale(1.05);
        }

        .row .col-md-3 {
            margin-bottom: 20px;
        }
        
        .container {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center"><?= htmlspecialchars($ders_adi) ?> Konuları</h1>
        <div class="row g-3 mt-4">
            <?php foreach ($konular as $konu): ?>
                <div class="col-md-3">
                    <a href="teacher_konu_detay.php?konu_id=<?= $konu['konu_id'] ?>" class="btn konu-btn">
                        <?= htmlspecialchars($konu['konu_ad']) ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
