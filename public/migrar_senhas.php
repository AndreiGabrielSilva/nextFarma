<?php
$db = new PDO('sqlite:' . __DIR__ . '/databaselogin.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$usuarios = $db->query("SELECT id, senha FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $user) {
    // Só converte se ainda não for hash
    if (strlen($user['senha']) < 40) {
        $hash = password_hash($user['senha'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $stmt->execute([$hash, $user['id']]);
        echo "Usuário ID {$user['id']} convertido.<br>";
    } else {
        echo "Usuário ID {$user['id']} já estava em hash, pulado.<br>";
    }
}

echo "<br><b>Concluído!</b>";
?>