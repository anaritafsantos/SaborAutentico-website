<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sabor Autêntico — Restaurante</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,500;1,600&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --red:    #c0392b;
            --red-dk: #922b21;
            --gold:   #c9a84c;
            --cream:  #f5f0e8;
            --dark:   #0d0b09;
            --dark2:  #111010;
        }
        html { scroll-behavior: smooth; }
        body { background: var(--dark); color: var(--cream); font-family: 'Montserrat', sans-serif; line-height: 1.6; }

        /* ALERTAS */
        .alert-success { background: var(--gold); color: var(--dark); padding: 15px; text-align: center; font-weight: 600; position: sticky; top: 0; z-index: 1000; }

        /* NAVBAR */
        nav {
            position: sticky; top: 0; z-index: 100;
            background: rgba(13,11,9,.92); backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(201,168,76,.12);
            padding: 0 40px; display: flex; align-items: center; justify-content: space-between; height: 72px;
        }
        .nav-logo { font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; font-weight: 600; color: var(--cream); text-decoration: none; }
        .nav-logo span { color: var(--red); font-style: italic; }
        .nav-links { display: flex; gap: 36px; list-style: none; margin: 0; }
        .nav-links a { font-size: .68rem; font-weight: 500; letter-spacing: .22em; text-transform: uppercase; color: rgba(245,240,232,.65); text-decoration: none; }
        
        .nav-right { display: flex; gap: 15px; align-items: center; }
        .btn-nav-reservar { font-size: .65rem; font-weight: 600; letter-spacing: .18em; text-transform: uppercase; color: var(--cream); background: var(--red); text-decoration: none; padding: 10px 22px; transition: 0.3s; }
        .btn-nav-staff { font-size: .62rem; color: var(--gold); text-decoration: none; text-transform: uppercase; border: 1px solid var(--gold); padding: 9px 15px; letter-spacing: 1px; transition: 0.3s; }
        .btn-nav-staff:hover { background: var(--gold); color: var(--dark); }

        /* HERO */
        .hero { position: relative; height: calc(100vh - 72px); display: flex; align-items: center; background-image: url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; }
        .hero::before { content: ''; position: absolute; inset: 0; background: rgba(10,8,6,0.75); }
        .hero-content { position: relative; z-index: 2; padding: 0 80px; max-width: 850px; }
        .hero h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(3rem, 7vw, 5.5rem); font-weight: 300; line-height: 1.1; margin-bottom: 20px; }
        .hero h1 em { color: var(--red); font-style: italic; display: block; }
        .hero-btns { display: flex; gap: 20px; margin-top: 30px; }
        .btn-hero { display: inline-block; background: var(--red); color: white; text-decoration: none; padding: 16px 40px; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; transition: 0.3s; }
        .btn-hero-staff { display: inline-block; border: 1px solid rgba(255,255,255,0.3); color: white; text-decoration: none; padding: 16px 30px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; }
        .btn-hero-staff:hover { background: white; color: var(--dark); }

        /* SECTIONS */
        .sobre { display: grid; grid-template-columns: 1fr 1fr; background: #161210; }
        .sobre-text { padding: 80px; }
        .chef { background: var(--dark2); padding: 80px; text-align: center; }
        .chef-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; text-align: left; margin-top: 40px; }
        .menu-item { border-bottom: 1px solid rgba(245,240,232,0.1); padding: 15px 0; }
        
        .cta { padding: 100px 40px; text-align: center; background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=1200&q=80'); background-size: cover; }

        footer { background: #080706; padding: 40px 80px; border-top: 1px solid rgba(201,168,76,.12); display: flex; justify-content: space-between; align-items: center; }
        
        @media (max-width: 768px) { .sobre, .chef-grid, footer { grid-template-columns: 1fr; } .nav-links { display: none; } .hero-content { padding: 0 20px; } }
    </style>
</head>
<body>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <nav>
        <a href="{{ route('home') }}" class="nav-logo">Sabor <span>Autêntico</span></a>
        <ul class="nav-links">
            <li><a href="#home">Início</a></li>
            <li><a href="#sobre">Sobre</a></li>
            <li><a href="#chef">Menu</a></li>
        </ul>
        <div class="nav-right">
            <a href="{{ route('reserva.publica') }}" class="btn-nav-reservar">Reservar</a>
        </div>
    </nav>

    <section class="hero" id="home">
        <div class="hero-content">
            <p style="color:var(--gold); letter-spacing: 4px; text-transform: uppercase; font-size: 0.7rem;">Viana do Castelo</p>
            <h1>Bem-Vindos ao <em>Sabor Autêntico</em></h1>
            <p style="color: rgba(245,240,232,0.7); max-width: 500px;">A experiência gastronómica onde a tradição se encontra com o requinte.</p>
            <div class="hero-btns">
                <a href="{{ route('reserva.publica') }}" class="btn-hero">Reservar uma Mesa</a>
            </div>
        </div>
    </section>

    <section class="sobre" id="sobre">
        <div style="background-image: url('https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=900&q=80'); background-size: cover; min-height: 400px;"></div>
        <div class="sobre-text">
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 2.8rem; font-weight: 400;">Uma história de Sabor.</h2>
            <p style="color: rgba(245,240,232,0.6); margin-top: 20px;">Desde 2026, trazemos o melhor da nossa terra para a sua mesa, com ingredientes frescos e paixão em cada prato.</p>
        </div>
    </section>

    <section class="chef" id="chef">
        <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 3rem; margin-bottom: 30px;">Menu de Assinatura</h2>
        <div class="chef-grid">
            <div>
                <div class="menu-item"><h4>Bacalhau à Lagareiro</h4><p>Lombo de bacalhau assado com azeite e batatas a murro.</p></div>
                <div class="menu-item"><h4>Secretos de Porco Preto</h4><p>Grelhados na brasa com migas alentejanas.</p></div>
            </div>
            <div>
                <div class="menu-item"><h4>Polvo à Lagareiro</h4><p>Polvo tenro grelhado com alho confitado.</p></div>
                <div class="menu-item"><h4>Costeletão Maturado</h4><p>Carne premium com 30 dias de maturação.</p></div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="cta-inner">
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 2.5rem;">Pronto para nos visitar?</h2>
            <a href="{{ route('reserva.publica') }}" class="btn-hero" style="margin-top: 20px;">Fazer Reserva</a>
        </div>
    </section>

    <footer>
        <p style="font-size: 0.65rem; opacity: 0.3;">&copy; {{ date('Y') }} Sabor Autêntico. Todos os direitos reservados.</p>
        <div style="display:flex; gap: 20px;">
             <a href="{{ route('login') }}" style="color: rgba(245,240,232,0.3); font-size: 0.6rem; text-decoration: none; text-transform: uppercase; letter-spacing: 1px;">
                Acesso Equipa
            </a>
        </div>
    </footer>
</body>
</html>