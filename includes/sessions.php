<?php
session_start();

// Zakończenie sesji i wylogowanie użytkownika
session_unset(); // Usunięcie wszystkich zmiennych sesji
session_destroy(); // Zniszczenie sesji

header('Location: www.a-polonia.pl'); // Przekierowanie na stronę główną
exit;
?>
