<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fazer Reserva - Sabor Autêntico</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --red: #c0392b;
            --red-dk: #922b21;
            --gold: #c9a84c;
            --cream: #f5f0e8;
            --dark: #0d0b09;
        }
        body {
            background: var(--dark);
            color: var(--cream);
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100 vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: #161210;
            padding: 40px;
            border-radius: 4px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            max-width: 500px;
            width: 100%;
            border-top: 3px solid var(--red);
        }
        h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--cream);
            text-align: center;
        }
        p.subtitle {
            text-align: center;
            color: var(--gold);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 30px;
        }
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            color: rgba(245,240,232,0.6);
        }
        input {
            width: 100%;
            padding: 12px;
            background: #0d0b09;
            border: 1px solid rgba(201,168,76,0.2);
            color: var(--cream);
            font-family: inherit;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: var(--red);
        }
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: var(--red);
            color: white;
            border: none;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 2px;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        .btn-submit:hover { background: var(--red-dk); }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: rgba(245,240,232,0.4);
            text-decoration: none;
            font-size: 0.8rem;
        }
        .back-link:hover { color: var(--gold); }
        .error-msg {
            color: var(--red);
            font-size: 0.7rem;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Reservar Mesa</h2>
    <p class="subtitle">Sabor Autêntico</p>

    <form action="{{ route('reserva.publica.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>O Seu Nome</label>
            <input type="text" name="cliente_nome" value="{{ old('cliente_nome') }}" required placeholder="Ex: João Silva">
            @error('cliente_nome') <div class="error-msg">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Contacto Telefónico</label>
            <input type="text" name="cliente_contacto" value="{{ old('cliente_contacto') }}" required placeholder="Ex: 912 345 678">
            @error('cliente_contacto') <div class="error-msg">{{ $message }}</div> @enderror
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Data</label>
                <input type="date" name="data_reserva" value="{{ old('data_reserva') }}" required>
                @error('data_reserva') <div class="error-msg">{{ $message }}</div> @enderror
            </div>

            <div class="form-group" style="flex: 1;">
                <label>Hora</label>
                <input type="time" name="hora" value="{{ old('hora') }}" required>
                @error('hora') <div class="error-msg">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label>Número de Pessoas</label>
            <input type="number" name="numero_pessoas" value="{{ 1 }}" min="1" max="20" required>
            @error('numero_pessoas') <div class="error-msg">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn-submit">Confirmar Reserva</button>
    </form>

    <a href="{{ route('home') }}" class="back-link">← Voltar ao Início</a>
</div>

</body>
</html>