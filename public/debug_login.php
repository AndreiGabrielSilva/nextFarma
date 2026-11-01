<?php
$db = new PDO('sqlite:' . __DIR__ . '/databaselogin.db');
$stmt = $db->query("SELECT usuario, senha FROM usuarios");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($users);
echo "</pre>";
?>