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

// Pobieranie atrybutów gracza
$attrStmt = $db->prepare("SELECT strength, endurance, agility, perception, intelligence, luck, charisma, attribute_points FROM player_attributes WHERE player_id = :id");
$attrStmt->execute([':id' => $userId]);
$attributes = $attrStmt->fetch(PDO::FETCH_ASSOC);

// Przypisanie atrybutów do zmiennych
$strength = intval($attributes['strength']);
$endurance = intval($attributes['endurance']);
$agility = intval($attributes['agility']);
$perception = intval($attributes['perception']);
$intelligence = intval($attributes['intelligence']);
$luck = intval($attributes['luck']);
$charisma = intval($attributes['charisma']);
$attributePoints = intval($attributes['attribute_points']);
?>

<div class="attributes">
    <h3>Atrybuty</h3>
    <p><strong>Siła:</strong> <?php echo $strength; ?></p>
    <p><strong>Wytrzymałość:</strong> <?php echo $endurance; ?></p>
    <p><strong>Zręczność:</strong> <?php echo $agility; ?></p>
    <p><strong>Percepcja:</strong> <?php echo $perception; ?></p>
    <p><strong>Inteligencja:</strong> <?php echo $intelligence; ?></p>
    <p><strong>Szczęście:</strong> <?php echo $luck; ?></p>
    <p><strong>Charyzma:</strong> <?php echo $charisma; ?></p>
    <p><strong>Punkty do rozdania:</strong> <?php echo $attributePoints; ?></p>
</div>
