<?php
session_start();
header('Content-Type: application/json');

// 1. Verificação de Segurança
if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'mestre') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

try {
    $db = new PDO('sqlite:' . __DIR__ . '/databaselogin.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $nome  = $_POST['nome'] ?? '';
    $cpf   = $_POST['cpf'] ?? '';
    // Limpar o CPF (remover pontos e traços) se necessário para evitar duplicidade por formatação
    $cpf = preg_replace('/[^0-9]/', '', $cpf); 
    $role  = $_POST['role'] ?? 'farmaceutico';

    if (empty($nome) || empty($cpf)) {
        echo json_encode(['success' => false, 'message' => 'Nome e CPF são obrigatórios.']);
        exit;
    }

    // 2. Gerar senha provisória
    $senhaProvisoria = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);
    $senhaHash = password_hash($senhaProvisoria, PASSWORD_DEFAULT);

    // 3. Inserir no banco
    $stmt = $db->prepare("INSERT INTO usuarios (usuario, senha, nivel) VALUES (:cpf, :senha, :nivel)");
    
    // ATENÇÃO: Definindo a variável $executou aqui
    $executou = $stmt->execute([
        ':cpf'   => $cpf,
        ':senha' => $senhaHash,
        ':nivel' => $role
    ]);

    if ($executou) {
        echo json_encode([
            'success' => true, 
            'senha' => $senhaProvisoria,
            'message' => 'Usuário criado com sucesso!'
        ]);
    }

} catch (PDOException $e) {
    // Código 23000 no SQLite indica violação de integridade (Unique constraint)
    if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
        echo json_encode(['success' => false, 'message' => 'Erro: Este CPF já possui acesso ao sistema.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro técnico: ' . $e->getMessage()]);
    }
}
exit;