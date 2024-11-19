<?php
session_start();
require_once '../includes/database.php'; // Upewnij się, że ścieżka jest poprawna

// Sprawdzenie, czy użytkownik jest zalogowany
if (isset($_SESSION['user_id'])) {
    // Aktualizacja czasu ostatniej aktywności w bazie danych
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE players SET last_activity = NOW() WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
}
?>
