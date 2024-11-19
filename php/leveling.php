<?php
session_start();
require_once '../includes/config.php'; // Ładowanie konfiguracji bazy danych
require_once '../includes/database.php'; // Ładowanie połączenia z bazą danych

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Pobranie połączenia z bazy danych
$db = Database::getInstance()->getConnection();
$userId = $_SESSION['user_id'];

// Funkcja do zwiększania poziomu i przydzielania punktów atrybutów
function levelUp($userId, $db) {
    // Pobierz aktualny poziom gracza
    $stmt = $db->prepare("SELECT level FROM player_stats WHERE player_id = :id");
    $stmt->execute([':id' => $userId]);
    $playerStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentLevel = intval($playerStats['level']);

    // Zwiększ poziom
    $newLevel = $currentLevel + 1;

    // Liczba przyznanych punktów za poziom
    $additionalPoints = 2;

    // Zaktualizuj poziom gracza
    $stmt = $db->prepare("UPDATE player_stats SET level = :level WHERE player_id = :id");
    $stmt->execute([':level' => $newLevel, ':id' => $userId]);

    // Pobierz aktualną liczbę punktów atrybutów
    $attrStmt = $db->prepare("SELECT attribute_points FROM player_attributes WHERE player_id = :id");
    $attrStmt->execute([':id' => $userId]);
    $attributes = $attrStmt->fetch(PDO::FETCH_ASSOC);
    $currentPoints = intval($attributes['attribute_points']);

    // Zwiększ liczbę punktów atrybutów
    $newPoints = $currentPoints + $additionalPoints;
    $updateAttrStmt = $db->prepare("UPDATE player_attributes SET attribute_points = :points WHERE player_id = :id");
    $updateAttrStmt->execute([':points' => $newPoints, ':id' => $userId]);

    echo "Gratulacje! Awansowałeś na poziom $newLevel. Otrzymałeś $additionalPoints punkty atrybutów.";
}

// Przykład użycia funkcji (wywołaj funkcję, gdy gracz zdobywa wystarczająco dużo doświadczenia)
levelUp($userId, $db);
?>
