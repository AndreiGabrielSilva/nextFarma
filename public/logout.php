<?php
session_start();
session_destroy(); // Mata todas as variáveis salvas
header("Location: index.php"); // Manda de volta para o login
exit;
?>