<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
include 'activity_tracker.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$userId = $_SESSION['user_id'];

// Pobieranie nazwy użytkownika
$userStmt = $db->prepare("SELECT username FROM players WHERE id = :id");
$userStmt->execute([':id' => $userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
$username = htmlspecialchars($user['username']);

// Obsługa wysyłania wiadomości
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = htmlspecialchars(trim($_POST['message']));
    if (!empty($message)) {
        $stmt = $db->prepare("INSERT INTO chat (user_id, username, message, created_at) VALUES (:user_id, :username, :message, NOW())");
        $stmt->execute([
            ':user_id' => $userId,
            ':username' => $username,
            ':message' => $message
        ]);
    }
}

// Pobieranie ostatnich wiadomości z czatu
$chatStmt = $db->prepare("SELECT username, message, created_at FROM chat ORDER BY created_at ASC LIMIT 20");
$chatStmt->execute();
$messages = $chatStmt->fetchAll(PDO::FETCH_ASSOC);

if (!$messages) {
    $messages = [];
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apolonia - Chat</title>
    <link rel="stylesheet" href="../css/gamestyle.css">
    <style>
        /* Stylizacja kontenera czatu */
        .chat-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #1a1a2e;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
            color: #e0e0e0;
        }

        .chat-messages {
            height: 300px;
            overflow-y: auto;
            background-color: #2a2a3d;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: flex;
            flex-direction: column; /* Starsze wiadomości na górze, nowe na dole */
        }

        .chat-message {
            margin-bottom: 10px;
        }

        .chat-message strong {
            color: #ffcc00;
        }

        .chat-timestamp {
            font-size: 0.8em;
            color: #888;
        }

        .chat-form {
            display: flex;
            gap: 10px;
        }

        .chat-form input[type="text"] {
            flex: 1;
            padding: 10px;
            border-radius: 5px;
            border: none;
            background-color: #2a2a3d;
            color: #e0e0e0;
        }

        .chat-form button {
            padding: 10px;
            background-color: #ffcc00;
            color: #1a1a2e;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .chat-form button:hover {
            background-color: #e6b800;
        }
    </style>
    <script>
        // Automatyczne przewijanie czatu do najnowszych wiadomości
        function scrollChatToBottom() {
            const chatMessages = document.querySelector('.chat-messages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Funkcja do odświeżania wiadomości co 5 sekund
        function refreshChat() {
            fetch('load_chat.php')
                .then(response => response.text())
                .then(data => {
                    document.querySelector('.chat-messages').innerHTML = data;
                    scrollChatToBottom();
                })
                .catch(error => console.error('Błąd podczas odświeżania czatu:', error));
        }

        // Odświeżanie wiadomości co 5 sekund
        setInterval(refreshChat, 5000);

        // Przewinięcie czatu przy załadowaniu strony
        window.onload = scrollChatToBottom;
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

        <!-- Sekcja czatu -->
        <div class="chat-container">
            <h2>Chat Karczmy</h2>
            <div class="chat-messages">
                <?php foreach ($messages as $message): ?>
                    <div class="chat-message">
                        <strong><?php echo htmlspecialchars($message['username']); ?>:</strong>
                        <span><?php echo htmlspecialchars($message['message']); ?></span>
                        <div class="chat-timestamp"><?php echo date('H:i', strtotime($message['created_at'])); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Formularz wysyłania wiadomości -->
            <form method="post" action="chat.php" class="chat-form">
                <input type="text" name="message" placeholder="Wpisz swoją wiadomość..." maxlength="255" required>
                <button type="submit">Wyślij</button>
            </form>
        </div>
    </div>

    <!-- Dodanie listy graczy online -->
    <?php include 'online_players.php'; ?>
</body>
</html>
