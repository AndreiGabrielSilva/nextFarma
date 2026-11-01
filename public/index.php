<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NextFarm - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --verde:      #2d6a4f;
            --verde-mid:  #40916c;
            --verde-claro:#74c69d;
            --creme:      #f8f5f0;
            --creme-dark: #ede8e0;
            --texto:      #1a2e22;
            --texto-muted:#6b7c6e;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--creme);
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* ── Painel esquerdo decorativo ── */
        .painel-esq {
            width: 52%;
            background-color: var(--verde);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: flex-end;
            padding: 3rem;
        }

        .painel-esq::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 80% 20%, rgba(116,198,157,0.25) 0%, transparent 70%),
                radial-gradient(ellipse 40% 60% at 10% 80%, rgba(29,53,36,0.6) 0%, transparent 70%);
        }

        /* Círculos decorativos */
        .circulo {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(116,198,157,0.2);
        }
        .c1 { width: 420px; height: 420px; top: -80px; right: -100px; }
        .c2 { width: 260px; height: 260px; top: 60px; right: 40px; background: rgba(64,145,108,0.15); }
        .c3 { width: 180px; height: 180px; bottom: 120px; left: -40px; border-color: rgba(116,198,157,0.15); }
        .c4 { width: 80px;  height: 80px;  bottom: 80px;  left: 60px; background: rgba(116,198,157,0.1); }

        /* Folha SVG decorativa */
        .folha-deco {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.07;
            width: 380px;
        }

        .painel-esq-texto {
            position: relative;
            z-index: 2;
        }
        .painel-esq-texto h2 {
            font-family: 'DM Serif Display', serif;
            font-style: italic;
            color: var(--verde-claro);
            font-size: 2rem;
            line-height: 1.3;
            margin-bottom: 0.75rem;
        }
        .painel-esq-texto p {
            color: rgba(255,255,255,0.5);
            font-size: 0.85rem;
            font-weight: 300;
            letter-spacing: 0.02em;
        }

        /* ── Painel direito (formulário) ── */
        .painel-dir {
            width: 48%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 4rem;
            background: var(--creme);
            position: relative;
        }

        .painel-dir::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(116,198,157,0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .login-box {
            width: 100%;
            max-width: 360px;
            animation: subir 0.7s cubic-bezier(0.16,1,0.3,1) both;
        }

        @keyframes subir {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .marca {
            margin-bottom: 2.5rem;
        }
        .marca-nome {
            font-family: 'DM Serif Display', serif;
            font-size: 2.4rem;
            color: var(--texto);
            letter-spacing: -0.02em;
            line-height: 1;
        }
        .marca-nome span {
            color: var(--verde-mid);
        }
        .marca-linha {
            width: 36px;
            height: 3px;
            background: var(--verde-claro);
            border-radius: 2px;
            margin-top: 10px;
        }
        .marca-sub {
            margin-top: 8px;
            font-size: 0.8rem;
            color: var(--texto-muted);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: 300;
        }

        .campo {
            margin-bottom: 1.25rem;
        }
        .campo label {
            display: block;
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--texto-muted);
            margin-bottom: 6px;
        }
        .campo input {
            width: 100%;
            height: 48px;
            border: 1.5px solid var(--creme-dark);
            border-radius: 10px;
            padding: 0 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            color: var(--texto);
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .campo input::placeholder { color: #b5bdb7; }
        .campo input:focus {
            border-color: var(--verde-mid);
            box-shadow: 0 0 0 3px rgba(64,145,108,0.12);
        }

        .btn-entrar {
            width: 100%;
            height: 50px;
            background: var(--verde);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
            letter-spacing: 0.04em;
            cursor: pointer;
            margin-top: 0.5rem;
            position: relative;
            overflow: hidden;
            transition: background 0.2s, transform 0.15s;
        }
        .btn-entrar:hover  { background: var(--verde-mid); }
        .btn-entrar:active { transform: scale(0.98); }
        .btn-entrar::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, transparent 60%);
            pointer-events: none;
        }

        #mensagem-erro {
            margin-top: 14px;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 400;
            display: none;
        }
        #mensagem-erro.erro  { background: #fdecea; color: #c0392b; }
        #mensagem-erro.sucesso { background: #eafaf1; color: #1e8449; }

        .rodape-login {
            margin-top: 2.5rem;
            font-size: 0.75rem;
            color: var(--texto-muted);
            text-align: center;
            opacity: 0.6;
        }

        /* Responsivo */
        @media (max-width: 700px) {
            .painel-esq { display: none; }
            .painel-dir { width: 100%; padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

    <!-- Painel esquerdo -->
    <div class="painel-esq">
        <div class="circulo c1"></div>
        <div class="circulo c2"></div>
        <div class="circulo c3"></div>
        <div class="circulo c4"></div>

        <svg class="folha-deco" viewBox="0 0 200 300" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M100 280 C100 280 10 200 10 120 C10 60 50 10 100 10 C150 10 190 60 190 120 C190 200 100 280 100 280Z" fill="white"/>
            <path d="M100 280 L100 60" stroke="white" stroke-width="3"/>
            <path d="M100 160 C70 130 40 120 20 110" stroke="white" stroke-width="2"/>
            <path d="M100 200 C130 170 160 160 180 150" stroke="white" stroke-width="2"/>
            <path d="M100 120 C75 95 55 85 35 75" stroke="white" stroke-width="1.5"/>
        </svg>

        <div class="painel-esq-texto">
            <h2>Gestão farmacêutica<br>com precisão.</h2>
            <p>Sistema integrado NextFarm — v2.0</p>
        </div>
    </div>

    <!-- Painel direito -->
    <div class="painel-dir">
        <div class="login-box">

            <div class="marca">
                <div class="marca-nome">Next<span>Farm</span></div>
                <div class="marca-linha"></div>
                <div class="marca-sub">Acesso ao sistema</div>
            </div>

            <form id="formLogin">
                <div class="campo">
                    <label for="cpf">CPF</label>
                    <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" maxlength="14" required autocomplete="username">
                </div>
                <div class="campo">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="••••••••" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn-entrar">Entrar</button>
                <div id="mensagem-erro"></div>
            </form>

            <p class="rodape-login">NextFarm &copy; <?php echo date('Y'); ?> — Uso interno</p>
        </div>
    </div>

    <script>
        // Máscara de CPF
        const cpfInput = document.getElementById('cpf');
        cpfInput.addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g, "");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            e.target.value = v;
        });

        // Envio AJAX
        document.getElementById('formLogin').addEventListener('submit', async (e) => {
            e.preventDefault();
            const erroDisplay = document.getElementById('mensagem-erro');
            const btn = e.target.querySelector('button[type="submit"]');
            btn.textContent = 'Verificando...';
            btn.disabled = true;

            try {
                const resposta = await fetch('processar_login.php', {
                    method: 'POST',
                    body: new FormData(e.target)
                });
                const resultado = await resposta.json();

                if (resultado.status === 'success') {
                    erroDisplay.className = 'sucesso';
                    erroDisplay.innerText = '✓ ' + resultado.message;
                    erroDisplay.style.display = 'block';
                    setTimeout(() => { window.location.href = 'dashboard.php'; }, 900);
                } else {
                    erroDisplay.className = 'erro';
                    erroDisplay.innerText = resultado.message;
                    erroDisplay.style.display = 'block';
                    btn.textContent = 'Entrar';
                    btn.disabled = false;
                }
            } catch (err) {
                erroDisplay.className = 'erro';
                erroDisplay.innerText = 'Erro ao conectar com o servidor.';
                erroDisplay.style.display = 'block';
                btn.textContent = 'Entrar';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>