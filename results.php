<?php
session_start();

// Zobrazení výsledků
if (!isset($_SESSION['score'])) {
    echo "Test ještě nebyl dokončen.";
    exit;
}

$score = $_SESSION['score'];

// Připojení k databázi
$host = 'localhost';
$dbname = 'autoskola';
$username = 'root'; 
$password = ''; 
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

// Získání počtu otázek v databázi
$stmt = $pdo->query("SELECT COUNT(*) FROM questions");
$total_questions = $stmt->fetchColumn();

// Vyčištění skóre pro příští test
session_destroy();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="results.css">
    <title>Výsledky Testu</title>
</head>
<body>

<div class="container">
    <h1>Výsledky Testu</h1>
    <div class="score">
        <p>Vaše skóre je: <?php echo $score; ?> z <?php echo $total_questions; ?>.</p>
    </div>
    <div class="back-btn">
        <a href="index.html">Zpět na hlavní stránku</a>
    </div>
</div>

</body>
</html>