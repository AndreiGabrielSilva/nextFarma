<?php
/**
 * usuarios_action.php — NextFarma
 * Banco: SQLite — databaselogin.db
 * Colunas: id | usuario | senha | nivel
 *
 * Ações aceitas via POST (JSON):
 *   { "acao": "editar",  "id": 1, "nome": "553.728.763-04", "senha": "", "perfil": "mestre" }
 *   { "acao": "excluir", "id": 1 }
 */

define('DB_PATH', __DIR__ . '/databaselogin.db');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

session_start();

if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autenticado.']);
    exit;
}

if (($_SESSION['usuario_role'] ?? '') !== 'mestre') {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado. Apenas mestres podem gerenciar usuários.']);
    exit;
}

$corpo = file_get_contents('php://input');
$dados = json_decode($corpo, true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($dados['acao'])) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Requisição inválida.']);
    exit;
}

// Conexão SQLite
try {
    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON;');
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao abrir o banco de dados.']);
    exit;
}

$acao = trim($dados['acao']);

switch ($acao) {

    // ── EDITAR ────────────────────────────────────────────────────────────────
    case 'editar':
        $id      = filter_var($dados['id']     ?? null, FILTER_VALIDATE_INT);
        $usuario = trim($dados['nome']          ?? '');  // campo "nome" vindo do JS = coluna "usuario"
        $senha   = $dados['senha']              ?? '';
        $nivel   = trim($dados['perfil']        ?? '');  // campo "perfil" vindo do JS = coluna "nivel"

        $niveisPermitidos = ['farmaceutico', 'mestre'];

        if (!$id || $id <= 0)                             responder(400, 'ID inválido.');
        if (empty($usuario))                              responder(400, 'O usuário é obrigatório.');
        if (!in_array($nivel, $niveisPermitidos, true))   responder(400, 'Nível inválido.');

        // Checa usuário duplicado em outro registro
        $stmtCheck = $pdo->prepare(
            'SELECT id FROM usuarios WHERE usuario = :usuario AND id != :id LIMIT 1'
        );
        $stmtCheck->execute([':usuario' => $usuario, ':id' => $id]);
        if ($stmtCheck->fetch()) {
            responder(409, 'Este usuário/CPF já está em uso por outro registro.');
        }

        if (!empty($senha)) {
            if (strlen($senha) < 6) responder(400, 'A senha deve ter no mínimo 6 caracteres.');
            $hash = password_hash($senha, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                'UPDATE usuarios SET usuario = :usuario, senha = :senha, nivel = :nivel WHERE id = :id'
            );
            $stmt->execute([
                ':usuario' => $usuario,
                ':senha'   => $hash,
                ':nivel'   => $nivel,
                ':id'      => $id,
            ]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE usuarios SET usuario = :usuario, nivel = :nivel WHERE id = :id'
            );
            $stmt->execute([
                ':usuario' => $usuario,
                ':nivel'   => $nivel,
                ':id'      => $id,
            ]);
        }

        if ($stmt->rowCount() === 0) {
            responder(404, 'Usuário não encontrado ou sem alterações.');
        }

        registrarLog($pdo, $_SESSION['usuario_id'], 'editar_usuario', "Usuário ID {$id} editado.");
        responder(200, 'Usuário atualizado com sucesso.', ['id' => $id]);
        break;

    // ── EXCLUIR ───────────────────────────────────────────────────────────────
    case 'excluir':
        $id = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id <= 0) responder(400, 'ID inválido.');

        if ($id === (int) $_SESSION['usuario_id']) {
            responder(403, 'Você não pode excluir seu próprio usuário.');
        }

        $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 0) {
            responder(404, 'Usuário não encontrado.');
        }

        registrarLog($pdo, $_SESSION['usuario_id'], 'excluir_usuario', "Usuário ID {$id} excluído.");
        responder(200, 'Usuário excluído com sucesso.', ['id' => $id]);
        break;

    default:
        responder(400, 'Ação desconhecida.');
}

// ── Funções auxiliares ────────────────────────────────────────────────────────

function responder(int $httpCode, string $mensagem, array $extra = []): void
{
    http_response_code($httpCode);
    echo json_encode(
        array_merge(
            ['sucesso' => $httpCode >= 200 && $httpCode < 300, 'mensagem' => $mensagem],
            $extra
        ),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

function registrarLog(PDO $pdo, int $usuarioId, string $acao, string $descricao): void
{
    try {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS logs_sistema (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                usuario_id  INTEGER NOT NULL,
                acao        TEXT    NOT NULL,
                descricao   TEXT,
                criado_em   TEXT    NOT NULL DEFAULT (datetime("now","localtime"))
            )'
        );
        $stmt = $pdo->prepare(
            'INSERT INTO logs_sistema (usuario_id, acao, descricao) VALUES (:uid, :acao, :desc)'
        );
        $stmt->execute([':uid' => $usuarioId, ':acao' => $acao, ':desc' => $descricao]);
    } catch (PDOException $e) {
        // Silencioso
    }
}