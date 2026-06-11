<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Restrito — Sabor Autêntico</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --red: #c0392b; --gold: #c9a84c; --cream: #f5f0e8; --dark: #0d0b09; }
        body { background: var(--dark); color: var(--cream); font-family: 'Montserrat', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .container { background: #161210; padding: 50px 40px; border-radius: 4px; box-shadow: 0 20px 50px rgba(0,0,0,0.8); max-width: 450px; width: 100%; border-top: 3px solid var(--gold); text-align: center; }
        h2 { font-family: 'Cormorant Garamond', serif; font-size: 2.2rem; margin-bottom: 5px; color: var(--cream); }
        p.subtitle { color: var(--red); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 35px; }
        .form-group { margin-bottom: 25px; text-align: left; }
        label { display: block; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; color: rgba(245,240,232,0.5); }
        input { width: 100%; padding: 12px; background: rgba(245,240,232,0.03); border: 1px solid rgba(201,168,76,0.3); color: var(--cream); font-family: inherit; transition: all 0.3s; }
        input:focus { outline: none; border-color: var(--gold); background: rgba(201,168,76,0.08); }
        .btn-submit { width: 100%; padding: 15px; background: var(--gold); color: var(--dark); border: none; text-transform: uppercase; font-weight: 600; letter-spacing: 2px; cursor: pointer; transition: background 0.3s; margin-top: 10px; }
        .btn-submit:hover { background: #b0913c; }
        .back-link { display: inline-block; margin-top: 25px; color: rgba(245,240,232,0.4); text-decoration: none; font-size: 0.75rem; letter-spacing: 1px; }
        .back-link:hover { color: var(--red); }
        .alert { background: rgba(192,57,43,.1); border-left: 3px solid var(--red); padding: 12px; margin-bottom: 20px; font-size: 0.8rem; color: #e07a70; text-align: left; }
    </style>
</head>
<body>

<div class="container">
    <h2>Painel de Gestão</h2>
    <p class="subtitle">Acesso Exclusivo à Equipa</p>

    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Email Institucional</label>
            <input type="email" name="email" value="{{ old('email') }}" required placeholder="exemplo@saborautentico.pt">
        </div>

        <div class="form-group">
            <label>Palavra-Passe</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn-submit">Entrar no Sistema</button>
    </form>

    <a href="{{ route('home') }}" class="back-link">← Voltar ao Site Público</a>
</div>

</body>
</html>