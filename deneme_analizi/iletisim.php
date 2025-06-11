<?php
include 'db.php';

session_start();
$ogrenci_id = $_SESSION['user_id']; 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad_soyad = $_POST['ad_soyad'];
    $email = $_POST['email'];
    $mesaj = $_POST['mesaj'];
    $alici_id = $_POST['alici_id'];  

   
    $sql = "INSERT INTO iletisim_mesajlar (gonderen_id, alici_id, mesaj) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $ogrenci_id, $alici_id, $mesaj);  
    if ($stmt->execute()) {
        $success = "Mesajınız başarıyla gönderildi. Teşekkür ederiz!";
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
    <title>Öğrenci İletişimi</title>
    
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
        <h1>Yeni Mesaj Gönder</h1>

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
           
            <form method="POST" action="iletisim.php">
                <div class="mb-3">
                    <label for="ad_soyad" class="form-label">Ad Soyad</label>
                    <input type="text" name="ad_soyad" class="form-control" placeholder="Ad Soyad" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-posta</label>
                    <input type="email" name="email" class="form-control" placeholder="E-posta" required>
                </div>
                <div class="mb-3">
                    <label for="mesaj" class="form-label">Mesajınız</label>
                    <textarea name="mesaj" class="form-control" placeholder="Mesajınızı buraya yazın..." rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="alici_id" class="form-label">Öğretmen Seç</label>
                    <select name="alici_id" class="form-select" required>
                        <option value="">Bir Öğretmen Seçin</option>
                        <?php
                       
                        $sql_ogretmenler = "SELECT ogretmen_id, ogretmen_ad, ogretmen_soyad FROM ogretmen";
                        $result_ogretmenler = $conn->query($sql_ogretmenler);
                        while ($row = $result_ogretmenler->fetch_assoc()) {
                            echo '<option value="' . $row['ogretmen_id'] . '">' . $row['ogretmen_ad'] . ' ' . $row['ogretmen_soyad'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <button type="submit">Gönder</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
