// --- FUNÇÃO DE LOGIN ---
async function fazerLogin() {
    const cpfElement = document.getElementById('cpf');
    const senhaElement = document.getElementById('senha');

    if (!cpfElement || !senhaElement) return;

    const cpf = cpfElement.value;
    const senha = senhaElement.value;

    const dados = new FormData();
    dados.append('cpf', cpf);
    dados.append('senha', senha);

    try {
        // Apontando para o arquivo PHP correto
        const resposta = await fetch('processar_login.php', {
            method: 'POST',
            body: dados
        });

        const textoPuro = await resposta.text();
        
        try {
            const resultado = JSON.parse(textoPuro);
            if (resultado.status === 'success') {
                // Salva os dados e redireciona para o .php
                localStorage.setItem('usuarioAtivo', JSON.stringify({ cpf: cpf }));
                window.location.href = "dashboard.php";
            } else {
                alert("Erro: " + resultado.message);
            }
        } catch (e) {
            alert("O PHP retornou um erro inesperado: \n" + textoPuro);
        }

    } catch (error) {
        console.error('Erro de conexão:', error);
        alert("Erro ao conectar com o servidor.");
    }
}

// --- CONTROLE GERAL DO DOM ---
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Lógica do Formulário de Dispensação
    const formDispensacao = document.getElementById('formDispensacao');
    if (formDispensacao) {
        formDispensacao.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('processar_dispensacao.php', {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                const textoPuro = await response.text();
                try {
                    const data = JSON.parse(textoPuro);
                    if (data.status === 'success') {
                        alert("Sucesso! " + data.message);
                        location.reload();
                    } else {
                        alert("Erro: " + data.message);
                    }
                } catch (err) {
                    alert("Erro no servidor: \n" + textoPuro);
                }
            })
            .catch(error => alert("ERRO DE CONEXÃO: " + error.message));
        });
    }

    // 2. Lógica do Formulário de Pacientes (se houver)
    const formPaciente = document.getElementById('formPaciente');
    if (formPaciente) {
        formPaciente.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('processar_paciente.php', {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                const textoPuro = await response.text();
                try {
                    const data = JSON.parse(textoPuro);
                    if (data.success || data.status === 'success') {
                        alert("Paciente cadastrado!");
                        location.reload();
                    } else {
                        alert("Erro: " + data.message);
                    }
                } catch (err) {
                    alert("Resposta inválida do servidor.");
                }
            })
            .catch(error => console.error('Erro:', error));
        });
    }
});

async function abrirRelatorioDispensacao() {
    abrirModal('modalRelatoriosDispensacao');
    await carregarDispensacoes();
}

async function carregarDispensacoes() {
    const corpoTabela = document.getElementById('corpoTabelaDispensacao');
    corpoTabela.innerHTML = '<tr><td colspan="4">Carregando...</td></tr>';

    try {
        const resposta = await fetch('listar_dispensacoes.php');
        const dados = await resposta.json();

        if (dados.length === 0) {
            corpoTabela.innerHTML = '<tr><td colspan="4">Nenhuma dispensação encontrada.</td></tr>';
            return;
        }

        corpoTabela.innerHTML = '';
        dados.forEach(item => {
            const linha = `
                <tr>
                    <td>${item.data_hora}</td>
                    <td>${item.cpf_paciente}</td>
                    <td>${item.medicamento}</td>
                    <td>${item.quantidade}</td>
                </tr>
            `;
            corpoTabela.innerHTML += linha;
        });
    } catch (erro) {
        corpoTabela.innerHTML = '<tr><td colspan="4">Erro ao carregar dados.</td></tr>';
        console.error(erro);
    }
}

async function buscarPorCpf() {
    const cpf = document.getElementById('buscaCpfPaciente').value;
    const corpoTabela = document.getElementById('resultadoBuscaCpf');

    if (cpf.length < 3) {
        alert("Por favor, digite um CPF válido.");
        return;
    }

    corpoTabela.innerHTML = '<tr><td colspan="3">Pesquisando...</td></tr>';

    try {
        const resposta = await fetch(`procurar_dispensacao_cpf.php?cpf=${cpf}`);
        const dados = await resposta.json();

        if (dados.length === 0) {
            corpoTabela.innerHTML = '<tr><td colspan="3">Nenhum registro encontrado para este CPF.</td></tr>';
            return;
        }

        corpoTabela.innerHTML = '';
        dados.forEach(item => {
            // Normaliza as chaves para minúsculo (mesma solução que funcionou antes)
            const registro = {};
            for (let chave in item) { registro[chave.toLowerCase()] = item[chave]; }

            corpoTabela.innerHTML += `
                <tr>
                    <td style="font-size: 11px;">${registro.data_registro || '---'}</td>
                    <td>${registro.medicamento || '---'}</td>
                    <td>${registro.quantidade || '0'}</td>
                </tr>
            `;
        });
    } catch (erro) {
        corpoTabela.innerHTML = '<tr><td colspan="3">Erro na busca.</td></tr>';
    }
}

// Função para abrir o modal
function modalProcurarPacientes() {
    document.getElementById('modalProcurarPacientes').style.display = 'flex';
    document.getElementById('resultadoBuscaCpf').innerHTML = '<tr><td colspan="3">Digite o CPF para pesquisar</td></tr>';
}