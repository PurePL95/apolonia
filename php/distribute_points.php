<?php
require_once '../includes/config.php';
require_once '../includes/database.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$userId = $_SESSION['user_id'];

// Pobieranie dostępnych punktów do rozdania
$attrStmt = $db->prepare("SELECT strength, endurance, agility, perception, intelligence, luck, charisma, attribute_points FROM player_attributes WHERE player_id = :id");
$attrStmt->execute([':id' => $userId]);
$attributes = $attrStmt->fetch(PDO::FETCH_ASSOC);

if (!$attributes) {
    die("Błąd: Nie udało się pobrać atrybutów gracza.");
}

$attributePoints = intval($attributes['attribute_points']);

// Obsługa formularza rozdawania punktów
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['distribute_points'])) {
    // Pobierz wartości z formularza
    $strengthIncrease = intval($_POST['strength']);
    $enduranceIncrease = intval($_POST['endurance']);
    $agilityIncrease = intval($_POST['agility']);
    $perceptionIncrease = intval($_POST['perception']);
    $intelligenceIncrease = intval($_POST['intelligence']);
    $luckIncrease = intval($_POST['luck']);
    $charismaIncrease = intval($_POST['charisma']);

    // Suma przydzielonych punktów
    $totalPoints = $strengthIncrease + $enduranceIncrease + $agilityIncrease + $perceptionIncrease + $intelligenceIncrease + $luckIncrease + $charismaIncrease;

    // Sprawdzenie, czy gracz nie przekracza dostępnych punktów
    if ($totalPoints > $attributePoints) {
        die("Błąd: Próbujesz rozdać więcej punktów niż masz dostępnych.");
    }

    // Zaktualizuj atrybuty w bazie danych
    $updateStmt = $db->prepare("UPDATE player_attributes SET 
        strength = strength + :strength, 
        endurance = endurance + :endurance, 
        agility = agility + :agility, 
        perception = perception + :perception, 
        intelligence = intelligence + :intelligence, 
        luck = luck + :luck, 
        charisma = charisma + :charisma, 
        attribute_points = attribute_points - :usedPoints 
        WHERE player_id = :id");
    
    $updateStmt->execute([
        ':strength' => $strengthIncrease,
        ':endurance' => $enduranceIncrease,
        ':agility' => $agilityIncrease,
        ':perception' => $perceptionIncrease,
        ':intelligence' => $intelligenceIncrease,
        ':luck' => $luckIncrease,
        ':charisma' => $charismaIncrease,
        ':usedPoints' => $totalPoints,
        ':id' => $userId
    ]);

    // Przeładuj stronę, aby zaktualizować dane
    header("Location: profile.php");
    exit;
}
?>
