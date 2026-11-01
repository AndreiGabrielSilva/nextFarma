<?php
// 1. Limpa qualquer saída anterior e força o formato JSON
ob_clean(); 
header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $nome = $_POST['nome'] ?? '';
    $cpf  = $_POST['cpf'] ?? '';

    if (empty($nome) || empty($cpf)) {
        echo json_encode(['status' => 'error', 'message' => 'Campos vazios!']);
        exit;
    }

    // Usando os nomes exatos das colunas que vimos nas suas imagens
    $sql = "INSERT INTO pacientes (NOME, CPF_PACIENTE, CPF) VALUES (:nome, :cpf_p, :cpf)";
    $stmt = $db->prepare($sql);
    
    $success = $stmt->execute([
        ':nome'  => $nome,
        ':cpf_p' => $cpf,
        ':cpf'   => $cpf
    ]);

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Paciente salvo com sucesso!']);
    }

} catch (PDOException $e) {
    // Se cair aqui, é porque o CPF já existe ou houve erro de SQL
    if ($e->getCode() == 23000) {
        echo json_encode(['status' => 'error', 'message' => 'Este CPF já está cadastrado.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro: ' . $e->getMessage()]);
    }
}
exit; // Garante que nada mais seja impresso depois do JSON