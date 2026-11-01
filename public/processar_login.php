<?php
session_start();
header('Content-Type: application/json');

try {
    // Conexão com o banco de login
    $db = new PDO('sqlite:' . __DIR__ . '/databaselogin.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Captura os dados enviados pelo script.js (FormData)
    $cpfEnviado   = $_POST['cpf'] ?? ''; 
    $senhaEnviada = $_POST['senha'] ?? '';

    if (empty($cpfEnviado) || empty($senhaEnviada)) {
        echo json_encode(['status' => 'error', 'message' => 'Campos vazios!']);
        exit;
    }

    // Busca o usuário
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE usuario = :u LIMIT 1");
    $stmt->execute([':u' => $cpfEnviado]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validação
    if ($user && password_verify($senhaEnviada, $user['senha'])) {
        
        // --- AS CHAVES ABAIXO DEVEM SER IGUAIS ÀS DO DASHBOARD ---
        $_SESSION['usuario_id']   = $user['id'];
        $_SESSION['usuario_nome'] = $user['usuario']; // Ou a coluna de nome, se tiver
        $_SESSION['usuario_role'] = $user['nivel'];   // mestre ou farmaceutico
        $_SESSION['logado']       = true;

        echo json_encode([
            'status' => 'success', 
            'message' => 'Acesso liberado!'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'CPF ou Senha incorretos!']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro no banco: ' . $e->getMessage()]);
}
exit;