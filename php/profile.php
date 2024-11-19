<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
include '../activity_tracker.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Pobranie połączenia z bazą danych
$db = Database::getInstance()->getConnection();
$userId = $_SESSION['user_id'];

// Pobieranie danych gracza
$stmt = $db->prepare("SELECT players.username, players.email, player_stats.level, player_stats.gold, player_stats.silver, player_stats.energy 
                      FROM players 
                      JOIN player_stats ON players.id = player_stats.player_id 
                      WHERE players.id = :id");
$stmt->execute([':id' => $userId]);
$player = $stmt->fetch(PDO::FETCH_ASSOC);

$username = htmlspecialchars($player['username']);
$email = htmlspecialchars($player['email']);
$level = intval($player['level']);
$gold = intval($player['gold']);
$silver = intval($player['silver']);
$energy = intval($player['energy']);

// Pobieranie atrybutów gracza
$attrStmt = $db->prepare("SELECT strength, endurance, agility, perception, intelligence, luck, charisma, attribute_points 
                          FROM player_attributes 
                          WHERE player_id = :id");
$attrStmt->execute([':id' => $userId]);
$attributes = $attrStmt->fetch(PDO::FETCH_ASSOC);

$strength = intval($attributes['strength']);
$endurance = intval($attributes['endurance']);
$agility = intval($attributes['agility']);
$perception = intval($attributes['perception']);
$intelligence = intval($attributes['intelligence']);
$luck = intval($attributes['luck']);
$charisma = intval($attributes['charisma']);
$attributePoints = intval($attributes['attribute_points']);

// Pobieranie danych fabularnych gracza
$narrativeStmt = $db->prepare("SELECT appearance, personality FROM player_narrative WHERE player_id = :id");
$narrativeStmt->execute([':id' => $userId]);
$narrative = $narrativeStmt->fetch(PDO::FETCH_ASSOC);

$appearance = htmlspecialchars($narrative['appearance'] ?? '');
$personality = htmlspecialchars($narrative['personality'] ?? '');
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Gracza - Apolonia</title>
    <link rel="stylesheet" href="../css/gamestyle.css">
    <style>
        /* Stylizacja głównego kontenera profilu */
        .profile-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #1a1a2e;
            color: #e0e0e0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }

        /* Stylizacja nagłówka */
        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-header h2 {
            font-size: 28px;
            color: #ffcc00;
        }

        /* Stylizacja sekcji profilu */
        .profile-sections {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Stylizacja danych mechanicznych */
        .mechanical-profile, .narrative-profile {
            background-color: #2a2a3d;
            border-radius: 8px;
            padding: 15px;
        }

        /* Stylizacja atrybutów */
        .attribute {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #444;
        }

        .attribute:last-child {
            border-bottom: none;
        }

        .attribute-icon {
            font-size: 24px;
            margin-right: 10px;
            color: #ffcc00;
        }

        /* Stylizacja formularza */
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        textarea {
            border-radius: 5px;
            padding: 10px;
            background-color: #1e1e2e;
            color: #fff;
            border: none;
            resize: vertical;
        }

        button {
            background-color: #ffcc00;
            color: #1a1a2e;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #e6b800;
        }

        /* Stylizacja przełączników sekcji */
        .section-toggle {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .section-toggle button {
            padding: 10px 20px;
        }
    </style>
    <script>
        // Funkcja do przełączania widoczności sekcji
        function showSection(sectionId) {
            document.getElementById('mechanical-profile').style.display = 'none';
            document.getElementById('narrative-profile').style.display = 'none';
            document.getElementById(sectionId).style.display = 'block';
        }

        // Funkcja do przełączania trybu edycji danych fabularnych
        function toggleEditMode() {
            const appearanceText = document.getElementById('appearance-text');
            const appearanceInput = document.getElementById('appearance-input');
            const personalityText = document.getElementById('personality-text');
            const personalityInput = document.getElementById('personality-input');
            const editButton = document.getElementById('edit-button');
            const saveButton = document.getElementById('save-button');

            if (appearanceInput.style.display === 'none') {
                appearanceText.style.display = 'none';
                appearanceInput.style.display = 'block';
                personalityText.style.display = 'none';
                personalityInput.style.display = 'block';
                editButton.style.display = 'none';
                saveButton.style.display = 'block';
            } else {
                appearanceText.style.display = 'block';
                appearanceInput.style.display = 'none';
                personalityText.style.display = 'block';
                personalityInput.style.display = 'none';
                editButton.style.display = 'block';
                saveButton.style.display = 'none';
            }
        }

        // Funkcja AJAX do zapisywania danych fabularnych
        function saveNarrative() {
            const appearanceInput = document.getElementById('appearance-input');
            const personalityInput = document.getElementById('personality-input');
            const appearanceText = document.getElementById('appearance-text');
            const personalityText = document.getElementById('personality-text');

            // Aktualizacja wyświetlanego tekstu
            appearanceText.textContent = appearanceInput.value;
            personalityText.textContent = personalityInput.value;

            toggleEditMode();

            // AJAX do zapisywania danych
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "save_narrative.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            const params = "appearance=" + encodeURIComponent(appearanceInput.value) + 
                           "&personality=" + encodeURIComponent(personalityInput.value);

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log("Dane zostały zapisane!");
                }
            };

            xhr.send(params);
        }
    </script>
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

        <!-- Profil gracza -->
        <div class="profile-container">
            <div class="profile-header">
                <h2>Profil Gracza: <?= $username ?></h2>
                <p>Email: <?= $email ?></p>
            </div>

            <div class="profile-sections">
                <!-- Mechaniczne dane gracza -->
                <div id="mechanical-profile" class="mechanical-profile">
                    <div class="attribute"><span class="attribute-icon">🏆</span><strong>Poziom:</strong> <?= $level ?></div>
                    <div class="attribute"><span class="attribute-icon">💰</span><strong>Złoto:</strong> <?= $gold ?></div>
                    <div class="attribute"><span class="attribute-icon">🪙</span><strong>Srebro:</strong> <?= $silver ?></div>
                    <div class="attribute"><span class="attribute-icon">⚡</span><strong>Energia:</strong> <?= $energy ?>%</div>
                    <h3>Atrybuty</h3>
                    <div class="attribute"><span class="attribute-icon">💪</span><strong>Siła:</strong> <?= $strength ?></div>
                    <div class="attribute"><span class="attribute-icon">🛡️</span><strong>Wytrzymałość:</strong> <?= $endurance ?></div>
                    <div class="attribute"><span class="attribute-icon">🏃</span><strong>Zwinność:</strong> <?= $agility ?></div>
                    <div class="attribute"><span class="attribute-icon">👁️</span><strong>Spostrzegawczość:</strong> <?= $perception ?></div>
                    <div class="attribute"><span class="attribute-icon">🧠</span><strong>Inteligencja:</strong> <?= $intelligence ?></div>
                    <div class="attribute"><span class="attribute-icon">🍀</span><strong>Szczęście:</strong> <?= $luck ?></div>
                    <div class="attribute"><span class="attribute-icon">💬</span><strong>Charyzma:</strong> <?= $charisma ?></div>
                    <div class="attribute"><span class="attribute-icon">✨</span><strong>Punkty atrybutów:</strong> <?= $attributePoints ?></div>
                </div>

                <!-- Dane fabularne gracza -->
                <div id="narrative-profile" class="narrative-profile" style="display: none;">
                    <!-- Opis wyglądu -->
                    <div class="narrative-section">
                        <p><strong>Opis wyglądu:</strong> <span id="appearance-text"><?= $appearance ?></span></p>
                        <textarea id="appearance-input" name="appearance" rows="5" maxlength="1000" style="display: none;"><?= $appearance ?></textarea>
                    </div>

                    <!-- Opis charakteru -->
                    <div class="narrative-section">
                        <p><strong>Opis charakteru:</strong> <span id="personality-text"><?= $personality ?></span></p>
                        <textarea id="personality-input" name="personality" rows="5" maxlength="1000" style="display: none;"><?= $personality ?></textarea>
                    </div>

                    <!-- Przycisk Edytuj i Zapisz -->
                    <button id="edit-button" onclick="toggleEditMode()">Edytuj</button>
                    <button id="save-button" type="button" onclick="saveNarrative()" style="display: none;">Zapisz</button>
                </div>
            </div>

            <!-- Przełączniki sekcji -->
            <div class="section-toggle">
                <button onclick="showSection('mechanical-profile')">Dane Mechaniczne</button>
                <button onclick="showSection('narrative-profile')">Dane Fabularne</button>
            </div>
        </div>
    </div>

    <!-- Dodanie listy graczy online -->
    <?php include 'online_players.php'; ?>
</body>
</html>
