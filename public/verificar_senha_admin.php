<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$senhaDigitada = $_POST['senha'] ?? '';
$usuarioLogado = $_SESSION['usuario_nome'];

try {
    $db = new PDO('sqlite:' . __DIR__ . '/databaselogin.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT senha FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuarioLogado]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($senhaDigitada, $user['senha'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Senha incorreta']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>