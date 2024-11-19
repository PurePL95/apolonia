<?php
session_start();
require_once '../includes/database.php';

// Sprawdzenie, czy sesja użytkownika istnieje
if (isset($_SESSION['user_id'])) {
    // Usunięcie użytkownika z listy aktywnych sesji
    if (isset($_SESSION['online_users'])) {
        unset($_SESSION['online_users'][$_SESSION['user_id']]);
    }

    // Aktualizacja czasu ostatniego logowania w bazie danych
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE players SET last_login = NOW() WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
}

// Zniszczenie sesji
session_unset();
session_destroy();

// Przekierowanie na stronę główną
header('Location: http://a-polonia.pl/index.html');
exit;
?>
