<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Busca os dados do banco
try {
    $db = new PDO('sqlite:' . __DIR__ . '/database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $db->query("SELECT * FROM dispensacoes ORDER BY data_registro DESC");
    $dispensacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erro ao buscar dados: " . $e->getMessage());
}

// Gera o HTML do PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #222; }
        h1   { color: #2e7d32; text-align: center; margin-bottom: 5px; }
        p.sub{ text-align: center; color: #666; margin-bottom: 20px; font-size: 11px; }
        table{ width: 100%; border-collapse: collapse; }
        th   { background-color: #2e7d32; color: white; padding: 8px; text-align: left; }
        td   { padding: 7px 8px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f5f5f5; }
        .rodape { margin-top: 30px; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <h1>NextFarm — Relatório de Dispensações</h1>
    <p class="sub">Gerado em: ' . date('d/m/Y H:i') . ' | Total de registros: ' . count($dispensacoes) . '</p>
    <table>
        <thead>
            <tr>
                <th>Data/Hora</th>
                <th>CPF do Paciente</th>
                <th>Medicamento</th>
                <th>Quantidade</th>
            </tr>
        </thead>
        <tbody>';

foreach ($dispensacoes as $d) {
    $html .= '<tr>
        <td>' . htmlspecialchars($d['data_registro'] ?? '---') . '</td>
        <td>' . htmlspecialchars($d['cpf_paciente']  ?? '---') . '</td>
        <td>' . htmlspecialchars($d['medicamento']   ?? '---') . '</td>
        <td>' . htmlspecialchars($d['quantidade']    ?? '0')   . '</td>
    </tr>';
}

$html .= '
        </tbody>
    </table>
    <p class="rodape">NextFarm — Sistema de Gestão Farmacêutica</p>
</body>
</html>';

// Envia como página para o navegador imprimir como PDF
header('Content-Type: text/html; charset=UTF-8');
echo $html;
?>

<!-- Abre o diálogo de impressão automaticamente -->
<script>
    window.onload = function() {
        window.print();
    }
</script>