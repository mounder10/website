<?php
session_start();
$host = 'localhost';
$dbname = 'test';
$user = 'root';
$pass = '';
$conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $file = $_FILES['photo'];
    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    echo $fileExt;
    $not_allowed = ['php', 'svg', 'zip', 'exe','py'];
    
    if (in_array($fileExt, $not_allowed)) {
        $_SESSION['error'] = "Invalid file type!";
    } else {
      
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        if (move_uploaded_file($fileTmp, $uploadDir . $fileName)) {
            $stmt = $conn->prepare("INSERT INTO photos (name) VALUES (?)");
            $stmt->execute([$fileName]);
            $_SESSION['success'] = "File uploaded successfully!";
        } else {
            $_SESSION['error'] = "Failed to upload file!";
        }
    }
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Photos</title>
</head>
<body>
    <?php if (isset($_SESSION['error'])) { echo "<p style='color: red;'>" . $_SESSION['error'] . "</p>"; unset($_SESSION['error']); } ?>
    <?php if (isset($_SESSION['success'])) { echo "<p style='color: green;'>" . $_SESSION['success'] . "</p>"; unset($_SESSION['success']); } ?>
    
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="file" name="photo" required>
        <button type="submit">Upload</button>
    </form>
    
    <h2>Uploaded Photos</h2>
    <?php
    $stmt = $conn->query("SELECT * FROM photos ORDER BY id DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<img src='uploads/" . htmlspecialchars($row['name']) . "' width='200' style='margin:10px;'>";
    }
    ?>
</body>
</html>
