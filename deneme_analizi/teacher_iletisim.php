<?php
session_start();
if ($_SESSION['user_type'] !== 'ogretmen') {
    header("Location: login.php");
    exit;
}

include 'db.php';

$ogretmen_id = $_SESSION['user_id']; 


$sql_gelen_mesajlar = "SELECT m.mesaj, m.tarih, o.ad, o.soyad 
                       FROM iletisim_mesajlar m
                       INNER JOIN ogrenci o ON m.gonderen_id = o.ogrenci_id
                       WHERE m.alici_id = ? 
                       ORDER BY m.tarih DESC";
$stmt = $conn->prepare($sql_gelen_mesajlar);
$stmt->bind_param("i", $ogretmen_id);
$stmt->execute();
$result_gelen_mesajlar = $stmt->get_result();

$gelen_mesajlar = [];
while ($row = $result_gelen_mesajlar->fetch_assoc()) {
    $gelen_mesajlar[] = $row;
}

$sql_ogrenciler = "SELECT ogrenci_id, ad, soyad FROM ogrenci";
$result_ogrenciler = $conn->query($sql_ogrenciler);

$ogrenciler = [];
while ($row = $result_ogrenciler->fetch_assoc()) {
    $ogrenciler[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alici_id'], $_POST['mesaj'])) {
    $alici_id = $_POST['alici_id'];
    $mesaj = $_POST['mesaj'];

    $sql_mesaj_gonder = "INSERT INTO iletisim_mesajlar (gonderen_id, alici_id, mesaj) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_mesaj_gonder);
    $stmt->bind_param("iis", $ogretmen_id, $alici_id, $mesaj);

    if ($stmt->execute()) {
        $success = "Mesaj başarıyla gönderildi!";
    } else {
        $error = "Mesaj gönderilirken bir hata oluştu.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğretmen İletişimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e6f2ff;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
        h1 {
            text-align: center;
            color: #4e54c8;
        }
        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-container label {
            color: #333;
        }
        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #42a5f5;
            box-shadow: 0 0 5px rgba(66, 165, 245, 0.7);
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #42a5f5;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #3578e5;
        }
        footer {
            text-align: center;
            margin-top: 30px;
            color: #fff;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Gelen Mesajlar</h1>
        <?php if (!empty($gelen_mesajlar)): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Gönderen</th>
                        <th scope="col">Mesaj</th>
                        <th scope="col">Tarih</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gelen_mesajlar as $mesaj): ?>
                        <tr>
                            <td><?= htmlspecialchars($mesaj['ad'] . ' ' . $mesaj['soyad']) ?></td>
                            <td><?= htmlspecialchars($mesaj['mesaj']) ?></td>
                            <td><?= htmlspecialchars($mesaj['tarih']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                Henüz mesaj yok.
            </div>
        <?php endif; ?>

        <h1 class="mt-5">Yeni Mesaj Gönder</h1>
        <?php if (isset($success)): ?>
            <div class="alert alert-success" role="alert">
                <?= $success ?>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="teacher_iletisim.php">
                <div class="mb-3">
                    <label for="alici_id" class="form-label">Öğrenci Seç</label>
                    <select name="alici_id" id="alici_id" class="form-select" required>
                        <option value="">Bir Öğrenci Seçin</option>
                        <?php foreach ($ogrenciler as $ogrenci): ?>
                            <option value="<?= $ogrenci['ogrenci_id'] ?>">
                                <?= htmlspecialchars($ogrenci['ad'] . ' ' . $ogrenci['soyad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="mesaj" class="form-label">Mesajınız</label>
                    <textarea name="mesaj" id="mesaj" class="form-control" placeholder="Mesajınızı buraya yazın..." rows="4" required></textarea>
                </div>
                <button type="submit">Gönder</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
