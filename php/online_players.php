<?php
session_start();
require_once '../includes/database.php';

// Pobranie połączenia z bazą danych
$db = Database::getInstance()->getConnection();

// Pobieranie liczby graczy online (aktywnych w ciągu ostatnich 5 minut)
$stmt = $db->prepare("SELECT username FROM players WHERE last_activity >= (NOW() - INTERVAL 5 MINUTE)");
$stmt->execute();
$onlinePlayers = $stmt->fetchAll(PDO::FETCH_ASSOC);
$onlineCount = count($onlinePlayers); // Liczba graczy online

// Pobieranie całkowitej liczby graczy z bazy danych
$stmt = $db->prepare("SELECT COUNT(*) AS total_players FROM players");
$stmt->execute();
$totalPlayers = $stmt->fetch(PDO::FETCH_ASSOC)['total_players'];

// Obliczenie liczby graczy offline
$offlineCount = $totalPlayers - $onlineCount;
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracze Online - Apolonia</title>
    <link rel="stylesheet" href="../css/gamestyle.css">
    <style>
        /* Stylizacja głównego kontenera dla listy graczy online */
        .online-players-container {
            background-color: #1a1a2e;
            color: #e0e0e0;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.7);
            width: 250px;
            position: fixed;
            top: 50px;
            right: 20px;
            z-index: 1000;
        }

        /* Stylizacja nagłówka listy */
        .online-players-container h2 {
            text-align: center;
            font-size: 20px;
            color: #ffcc00;
            border-bottom: 1px solid #444;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        /* Stylizacja liczników graczy */
        .player-count {
            text-align: center;
            margin-bottom: 15px;
        }

        /* Stylizacja listy graczy online */
        .online-players-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        /* Stylizacja każdego elementu listy (gracza online) */
        .online-players-list li {
            background-color: #2a2a3d;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            color: #ffffff;
            font-size: 16px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
        }

        /* Efekt hover (po najechaniu myszką) dla elementu listy */
        .online-players-list li:hover {
            background-color: #3e3e5e;
        }

        /* Stylizacja ikony statusu online (zielona kropka) */
        .online-players-list li::before {
            content: "🟢";
            margin-right: 10px;
            font-size: 12px;
            color: #00ff00;
        }
    </style>
</head>
<body>
    <div class="online-players-container">
        <h2>Gracze Online</h2>
        <div class="player-count">
            <p>Online: <?= $onlineCount ?> | Offline: <?= $offlineCount ?></p>
        </div>
        <ul class="online-players-list">
            <?php if (empty($onlinePlayers)): ?>
                <li>Brak graczy online.</li>
            <?php else: ?>
                <?php foreach ($onlinePlayers as $player): ?>
                    <li><span><?= htmlspecialchars($player['username']) ?></span></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>
