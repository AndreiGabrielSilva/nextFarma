<?php
session_start();

// Proteção de acesso
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$nomeUsuario = $_SESSION['usuario_nome'];
$nivelAcesso = $_SESSION['usuario_role'];
$totalPacientes = 0;
$usuarios = [];

try {
    $dbLogin = new PDO('sqlite:' . __DIR__ . '/databaselogin.db');
    $dbLogin->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $dbLogin->query('SELECT id, usuario, nivel FROM usuarios ORDER BY usuario');
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $usuarios = [];
}

try {
    $db = new PDO('sqlite:' . __DIR__ . '/database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $totalPacientes = $db->query("SELECT COUNT(*) FROM pacientes")->fetchColumn();
} catch (Exception $e) {
    $erro_banco = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NextFarma - Sistema de Gestão</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-body">

    <div class="layout-container">
        <aside class="sidebar">
            <div class="logo-area">
                <h2 id="logo-texto">NextFarma</h2>
                <span>Nível de Acesso: <?php echo strtoupper($nivelAcesso); ?></span>
                <span style="font-size: 14px; opacity: 0.8; display: block;">Total de pacientes: <?php echo (int)$totalPacientes; ?></span>
            </div>

            <nav class="menu">
                <p>Usuário: <strong><?php echo $nomeUsuario; ?></strong></p>
                <hr style="opacity: 0.1; margin: 15px 0;">

                <?php if ($nivelAcesso === 'mestre'): ?>
                    <button class="btn-menu" onclick="abrirConfiguracoes()">Configurar Identidade Visual</button>
                    <button class="btn-menu" onclick="abrirModal('modalUsuario')">Criar Usuários</button>
                    <button class="btn-menu" onclick="abrirModal('modalGerenciar')">Gerenciar Usuários</button>
                <?php endif; ?>

                <button class="btn-menu" onclick="abrirModal('modalPaciente')">Cadastrar Paciente</button>
                <button class="btn-menu" onclick="abrirModal('modalDispensacao')">Nova Dispensação</button>
                <button class="btn-menu" onclick="procurarPacientes()">Encontre pacientes</button>
                <button class="btn-menu" onclick="abrirRelatorioDispensacao()">Relatórios de dispensação</button>

                <div class="filtros-grafico">
                    <p style="font-size: 11px; opacity: 0.6;">ESCALA DOS RELATÓRIOS:</p>
                    <div class="botoes-container">
                        <button class="btn-small" onclick="atualizarEscala('diaria')">Diário</button>
                        <button class="btn-small" onclick="atualizarEscala('semanal')">Semanal</button>
                        <button class="btn-small" onclick="atualizarEscala('mensal')">Mensal</button>
                    </div>
                </div>
            </nav>

            <button class="btn-exportar" onclick="pedirSenhaExportar()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="12" y1="18" x2="12" y2="12"/>
                    <polyline points="9 15 12 18 15 15"/>
                </svg>
                Exportar PDF
            </button>
            <a href="logout.php" class="btn-sair">Encerrar Sessão</a>
        </aside>

        <main class="content">
            <header class="top-bar">
                <h1 id="titulo-dinamico">Painel de Controle Principal</h1>
            </header>

            <div class="painel-dados">
                <?php if ($nivelAcesso === 'farmaceutico'): ?>
                    <div class="graficos-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                        <div class="card-grafico">
                            <h3>Volume de Pacientes Atendidos</h3>
                            <canvas id="graficoPacientes"></canvas>
                        </div>
                        <div class="card-grafico">
                            <h3>Distribuição de Medicamentos</h3>
                            <canvas id="graficoPizza"></canvas>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card-mestre">
                        <h3>Administração do Sistema</h3>
                        <p>Selecione uma função administrativa no menu lateral.</p>
                        <div id="controles-layout" style="display:none; margin-top: 25px;">
                            <div class="campo-admin">
                                <label>Nome da Unidade:</label>
                                <input type="text" placeholder="Ex: NextFarma Matriz" oninput="document.getElementById('logo-texto').innerText = this.value">
                            </div>
                            <div class="campo-admin">
                                <label>Cor Principal do Sistema:</label>
                                <input type="color" onchange="document.documentElement.style.setProperty('--primaria', this.value)">
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal: Senha Exportar -->
    <div id="modalSenhaExportar" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Confirmar Identidade</h3>
            <p style="font-size: 13px; opacity: 0.7; margin-bottom: 15px;">Digite a senha do administrador para exportar.</p>
            <input type="password" id="inputSenhaExportar" placeholder="Senha do administrador" style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
            <div class="botoes-modal" style="margin-top: 15px;">
                <button type="button" class="btn-cancel" onclick="fecharModal('modalSenhaExportar')">Cancelar</button>
                <button type="button" class="btn-confirm" onclick="confirmarExportar()">Confirmar</button>
            </div>
        </div>
    </div>

    <!-- Modal: Criar Usuário -->
    <div id="modalUsuario" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Novo Cadastro de Usuário</h3>
            <form id="formNovoUsuario">
                <input type="text" name="nome" placeholder="Nome Completo" required>
                <input type="text" id="cpf_novo" name="cpf" placeholder="CPF para Login" maxlength="14" required>
                <input type="email" name="email" placeholder="E-mail Corporativo" required>
                <select name="role">
                    <option value="farmaceutico">Perfil: Farmacêutico</option>
                    <option value="mestre">Perfil: Administrador Mestre</option>
                </select>
                <div class="botoes-modal">
                    <button type="button" class="btn-cancel" onclick="fecharModal('modalUsuario')">Cancelar</button>
                    <button type="submit" class="btn-confirm">Gerar Acesso</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Cadastrar Paciente -->
    <div id="modalPaciente" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Cadastrar Novo Paciente</h3>
            <form id="formPaciente">
                <input type="text" name="nome" placeholder="Nome do Paciente" required>
                <input id="cpf" name="cpf" placeholder="CPF" required>
                <div class="botoes-modal">
                    <button type="button" class="btn-cancel" onclick="fecharModal('modalPaciente')">Cancelar</button>
                    <button type="submit" class="btn-confirm">Salvar Registro</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Dispensação -->
    <div id="modalDispensacao" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Registrar Dispensação</h3>
            <form id="formDispensacao">
                <input type="text" name="cpf_paciente" placeholder="CPF do Paciente" required>
                <input type="text" name="medicamento" placeholder="Medicamento" required>
                <input type="number" name="quantidade" placeholder="Quantidade" required>
                <div class="botoes-modal">
                    <button type="button" class="btn-cancel" onclick="fecharModal('modalDispensacao')">Cancelar</button>
                    <button type="submit" class="btn-confirm">Confirmar Saída</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Relatórios Dispensação -->
    <div id="modalRelatoriosDispensacao" class="modal" style="display:none;">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3>Relatório Geral de Dispensações</h3>
                <button type="button" class="btn-close" onclick="fecharModal('modalRelatoriosDispensacao')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="tabela-container">
                    <table id="tabelaDispensacoes">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Paciente (CPF)</th>
                                <th>Medicamento</th>
                                <th>Qtd</th>
                            </tr>
                        </thead>
                        <tbody id="corpoTabelaDispensacao"></tbody>
                    </table>
                </div>
            </div>
            <div class="botoes-modal">
                <button type="button" class="btn-cancel" onclick="fecharModal('modalRelatoriosDispensacao')">Fechar</button>
                <button type="button" class="btn-confirm" onclick="carregarDispensacoes()">Atualizar Dados</button>
            </div>
        </div>
    </div>

    <!-- Modal: Procurar Pacientes -->
    <div id="modalProcurarPacientes" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Consultar Histórico por CPF</h3>
                <button type="button" class="btn-close" onclick="fecharModal('modalProcurarPacientes')">&times;</button>
            </div>
            <div class="modal-body">
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <input type="text" id="buscaCpfPaciente" placeholder="Digite o CPF do Paciente" style="flex: 1; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    <button type="button" class="btn-small" onclick="buscarPorCpf()" style="background-color: #2e7d32; color: white;">BUSCAR</button>
                </div>
                <div class="tabela-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Medicamento</th>
                                <th>Qtd</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoBuscaCpf">
                            <tr><td colspan="3">Digite o CPF para pesquisar</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div>
            <button class="export" style="color: black;">exportar pdf</button>
        </div>
    </div>

    <!-- Modal: Gerenciar Usuários -->
    <div id="modalGerenciar" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:none; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:12px; width:700px; max-height:90vh; overflow:hidden; display:flex; flex-direction:column;">

            <div style="display:flex; justify-content:space-between; align-items:center; padding:1rem 1.25rem; border-bottom:1px solid #eee;">
                <div>
                    <strong>Gerenciar Usuários</strong>
                    <p style="font-size:12px; color:#888; margin:0">NextFarma — controle de acesso</p>
                </div>
                <button onclick="fecharModal('modalGerenciar')" style="border:1px solid #ddd; border-radius:6px; background:transparent; width:28px; height:28px; cursor:pointer; font-size:14px;">✕</button>
            </div>

            <div style="padding:.75rem 1.25rem; border-bottom:1px solid #eee;">
                <input type="text" id="busca" placeholder="Buscar por nome..." oninput="filtrar()"
                       style="width:100%; height:34px; padding:0 10px; border:1px solid #ddd; border-radius:8px; font-size:13px;" />
            </div>

            <div style="overflow-y:auto; flex:1; position:relative;">
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <thead style="background:#f9f9f9;">
                        <tr>
                            <th style="padding:8px 1.25rem; text-align:left; font-weight:500;">Usuário</th>
                            <th style="padding:8px 1.25rem; text-align:left; font-weight:500;">Nível</th>
                            <th style="padding:8px 1.25rem; text-align:left; font-weight:500;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela">
                        <?php foreach ($usuarios as $u): ?>
                        <tr style="border-bottom:1px solid #eee;" data-nome="<?= strtolower($u['usuario']) ?>">
                            <td style="padding:10px 1.25rem"><?= htmlspecialchars($u['usuario']) ?></td>
                            <td style="padding:10px 1.25rem">
                                <span style="padding:2px 8px; border-radius:6px; font-size:11px;
                                             background:<?= $u['nivel']==='mestre' ? '#FAEEDA' : '#E6F1FB' ?>;
                                             color:<?= $u['nivel']==='mestre' ? '#854F0B' : '#185FA5' ?>">
                                    <?= htmlspecialchars($u['nivel']) ?>
                                </span>
                            </td>
                            <td style="padding:10px 1.25rem">
                                <button onclick="editarUsuario(<?= $u['id'] ?>, '<?= addslashes($u['usuario']) ?>', '<?= $u['nivel'] ?>')"
                                        style="border:1px solid #ddd; border-radius:6px; background:transparent; padding:4px 10px; font-size:12px; cursor:pointer; margin-right:4px;">
                                    ✏ Editar
                                </button>
                                <button onclick="pedirExclusao(<?= $u['id'] ?>, '<?= addslashes($u['usuario']) ?>')"
                                        style="border:1px solid #f7c1c1; border-radius:6px; background:transparent; padding:4px 10px; font-size:12px; cursor:pointer; color:#a32d2d;">
                                    ✕ Excluir
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Sub-modal: Editar -->
                <div id="formOverlay" style="display:none; position:absolute; inset:0; background:rgba(0,0,0,0.5); z-index:10;">
                    <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%;">
                        <div style="background:#fff; border-radius:12px; padding:1.25rem; width:360px;">
                            <strong style="font-size:15px;">Editar Usuário</strong>
                            <input type="hidden" id="fId" />
                            <div style="margin-top:14px">
                                <label style="font-size:12px; color:#666; display:block; margin-bottom:4px">Usuário / CPF</label>
                                <input id="fNome" type="text" style="width:100%; height:34px; border:1px solid #ddd; border-radius:8px; padding:0 10px;" />
                            </div>
                            <div style="margin-top:10px">
                                <label style="font-size:12px; color:#666; display:block; margin-bottom:4px">Nova senha <span style="opacity:.6">(deixe em branco para manter)</span></label>
                                <input id="fSenha" type="password" placeholder="••••••" style="width:100%; height:34px; border:1px solid #ddd; border-radius:8px; padding:0 10px;" />
                            </div>
                            <div style="margin-top:10px">
                                <label style="font-size:12px; color:#666; display:block; margin-bottom:4px">Nível de acesso</label>
                                <select id="fPerfil" style="width:100%; height:34px; border:1px solid #ddd; border-radius:8px; padding:0 8px;">
                                    <option value="farmaceutico">Farmacêutico</option>
                                    <option value="mestre">Mestre</option>
                                </select>
                            </div>
                            <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:1rem">
                                <button onclick="fecharForm()" style="height:32px; padding:0 14px; border:1px solid #ddd; border-radius:8px; background:transparent; cursor:pointer;">Cancelar</button>
                                <button onclick="salvarEdicao()" style="height:32px; padding:0 14px; border:none; border-radius:8px; background:#185FA5; color:#fff; cursor:pointer;">Salvar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sub-modal: Confirmar exclusão -->
                <div id="confirmOverlay" style="display:none; position:absolute; inset:0; background:rgba(0,0,0,0.5); z-index:20;">
                    <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%;">
                        <div style="background:#fff; border-radius:12px; padding:1.5rem; width:300px; text-align:center;">
                            <p style="font-size:15px; font-weight:500; margin-bottom:6px;">Excluir usuário?</p>
                            <p id="confirmNome" style="font-size:12px; color:#666; margin-bottom:1rem;"></p>
                            <div style="display:flex; gap:8px; justify-content:center;">
                                <button onclick="fecharConfirm()" style="height:32px; padding:0 14px; border:1px solid #ddd; border-radius:8px; background:transparent; cursor:pointer;">Cancelar</button>
                                <button onclick="confirmarExclusao()" style="height:32px; padding:0 14px; border:none; border-radius:8px; background:#A32D2D; color:#fff; cursor:pointer;">Excluir</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- fim tabela wrapper -->

            <div style="padding:.75rem 1.25rem; border-top:1px solid #eee; font-size:12px; color:#888; display:flex; justify-content:space-between;">
                <span><?= count($usuarios ?? []) ?> usuário<?= count($usuarios ?? []) !== 1 ? 's' : '' ?> cadastrado<?= count($usuarios ?? []) !== 1 ? 's' : '' ?></span>
                <span>NextFarma v2.0</span>
            </div>

        </div>
    </div>

    <script>
        function abrirModal(id) { document.getElementById(id).style.display = 'flex'; }
        function fecharModal(id) { document.getElementById(id).style.display = 'none'; }

        function abrirConfiguracoes() {
            document.getElementById('controles-layout').style.display = 'block';
            document.getElementById('titulo-dinamico').innerText = 'Configurações de Identidade';
        }

        // ── Gerenciar Usuários ───────────────────────────────────────────────
        let excluindoId = null;

        function filtrar() {
            const q = document.getElementById('busca').value.toLowerCase();
            document.querySelectorAll('#tabela tr').forEach(tr => {
                const nome = tr.dataset.nome || '';
                tr.style.display = nome.includes(q) ? '' : 'none';
            });
        }

        function editarUsuario(id, nome, nivel) {
            document.getElementById('fId').value    = id;
            document.getElementById('fNome').value  = nome;
            document.getElementById('fSenha').value = '';
            document.getElementById('fPerfil').value = nivel;
            document.getElementById('formOverlay').style.display = 'block';
        }
        function fecharForm() {
            document.getElementById('formOverlay').style.display = 'none';
        }
        function salvarEdicao() {
            const id     = document.getElementById('fId').value;
            const nome   = document.getElementById('fNome').value.trim();
            const senha  = document.getElementById('fSenha').value;
            const perfil = document.getElementById('fPerfil').value;
            if (!nome) { alert('Preencha o usuário.'); return; }
            fetch('usuarios_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'editar', id, nome, senha, perfil })
            }).then(r => r.json()).then(d => {
                if (d.sucesso) location.reload();
                else alert('Erro: ' + d.mensagem);
            });
        }

        function pedirExclusao(id, nome) {
            excluindoId = id;
            document.getElementById('confirmNome').textContent = nome;
            document.getElementById('confirmOverlay').style.display = 'block';
        }
        function fecharConfirm() {
            document.getElementById('confirmOverlay').style.display = 'none';
            excluindoId = null;
        }
        function confirmarExclusao() {
            fetch('usuarios_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ acao: 'excluir', id: excluindoId })
            }).then(() => location.reload());
        }

        // ── Exportar PDF ─────────────────────────────────────────────────────
        function pedirSenhaExportar() {
            document.getElementById('inputSenhaExportar').value = '';
            abrirModal('modalSenhaExportar');
            document.getElementById('inputSenhaExportar').onkeydown = function(e) {
                if (e.key === 'Enter') confirmarExportar();
            };
        }
        async function confirmarExportar() {
            const senha = document.getElementById('inputSenhaExportar').value;
            if (!senha) { alert("Digite a senha!"); return; }
            const fd = new FormData();
            fd.append('senha', senha);
            const resposta = await fetch('verificar_senha_admin.php', { method: 'POST', body: fd });
            const dados = await resposta.json();
            if (dados.success) {
                fecharModal('modalSenhaExportar');
                window.open('exportar_pdf', '_blank');
            } else {
                alert("Senha incorreta!");
                document.getElementById('inputSenhaExportar').value = '';
            }
        }

        // ── Criar Usuário ────────────────────────────────────────────────────
        if (document.getElementById('formNovoUsuario')) {
            document.getElementById('formNovoUsuario').onsubmit = async (e) => {
                e.preventDefault();
                const res = await fetch('criar_usuario.php', { method: 'POST', body: new FormData(e.target) });
                const dados = await res.json();
                if (dados.success) {
                    alert("Usuário Criado!\nSenha: " + dados.senha);
                    fecharModal('modalUsuario'); e.target.reset();
                } else { alert("Erro: " + dados.message); }
            };
        }

        // ── Salvar Paciente ──────────────────────────────────────────────────
        if (document.getElementById('formPaciente')) {
            document.getElementById('formPaciente').onsubmit = async (e) => {
                e.preventDefault();
                try {
                    const res = await fetch('processar_paciente.php', { method: 'POST', body: new FormData(e.target) });
                    const textoBruto = await res.text();
                    try {
                        const dados = JSON.parse(textoBruto);
                        if (dados.success) {
                            alert("Paciente salvo com sucesso!");
                            fecharModal('modalPaciente'); e.target.reset(); atualizarEscala('diaria');
                        } else { alert("Erro: " + dados.message); }
                    } catch (e) { alert("Resposta inválida: " + textoBruto); }
                } catch (err) { alert("Erro na requisição: " + err.message); }
            };
        }

        // ── Dispensação ──────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function() {
            const formDispensacao = document.getElementById('formDispensacao');
            if (formDispensacao) {
                formDispensacao.addEventListener('submit', function(e) {
                    e.preventDefault();
                    fetch('processar_dispensacao.php', { method: 'POST', body: new FormData(this) })
                    .then(async response => {
                        const textoPuro = await response.text();
                        try {
                            const data = JSON.parse(textoPuro);
                            if (data.status === 'success') { alert("Dispensação salva: " + data.message); location.reload(); }
                            else { alert("Erro: " + data.message); }
                        } catch (err) { alert("Resposta do servidor:\n\n" + textoPuro); }
                    })
                    .catch(error => { alert("Erro de conexão: " + error.message); });
                });
            }
        });

        // ── Gráficos ─────────────────────────────────────────────────────────
        <?php if ($nivelAcesso === 'farmaceutico'): ?>
        let chartPacientes, chartPizza;
        async function atualizarEscala(escala) {
            try {
                const response = await fetch(`buscar_dados.php?escala=${escala}`);
                const json = await response.json();
                const ctxPacientes = document.getElementById('graficoPacientes').getContext('2d');
                const ctxPizza = document.getElementById('graficoPizza').getContext('2d');
                if (chartPacientes) chartPacientes.destroy();
                if (chartPizza) chartPizza.destroy();
                chartPacientes = new Chart(ctxPacientes, {
                    type: 'line',
                    data: { labels: json.linha.labels, datasets: [{ label: 'Pacientes Atendidos (' + escala + ')', data: json.linha.dados, borderColor: '#2e7d32', backgroundColor: 'rgba(46,125,50,0.1)', fill: true, tension: 0.3 }] },
                    options: { responsive: true }
                });
                chartPizza = new Chart(ctxPizza, {
                    type: 'doughnut',
                    data: { labels: json.pizza.labels, datasets: [{ data: json.pizza.dados, backgroundColor: ['#1b5e20','#2e7d32','#4caf50','#81c784'] }] },
                    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                });
            } catch (error) { console.error("Erro ao carregar gráfico:", error); }
        }
        document.addEventListener('DOMContentLoaded', () => { atualizarEscala('diaria'); });
        <?php endif; ?>

        // ── Máscara CPF ──────────────────────────────────────────────────────
        const cpfInput = document.getElementById('cpf_novo');
        if (cpfInput) {
            cpfInput.addEventListener('input', e => {
                let v = e.target.value.replace(/\D/g, "");
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
                e.target.value = v;
            });
        }

        // ── Relatório Dispensações ───────────────────────────────────────────
        function abrirRelatorioDispensacao() {
            document.getElementById('modalRelatoriosDispensacao').style.display = 'flex';
            carregarDispensacoes();
        }
        async function carregarDispensacoes() {
            const corpoTabela = document.getElementById('corpoTabelaDispensacao');
            if (!corpoTabela) return;
            corpoTabela.innerHTML = '<tr><td colspan="4">Carregando dados...</td></tr>';
            try {
                const resposta = await fetch('listar_dispensacoes.php');
                const dados = await resposta.json();
                if (dados.erro) { corpoTabela.innerHTML = `<tr><td colspan="4">Erro: ${dados.erro}</td></tr>`; return; }
                if (!Array.isArray(dados) || dados.length === 0) { corpoTabela.innerHTML = '<tr><td colspan="4">Nenhuma dispensação encontrada.</td></tr>'; return; }
                corpoTabela.innerHTML = '';
                dados.forEach(item => {
                    const r = {};
                    for (let k in item) { r[k.toLowerCase()] = item[k]; }
                    corpoTabela.innerHTML += `<tr><td style="font-size:11px">${r.data_registro||'---'}</td><td>${r.cpf_paciente||'---'}</td><td>${r.medicamento||'---'}</td><td><strong>${r.quantidade||'0'}</strong></td></tr>`;
                });
            } catch (erro) { corpoTabela.innerHTML = '<tr><td colspan="4">Erro ao conectar.</td></tr>'; }
        }

        // ── Procurar Pacientes ───────────────────────────────────────────────
        function procurarPacientes() {
            const modal = document.getElementById('modalProcurarPacientes');
            if (modal) {
                modal.style.display = 'flex';
                document.getElementById('buscaCpfPaciente').value = '';
                document.getElementById('resultadoBuscaCpf').innerHTML = '<tr><td colspan="3">Digite o CPF para pesquisar</td></tr>';
            }
        }
        document.getElementById('buscaCpfPaciente').addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 11) v = v.slice(0, 11);
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = v;
        });
        async function buscarPorCpf() {
            const cpfInput = document.getElementById('buscaCpfPaciente').value;
            const corpoTabela = document.getElementById('resultadoBuscaCpf');
            if (!cpfInput) { alert("Digite um CPF primeiro!"); return; }
            corpoTabela.innerHTML = '<tr><td colspan="3">Procurando no banco...</td></tr>';
            try {
                const resposta = await fetch(`procurar_dispensacao_cpf.php?cpf=${encodeURIComponent(cpfInput)}`);
                if (!resposta.ok) throw new Error(`Erro HTTP: ${resposta.status}`);
                const dados = await resposta.json();
                if (dados.erro) { corpoTabela.innerHTML = '<tr><td colspan="3">Erro ao buscar.</td></tr>'; return; }
                if (dados.length === 0) { corpoTabela.innerHTML = '<tr><td colspan="3">Nenhum registro encontrado.</td></tr>'; return; }
                corpoTabela.innerHTML = '';
                dados.forEach(item => {
                    const r = {};
                    for (let k in item) { r[k.toLowerCase()] = item[k]; }
                    corpoTabela.innerHTML += `<tr><td style="font-size:11px">${r.data_registro||'---'}</td><td>${r.medicamento||'---'}</td><td><strong>${r.quantidade||'0'}</strong></td></tr>`;
                });
            } catch (erro) { corpoTabela.innerHTML = '<tr><td colspan="3">Erro de conexão.</td></tr>'; }
        }
    </script>

</body>
</html>