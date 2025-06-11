<?php
session_start();
if ($_SESSION['user_type'] !== 'ogrenci') {
    header("Location: login.php");
    exit;
}
include 'db.php'; 
$sql = "SELECT DISTINCT deneme_id FROM notlar";
$result = $conn->query($sql);

$denemeler = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $denemeler[] = $row['deneme_id'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denemeler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container my-5">
        <h1 class="mb-4">Denemeler</h1>
        <div class="row">
            <?php foreach ($denemeler as $deneme_id): ?>
                <div class="col-md-3 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Deneme <?= $deneme_id ?></h5>
                            <a href="deneme_detay.php?deneme_id=<?= $deneme_id ?>" class="btn btn-primary">Görüntüle</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
