<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]); exit;
}

try {
    $db = new PDO('sqlite:' . __DIR__ . '/database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $cpfBusca = $_GET['cpf'] ?? '';
    // Remove pontos e traços do que o usuário digitou
    $cpfLimpo = preg_replace('/\D/', '', $cpfBusca);

    if (empty($cpfLimpo)) {
        echo json_encode([]); exit;
    }

    // A mágica: usamos REPLACE no SQL para comparar apenas os números do banco com os números digitados
    $sql = "SELECT * FROM dispensacoes 
            WHERE REPLACE(REPLACE(CPF_PACIENTE, '.', ''), '-', '') LIKE :cpf 
            ORDER BY ID DESC";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([':cpf' => "%$cpfLimpo%"]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultados);

} catch (PDOException $e) {
    echo json_encode(['erro' => $e->getMessage()]);
}