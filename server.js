const sqlite3 = require('sqlite3').verbose();
const express = require('express');
const app = express();
const db = new sqlite3.Database('./database.db');

app.use(express.json());
app.use(express.static('public')); // Serve seus arquivos HTML

// Rota de Login consultando o Banco de Dados
app.post('/login', (req, res) => {
    const { cpf, senha } = req.body;
    const query = `SELECT * FROM usuarios WHERE cpf = ? AND senha = ?`;

    db.get(query, [cpf, senha], (err, row) => {
        if (row) {
            res.json({ success: true, user: row });
        } else {
            res.json({ success: false, message: "Usuário não encontrado" });
        }
    });
});

app.listen(3000, () => console.log("Servidor rodando em http://localhost:3000"));