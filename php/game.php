<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
include '../php/activity_tracker.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Pobranie połączenia z bazą danych
$db = Database::getInstance()->getConnection();
$userId = $_SESSION['user_id'];

// Pobieranie danych gracza, bez kolumny avatar
$stmt = $db->prepare("SELECT players.id, players.username, player_stats.level, player_stats.gold, player_stats.silver, player_stats.energy, player_stats.hp, player_stats.max_hp, player_stats.exp, player_stats.max_exp 
FROM players 
LEFT JOIN player_stats ON players.id = player_stats.player_id 
WHERE players.id = :id");
$stmt->execute([':id' => $userId]);
$player = $stmt->fetch(PDO::FETCH_ASSOC);

// Sprawdzenie, czy dane zostały zwrócone
if (!$player) {
    die("Błąd: Nie znaleziono danych gracza.");
}

// Przypisanie danych gracza do zmiennych
$id = intval($player['id']);
$username = htmlspecialchars($player['username']);
$level = intval($player['level']);
$gold = intval($player['gold']);
$silver = intval($player['silver']);
$energy = intval($player['energy']);
$hp = intval($player['hp']);
$maxHp = intval($player['max_hp']);
$exp = intval($player['exp']);
$maxExp = intval($player['max_exp']);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apolonia - Główna Strona Gry</title>
    <link rel="stylesheet" href="../css/gamestyle.css">
</head>
<body>
    <div class="game-container">
        <!-- Pasek nawigacyjny -->
        <nav class="game-nav">
            <ul>
                <li><a href="game.php">Strona główna</a></li>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="chat.php">Chat</a></li>
                <li><a href="ranking.php">Ranking</a></li>
                <li><a href="player_config.php">Ustawienia</a></li>
                <li><a href="logout.php">Wyloguj</a></li>
            </ul>
        </nav>

        <!-- Sekcja główna gry -->
        <main class="game-main">
            <div class="profile-info">
                <h2><?php echo $username; ?> (ID: <?php echo $id; ?>)</h2>
                <p><strong>Poziom:</strong> <?php echo $level; ?></p>
                <p><strong>Złoto:</strong> <?php echo $gold; ?></p>
                <p><strong>Srebro:</strong> <?php echo $silver; ?></p>

                <!-- Pasek zdrowia -->
                <div class="progress-bar">
                    <label for="hp">Zdrowie:</label>
                    <div class="progress">
                        <div class="progress-fill health" style="width: <?php echo ($hp / $maxHp) * 100; ?>%;"></div>
                    </div>
                    <span><?php echo $hp; ?> / <?php echo $maxHp; ?></span>
                </div>

                <!-- Pasek energii -->
                <div class="progress-bar">
                    <label for="energy">Energia:</label>
                    <div class="progress">
                        <div class="progress-fill energy" style="width: <?php echo $energy; ?>%;"></div>
                    </div>
                    <span><?php echo $energy; ?>%</span>
                </div>

                <!-- Pasek doświadczenia -->
                <div class="progress-bar">
                    <label for="exp">Doświadczenie:</label>
                    <div class="progress">
                        <div class="progress-fill experience" style="width: <?php echo ($exp / $maxExp) * 100; ?>%;"></div>
                    </div>
                    <span><?php echo $exp; ?> / <?php echo $maxExp; ?></span>
                </div>
            </div>
        </main>
    </div>

    <!-- Dodanie listy graczy online -->
    <?php include 'online_players.php'; ?>
</body>
</html>
