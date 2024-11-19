<?php
require_once '../includes/config.php';
require_once '../includes/database.php';

$db = Database::getInstance()->getConnection();

// Pobieranie ostatnich wiadomości z czatu
$chatStmt = $db->prepare("SELECT username, message, created_at FROM chat ORDER BY created_at DESC LIMIT 20");
$chatStmt->execute();
$messages = $chatStmt->fetchAll(PDO::FETCH_ASSOC);

if ($messages) {
    foreach (array_reverse($messages) as $message) {
        echo "<div class='chat-message'>";
        echo "<strong>" . htmlspecialchars($message['username']) . ":</strong> ";
        echo "<span>" . htmlspecialchars($message['message']) . "</span>";
        echo "<div class='chat-timestamp'>" . date('H:i', strtotime($message['created_at'])) . "</div>";
        echo "</div>";
    }
}
?>
