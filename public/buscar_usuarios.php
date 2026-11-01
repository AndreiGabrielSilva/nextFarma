<?php
session_start();
if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'mestre') {
    exit(json_encode(['success' => false]));
}

try {
    $db = new PDO('sqlite:' . __DIR__ . '/databaselogin.db');
    $stmt = $db->query("SELECT usuario, nivel FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($usuarios);
} catch (Exception $e) {
    echo json_encode([]);
}