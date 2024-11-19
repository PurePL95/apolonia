<?php
session_start();

require_once '../includes/database.php'; // Poprawna ścieżka do database.php

// Zainicjalizowanie połączenia z bazą danych
$db = Database::getInstance()->getConnection();
if (!$db) {
    die("Nie udało się połączyć z bazą danych.");
}

// Sprawdzanie, czy formularz został wysłany
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobieranie i walidacja danych wejściowych
    $username = htmlspecialchars(strip_tags(trim($_POST['user'])));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $confirmEmail = filter_var(trim($_POST['vemail']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['pass']);

    // Sprawdzenie, czy wszystkie pola zostały wypełnione
    if (!$username || !$email || !$confirmEmail || !$password) {
        echo "Wszystkie pola muszą być wypełnione.";
        exit;
    }

    // Sprawdzenie, czy adresy e-mail się zgadzają
    if ($email !== $confirmEmail) {
        echo "Adresy e-mail muszą być identyczne.";
        exit;
    }

    // Sprawdzenie, czy adres e-mail jest już w użyciu
    $stmt = $db->prepare("SELECT COUNT(*) FROM players WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        echo "Ten adres e-mail jest już używany.";
        exit;
    }

    // Sprawdzenie, czy nazwa użytkownika jest już zajęta
    $stmt = $db->prepare("SELECT COUNT(*) FROM players WHERE username = :username");
    $stmt->execute([':username' => $username]);
    if ($stmt->fetchColumn() > 0) {
        echo "Ta nazwa użytkownika jest już zajęta.";
        exit;
    }

    // Hashowanie hasła
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Rozpoczęcie transakcji, aby zapewnić spójność danych
    try {
        $db->beginTransaction();

        // Wstawianie danych użytkownika do tabeli players
        $stmt = $db->prepare("INSERT INTO players (username, email, pass, created_at) VALUES (:username, :email, :pass, NOW())");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':pass' => $hashedPassword
        ]);

        // Pobranie ID nowo utworzonego użytkownika
        $userId = $db->lastInsertId();

        // Wstawianie domyślnych statystyk do tabeli player_stats
        $stmt = $db->prepare("INSERT INTO player_stats (player_id, level, hp, energy, gold, silver) VALUES (:player_id, 1, 100, 100, 50, 20)");
        $stmt->execute([
            ':player_id' => $userId
        ]);

        // Wstawianie domyślnych atrybutów do tabeli player_attributes
        $stmt = $db->prepare("INSERT INTO player_attributes (player_id, strength, endurance, agility, perception, intelligence, luck, charisma, attribute_points) VALUES (:player_id, 5, 5, 5, 5, 5, 5, 5, 10)");
        $stmt->execute([
            ':player_id' => $userId
        ]);

        // Zatwierdzenie transakcji
        $db->commit();

        // Przekierowanie po pomyślnej rejestracji
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        header('Location: game.php');
        exit;
    } catch (Exception $e) {
        // W przypadku błędu, cofnięcie transakcji
        $db->rollBack();
        die("Błąd podczas rejestracji: " . $e->getMessage());
    }
} else {
    echo "Nieprawidłowa metoda żądania.";
}
?>
