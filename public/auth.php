<?php
session_start();
ob_clean();
header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:' . __DIR__ . '/databaselogin.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Captura 'cpf' enviado pelo formulário
    $cpfEnviado   = $_POST['cpf'] ?? ''; 
    $senhaEnviada = $_POST['senha'] ?? '';

    if (empty($cpfEnviado) || empty($senhaEnviada)) {
        echo json_encode(['status' => 'error', 'message' => 'Campos vazios!']);
        exit;
    }

    // Busca no banco de login
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE usuario = :u");
    $stmt->execute([':u' => $cpfEnviado]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Comparação de texto simples conforme solicitado
    if ($user && password_verify($senhaEnviada, $user['senha'])) {
        $_SESSION['logado'] = true;
        $_SESSION['usuario_cpf'] = $user['usuario'];
        echo json_encode(['status' => 'success', 'message' => 'Acesso liberado!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'CPF ou Senha incorretos!']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro técnico: ' . $e->getMessage()]);
}
exit;