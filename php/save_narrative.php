<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$userId = $_SESSION['user_id'];

// Obsługa formularza fabularnego
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sprawdzenie, czy dane POST są ustawione i nie są puste
    if (!isset($_POST['appearance'], $_POST['personality'])) {
        die("Błąd: Brak wymaganych danych.");
    }

    // Sanityzacja danych wejściowych
    $appearance = htmlspecialchars(trim($_POST['appearance']), ENT_QUOTES, 'UTF-8');
    $personality = htmlspecialchars(trim($_POST['personality']), ENT_QUOTES, 'UTF-8');

    // Walidacja danych fabularnych
    if (strlen($appearance) > 1000 || strlen($personality) > 1000) {
        die("Błąd: Opis wyglądu lub charakteru jest zbyt długi.");
    }

    // Zapis danych fabularnych do bazy danych
    $updateNarrativeStmt = $db->prepare("INSERT INTO player_narrative (player_id, appearance, personality) 
        VALUES (:id, :appearance, :personality) 
        ON DUPLICATE KEY UPDATE appearance = :appearance, personality = :personality");

    $result = $updateNarrativeStmt->execute([
        ':id' => $userId,
        ':appearance' => $appearance,
        ':personality' => $personality
    ]);

    if ($result) {
        // Przeładuj stronę profilu
        header("Location: profile.php");
        exit;
    } else {
        // Diagnostyka błędów SQL
        $errorInfo = $updateNarrativeStmt->errorInfo();
        file_put_contents('debug.log', print_r($errorInfo, true), FILE_APPEND); // Zapis błędów do pliku debug.log
        die("Wystąpił błąd podczas zapisywania danych: " . $errorInfo[2]);
    }
}
?>
