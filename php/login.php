<?php
session_start();
require_once '../includes/database.php';

// Pobranie połączenia z bazy danych
$db = Database::getInstance()->getConnection();

// Pobieranie danych z formularza
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) : null;
$password = isset($_POST['pass']) ? trim($_POST['pass']) : null;

// Sprawdzenie, czy pola są wypełnione
if (!$email || !$password) {
    echo "Wszystkie pola muszą być wypełnione.";
    exit;
}

// Pobieranie użytkownika z bazy danych
$stmt = $db->prepare("SELECT id, username, pass FROM players WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Sprawdzenie, czy użytkownik istnieje i hasło jest poprawne
if ($user && password_verify($password, $user['pass'])) {
    // Logowanie użytkownika
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    // Dodanie użytkownika do listy aktywnych użytkowników
    if (!isset($_SESSION['online_users'])) {
        $_SESSION['online_users'] = [];
    }
    $_SESSION['online_users'][$user['id']] = $user['username'];

    header('Location: game.php');
    exit;
} else {
    echo "Nieprawidłowy e-mail lub hasło.";
}
?>
