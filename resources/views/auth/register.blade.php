<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta — O Nosso Restaurante</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --red:#c0392b; --gold:#c9a84c; --cream:#f5f0e8; --dark:#0d0b09; }
        html, body { height: 100%; }
        body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            font-family: 'Montserrat', sans-serif;
            background: var(--dark);
            min-height: 100vh;
        }
        .panel-img {
            position: relative;
            background-image: url('https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=1200&q=80');
            background-size: cover; background-position: center;
        }
        .panel-img::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(10,8,6,.75) 0%, rgba(10,8,6,.4) 100%);
        }
        .panel-img-content {
            position: relative; z-index: 1; height: 100%;
            display: flex; flex-direction: column;
            justify-content: space-between; padding: 40px 50px;
        }
        .back-link {
            display: inline-flex; align-items: center; gap: 10px;
            font-size: .65rem; letter-spacing: .22em; text-transform: uppercase;
            color: rgba(245,240,232,.55); text-decoration: none; transition: color .3s;
        }
        .back-link:hover { color: var(--gold); }
        .back-link svg { width: 14px; height: 14px; }
        .panel-quote { padding-bottom: 20px; }
        .panel-quote blockquote {
            font-family: 'Cormorant Garamond', serif; font-style: italic;
            font-size: clamp(1.6rem, 3vw, 2.4rem); font-weight: 300;
            line-height: 1.35; color: var(--cream);
        }
        .panel-quote blockquote em { color: var(--red); font-style: normal; }
        .panel-quote cite {
            display: block; margin-top: 18px; font-size: .65rem;
            letter-spacing: .25em; text-transform: uppercase;
            color: rgba(245,240,232,.4); font-style: normal;
        }
        .panel-form {
            display: flex; flex-direction: column; justify-content: center;
            padding: 60px 70px; background: #100e0c;
        }
        .form-header { margin-bottom: 38px; }
        .form-eyebrow { font-size: .62rem; letter-spacing: .35em; text-transform: uppercase; color: var(--red); margin-bottom: 12px; }
        .form-header h1 { font-family: 'Cormorant Garamond', serif; font-size: 2.6rem; font-weight: 300; color: var(--cream); line-height: 1.15; }
        .form-header h1 em { font-style: italic; color: var(--gold); }
        .form-header p { margin-top: 12px; font-size: .8rem; font-weight: 300; color: rgba(245,240,232,.45); line-height: 1.7; }
        .alert { background: rgba(192,57,43,.12); border-left: 3px solid var(--red); padding: 12px 16px; margin-bottom: 28px; font-size: .78rem; color: #e07a70; border-radius: 0 4px 4px 0; }
        .field { margin-bottom: 20px; }
        .field label { display: block; font-size: .62rem; letter-spacing: .22em; text-transform: uppercase; color: rgba(245,240,232,.45); margin-bottom: 10px; }
        .field input {
            width: 100%; background: rgba(245,240,232,.04); border: 1px solid rgba(245,240,232,.1);
            color: var(--cream); font-family: 'Montserrat', sans-serif; font-size: .88rem;
            font-weight: 300; padding: 14px 18px; outline: none; transition: border-color .3s, background .3s;
        }
        .field input::placeholder { color: rgba(245,240,232,.2); }
        .field input:focus { border-color: var(--gold); background: rgba(201,168,76,.05); }
        .btn-submit {
            width: 100%; padding: 16px; background: var(--red); color: #fff;
            font-family: 'Montserrat', sans-serif; font-size: .7rem; font-weight: 600;
            letter-spacing: .25em; text-transform: uppercase; border: none;
            cursor: pointer; margin-top: 10px; transition: background .3s;
        }
        .btn-submit:hover { background: #a93226; }
        .form-footer {
            margin-top: 28px; padding-top: 22px;
            border-top: 1px solid rgba(245,240,232,.06);
            display: flex; justify-content: space-between; align-items: center;
        }
        .form-footer p { font-size: .75rem; font-weight: 300; color: rgba(245,240,232,.35); }
        .form-footer a { font-size: .75rem; font-weight: 400; color: var(--gold); text-decoration: none; transition: opacity .3s; }
        .form-footer a:hover { opacity: .7; }
        @media (max-width: 800px) {
            body { grid-template-columns: 1fr; }
            .panel-img { display: none; }
            .panel-form { padding: 50px 28px; }
        }
    </style>
</head>
<body>
    <div class="panel-img">
        <div class="panel-img-content">
            <a href="{{ route('home') }}" class="back-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Voltar ao início
            </a>
            <div class="panel-quote">
                <blockquote>"A sua mesa<br>favorita está<br>à sua <em>espera</em>"</blockquote>
                <cite>O Nosso Restaurante — Felgueiras</cite>
            </div>
        </div>
    </div>

    <div class="panel-form">
        <div class="form-header">
            <p class="form-eyebrow">Área de Clientes</p>
            <h1>Criar <em>conta</em></h1>
            <p>Registe-se gratuitamente para fazer reservas online e acompanhar o histórico das suas visitas.</p>
        </div>

        @if($errors->any())
            <div class="alert">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('cliente.register.post') }}" method="POST">
            @csrf
            <div class="field">
                <label>Nome completo</label>
                <input type="text" name="nome" placeholder="O seu nome" value="{{ old('nome') }}" required>
            </div>
            <div class="field">
                <label>Contacto (telemóvel)</label>
                <input type="text" name="contacto" placeholder="9XX XXX XXX" value="{{ old('contacto') }}" required>
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
            </div>
            <div class="field">
                <label>Confirmar Password</label>
                <input type="password" name="password_confirmation" placeholder="Repetir password" required>
            </div>
            <button type="submit" class="btn-submit">Criar Conta</button>
        </form>

        <div class="form-footer">
            <p>Já tem conta?</p>
            <a href="{{ route('cliente.login') }}">Iniciar sessão →</a>
        </div>
    </div>
</body>
</html>
