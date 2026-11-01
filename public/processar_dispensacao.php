<?php
ob_clean(); 
header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Captura os dados (certifique-se que o 'name' no HTML é exatamente esse)
    $cpf_paciente = $_POST['cpf_paciente'] ?? '';
    $medicamento  = $_POST['medicamento'] ?? '';
    $quantidade   = $_POST['quantidade'] ?? '';

    if (empty($cpf_paciente) || empty($medicamento)) {
        echo json_encode(['status' => 'error', 'message' => 'Preencha os campos obrigatórios!']);
        exit;
    }

    // GERAÇÃO DO PROTOCOLO ALEATÓRIO (NF-XXXXXX)
    $codigo = "NF-" . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

    // SQL usando exatamente os nomes criados no reset
    $sql = "INSERT INTO dispensacoes (cpf_paciente, medicamento, quantidade, codigo_registro) 
            VALUES (:cpf, :med, :qtd, :cod)";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':cpf' => $cpf_paciente,
        ':med' => $medicamento,
        ':qtd' => $quantidade,
        ':cod' => $codigo
    ]);

    echo json_encode([
        'status' => 'success', 
        'message' => "Dispensação Salva! \nProtocolo: $codigo",
        'codigo' => $codigo
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Erro no Banco: ' . $e->getMessage()
    ]);
}
exit;