<?php
session_start();

// Pokud není uživatel přihlášen, přesměrujeme ho na přihlašovací stránku
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

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

// Načtení uživatelských údajů
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");  // Změna na 'name' místo 'username'
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Pokud uživatel neexistuje, přesměrujeme na přihlašovací stránku
if (!$user) {
    header('Location: login.html');
    exit;
}

// Načtení otázky
if (isset($_GET['question_id'])) {
    $question_id = (int)$_GET['question_id'];
} else {
    $question_id = 1; // Začneme od první otázky
}

// Získání otázky z databáze
$stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->execute([$question_id]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

// Kontrola, jestli otázka existuje
if (!$question) {
    echo "Otázka nebyla nalezena.";
    exit;
}

// Zpracování odpovědi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_answer = $_POST['answer'];
    $correct_answer = $question['correct_answer'];

    // Uložení bodu do session
    if (!isset($_SESSION['score'])) {
        $_SESSION['score'] = 0;
    }

    if ($user_answer == $correct_answer) {
        $_SESSION['score']++;
    }

    // Přechod na další otázku nebo dokončení testu
    $next_question_id = $question_id + 1;
    $stmt = $pdo->query("SELECT COUNT(*) FROM questions");
    $total_questions = $stmt->fetchColumn();
    
    if ($next_question_id > $total_questions) {
        // Uložení výsledků testu do databáze
        $score = $_SESSION['score']; // Body získané v testu
        $stmt = $pdo->prepare("INSERT INTO tests (user_id, score) VALUES (?, ?)");
        $stmt->execute([$user_id, $score]);

        // Přesměrování na stránku s výsledky
        header("Location: results.php");
        exit;
    } else {
        header("Location: test.php?question_id=$next_question_id");
        exit;
    }
}

// Zobrazení otázky a odpovědí
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/img/image.svg">
    <link rel="stylesheet" href="test.css">
    <title>Autoškola adam</title>
</head>
<body>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
  }
  .container {
      width: 80%;
      margin: 20px auto;
      background-color: #fff;
      padding: 20px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
  h1 {
      text-align: center;
  }
  .question {
      font-size: 18px;
      margin-bottom: 20px;
  }
  .answers {
      margin: 10px 0;
  }
  .answers label {
      display: block;
      margin: 5px 0;
  }
  .btn {
      display: block;
      width: 100%;
      padding: 10px;
      background-color: #4CAF50;
      color: white;
      border: none;
      cursor: pointer;
      font-size: 16px;
  }
  .btn:hover {
      background-color: #45a049;
  }
  .back-btn {
      margin-top: 20px;
      text-align: center;
  }
  .back-btn a {
      padding: 10px 20px;
      background-color: #2196F3;
      color: white;
      text-decoration: none;
      font-size: 16px;
      border-radius: 5px;
  }
  .back-btn a:hover {
      background-color: #0b7dda;
  }
</style>
<div class="container">
    <h1>Test Autoškola</h1>
    <p>Uživatel: <?php echo htmlspecialchars($user['name']); ?></p> <!-- Zobrazení jména uživatele -->

    <div class="question">
        <p><?php echo htmlspecialchars($question['question_text']); ?></p>
    </div>

    <form method="POST">
        <div class="answers">
            <label>
                <input type="radio" name="answer" value="<?php echo htmlspecialchars($question['wrong_answer_1']); ?>" required>
                <?php echo htmlspecialchars($question['wrong_answer_1']); ?>
            </label>
            <label>
                <input type="radio" name="answer" value="<?php echo htmlspecialchars($question['wrong_answer_2']); ?>">
                <?php echo htmlspecialchars($question['wrong_answer_2']); ?>
            </label>
            <label>
                <input type="radio" name="answer" value="<?php echo htmlspecialchars($question['correct_answer']); ?>">
                <?php echo htmlspecialchars($question['correct_answer']); ?>
            </label>
        </div>
        <button type="submit" class="btn">Odpovědět</button>
    </form>
</div>

</body>
</html>
