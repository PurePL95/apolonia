<?php
session_start();
require_once '../includes/database.php';
include 'activity_tracker.php';

// Włączenie wyświetlania błędów dla celów debugowania
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$userId = $_SESSION['user_id'];

// Obsługa formularza ustawień konta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Zmiana e-maila (tylko jeśli pole zostało wypełnione)
    if (!empty($_POST['email'])) {
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $errors[] = "Nieprawidłowy adres e-mail.";
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM players WHERE email = :email AND id != :id");
            $stmt->execute([':email' => $email, ':id' => $userId]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Ten adres e-mail jest już używany.";
            } else {
                $stmt = $db->prepare("UPDATE players SET email = :email WHERE id = :id");
                $stmt->execute([':email' => $email, ':id' => $userId]);
            }
        }
    }

    // Zmiana hasła (tylko jeśli oba pola zostały wypełnione)
    if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
        $currentPassword = trim($_POST['current_password']);
        $newPassword = trim($_POST['new_password']);

        $stmt = $db->prepare("SELECT password FROM players WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $hashedPassword = $stmt->fetchColumn();

        if (!password_verify($currentPassword, $hashedPassword)) {
            $errors[] = "Bieżące hasło jest nieprawidłowe.";
        } elseif (strlen($newPassword) < 8) {
            $errors[] = "Nowe hasło musi mieć co najmniej 8 znaków.";
        } else {
            $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE players SET password = :password WHERE id = :id");
            $stmt->execute([':password' => $newHashedPassword, ':id' => $userId]);
        }
    }

    // Zmiana pseudonimu (tylko jeśli pole zostało wypełnione)
    if (!empty($_POST['nickname'])) {
        $nickname = htmlspecialchars(trim($_POST['nickname']));
        if (strlen($nickname) < 3 || strlen($nickname) > 20) {
            $errors[] = "Pseudonim musi mieć od 3 do 20 znaków.";
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM players WHERE username = :username AND id != :id");
            $stmt->execute([':username' => $nickname, ':id' => $userId]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Ten pseudonim jest już zajęty.";
            } else {
                $stmt = $db->prepare("UPDATE players SET username = :username WHERE id = :id");
                $stmt->execute([':username' => $nickname, ':id' => $userId]);
            }
        }
    }

    if (empty($errors)) {
        $_SESSION['success'] = "Zmiany zostały zapisane pomyślnie.";
    } else {
        $_SESSION['errors'] = $errors;
    }

    header('Location: player_config.php');
    exit;
}

// Pobranie aktualnych danych użytkownika
$stmt = $db->prepare("SELECT username, email FROM players WHERE id = :id");
$stmt->execute([':id' => $userId]);
$player = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ustawienia Konta - Apolonia</title>
    <link rel="stylesheet" href="../css/gamestyle.css">
    <style>
        /* Ulepszenia stylu dla kontenera ustawień */
        .settings-container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #1a1a2e;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
            color: #e0e0e0;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #ffcc00;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            padding: 10px;
            border-radius: 5px;
            border: none;
            background-color: #2a2a3d;
            color: #e0e0e0;
        }

        input:focus {
            outline: none;
            background-color: #33334d;
        }

        .success-message {
            color: #4CAF50;
            background-color: #2e7d32;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .error-message {
            color: #f44336;
            background-color: #c62828;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        button {
            padding: 10px;
            background-color: #ffcc00;
            color: #1a1a2e;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #e6b800;
        }
    </style>
</head>
<body>
    <div class="game-container">
        <nav class="game-nav">
            <ul>
                <li><a href="game.php">Strona główna</a></li>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="chat.php">Chat</a></li>
                <li><a href="ranking.php">Ranking</a></li>
                <li><a href="ustawienia.php">Ustawienia</a></li>
                <li><a href="logout.php">Wyloguj</a></li>
            </ul>
        </nav>

        <div class="settings-container">
            <h2>Ustawienia Konta</h2>

            <?php
            if (isset($_SESSION['success'])) {
                echo "<p class='success-message'>{$_SESSION['success']}</p>";
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['errors'])) {
                foreach ($_SESSION['errors'] as $error) {
                    echo "<p class='error-message'>$error</p>";
                }
                unset($_SESSION['errors']);
            }
            ?>

            <form method="POST" action="player_config.php">
                <label for="email">Zmień e-mail (opcjonalnie):</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($player['email']) ?>">

                <label for="current_password">Bieżące hasło (opcjonalnie):</label>
                <input type="password" id="current_password" name="current_password">

                <label for="new_password">Nowe hasło (opcjonalnie):</label>
                <input type="password" id="new_password" name="new_password">

                <label for="nickname">Zmień pseudonim (opcjonalnie):</label>
                <input type="text" id="nickname" name="nickname" value="<?= htmlspecialchars($player['username']) ?>">

                <button type="submit">Zapisz Zmiany</button>
            </form>
        </div>
    </div>
</body>
</html>
