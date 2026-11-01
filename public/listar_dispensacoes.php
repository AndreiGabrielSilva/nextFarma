<?php
session_start();
header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:' . __DIR__ . '/database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // O '*' garante que pegamos DATA_REGISTRO, CODIGO_REGISTRO, etc.
    $stmt = $db->query("SELECT * FROM dispensacoes ORDER BY ID DESC");
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultados);
} catch (PDOException $e) {
    echo json_encode(['erro' => $e->getMessage()]);
}