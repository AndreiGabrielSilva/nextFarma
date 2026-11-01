<?php
try {
    // Conecta ao banco de login
    $db = new PDO('sqlite:databaselogin.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. CRIA A TABELA (Garante que ela exista)
    $db->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario TEXT UNIQUE NOT NULL,
        senha TEXT NOT NULL,
        nivel TEXT NOT NULL
    )");

    // 2. DADOS DO SEU USUÁRIO MESTRE (Sem Hash)
    $usuarioMestre = "553.728.763-04";
    $senhaLimpa    = "AP123456"; // Será salva exatamente assim

    // 3. INSERE O USUÁRIO (Usando INSERT OR REPLACE para evitar erro de duplicata)
    $sql = "INSERT OR REPLACE INTO usuarios (usuario, senha, nivel) VALUES (:u, :s, :n)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':u' => $usuarioMestre,
        ':s' => $senhaLimpa,
        ':n' => 'mestre'
    ]);

    echo "<h2>✅ Configurado com Senha Direta!</h2>";
    echo "<b>Login:</b> $usuarioMestre<br>";
    echo "<b>Senha Salva:</b> $senhaLimpa<br>";
    echo "<p>Agora a senha no banco de dados não está mais mascarada.</p>";

} catch (PDOException $e) {
    echo "❌ Erro Fatal: " . $e->getMessage();
}