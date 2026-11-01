<?php
session_start();
header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:database.db');
    $escala = $_GET['escala'] ?? 'diaria';

    // Define o intervalo de tempo baseado no botão clicado
    if ($escala === 'semanal') {
        $intervalo = "-7 days";
    } elseif ($escala === 'mensal') {
        $intervalo = "-30 days";
    } else {
        $intervalo = "-1 day";
    }

    // Busca pacientes no intervalo selecionado
    $stmt = $db->prepare("SELECT count(id) as total, date(data_cadastro) as dia FROM pacientes WHERE data_cadastro >= date('now', :intervalo) GROUP BY dia ORDER BY dia ASC");
    $stmt->execute([':intervalo' => $intervalo]);
    $resLinha = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $valores = [];
    foreach($resLinha as $linha) {
        $labels[] = date('d/m', strtotime($linha['dia']));
        $valores[] = (int)$linha['total'];
    }

    // Gráfico de Pizza (Total de medicamentos no período)
    $stmtMed = $db->prepare("SELECT medicamento, SUM(quantidade) as total FROM dispensacoes WHERE data_registro >= date('now', :intervalo) GROUP BY medicamento");
    $stmtMed->execute([':intervalo' => $intervalo]);
    $resPizza = $stmtMed->fetchAll(PDO::FETCH_ASSOC);

    $meds = []; $qtds = [];
    foreach($resPizza as $item) {
        $meds[] = $item['medicamento'];
        $qtds[] = (int)$item['total'];
    }

    echo json_encode([
        'linha' => [ 'labels' => $labels, 'dados' => $valores ],
        'pizza' => [ 'labels' => $meds, 'dados' => $qtds ]
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}