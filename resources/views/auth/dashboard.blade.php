<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Gestão - Sabor Autêntico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0d0b09; color: #f5f0e8; font-family: 'Montserrat', sans-serif; }
        .navbar { background-color: #161210; border-bottom: 1px solid #c9a84c33; }
        .card { background-color: rgb(70, 55, 48); border: 1px solid #c9a84c33; color: #f5f0e8; }
        .table { color:rgb(152, 143, 128); border-color: #c9a84c22; }
        .table thead { background-color: #c0392b; color: white; }
        .table th{background-color: black; color: white;}
        .table td{background-color:rgb(70, 55, 48); color: white;}
        .table-hover tbody tr:hover { background-color: #221a16; color: #fff; }
        
        /* Badges de Estado Customizados */
        .badge-pendente { background-color: #dfa82b; color: #000; }
        .badge-confirmada { background-color: #27ae60; color: white; }
        .badge-preparacao { background-color: #2980b9; color: white; }
        .badge-servido { background-color: #8e44ad; color: white; }
        .badge-concluido { background-color: #7f8c8d; color: white; }

        .user-badge { font-size: 0.7rem; padding: 6px 12px; border-radius: 4px; font-weight: bold; }
        .badge-admin { background: #c0392b; color: white; }
        .badge-patrao { background: #c9a84c; color: #0d0b09; }
        .badge-staff { background: #2980b9; color: white; }
        
        select.form-select-sm { background-color: #0d0b09; color: #f5f0e8; border: 1px solid #c9a84c55; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg mb-4">
    <div class="container">
        <a class="navbar-brand text-white font-weight-bold" href="#">Sabor <span style="color:#c9a84c;">Autêntico</span> | Gestão</a>
        <div class="d-flex align-items-center">
            <span class="me-3">Olá, <strong>{{ Auth::user()->name }}</strong></span>
            
            @if(Auth::user()->role == 'admin')
                <span class="user-badge badge-admin">ADMINISTRADOR</span>
            @elseif(Auth::user()->role == 'patrao')
                <span class="user-badge badge-patrao">PATRÃO</span>
            @else
                <span class="user-badge badge-staff">STAFF / EQUIPA</span>
            @endif

            <form action="{{ route('logout') }}" method="POST" class="ms-3">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">Sair</button>
            </form>
        </div>
    </div>
</nav>

<div class="container">
    
    @if(session('success'))
        <div class="alert alert-success bg-success text-white border-0 shadow">{{ session('success') }}</div>
    @endif

    <div class="card p-3 mb-4 border-left border-warning">
        <h5 class="text-warning mb-2" style="font-size:0.9rem; letter-spacing:1px; text-transform:uppercase;">As suas permissões ativas:</h5>
        <p class="mb-0 text-muted small">
            @can('acesso-total')
                <span class="text-success">● Moderação Total</span> (Pode validar pedidos, alterar atribuição de mesas e apagar reservas do histórico).
            @else
                <span class="text-info">● Operação de Sala</span> (Pode gerir o fluxo das mesas e editar o estado de preparação dos pedidos. Eliminações bloqueadas).
            @endcan
        </p>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4 text-white">Controlo de Fluxo e Pedidos</h2>

            <div class="card p-4 shadow-lg">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-center">
                            <th>Cliente</th>
                            <th>Contacto</th>
                            <th>Data / Hora</th>
                            <th>Pessoas</th>
                            <th>Estado Atual</th>
                            <th>Ações / Editar Pedido</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservas as $reserva)
                            <tr class="text-center">
                                <td class="text-start fw-bold">{{ $reserva->cliente_nome }}</td>
                                <td>{{ $reserva->cliente_contacto }}</td>
                                <td>{{ \Carbon\Carbon::parse($reserva->data_reserva)->format('d/m/Y') }} às {{ $reserva->hora }}</td>
                                <td><span class="badge bg-secondary p-2">{{ $reserva->numero_pessoas }}</span></td>
                                
                                <td>
                                    @if($reserva->estado == 'pendente')
                                        <span class="badge badge-pendente p-2">Pendente</span>
                                    @elseif($reserva->estado == 'confirmada')
                                        <span class="badge badge-confirmada p-2">Confirmada</span>
                                    @elseif($reserva->estado == 'em_preparacao')
                                        <span class="badge badge-preparacao p-2">Em Preparação</span>
                                    @elseif($reserva->estado == 'servido')
                                        <span class="badge badge-servido p-2">Servido</span>
                                    @else
                                        <span class="badge badge-concluido p-2">Concluído</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        
                                        @can('operar-restaurante')
                                            <form action="/reservas/{{ $reserva->id_reserva }}/confirmar" method="POST" class="d-flex align-items-center gap-1">
                                                @csrf
                                                <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="">-- Mudar Estado --</option>
                                                    <option value="pendente" {{ $reserva->estado == 'pendente' ? 'selected' : '' }}>Pendente</option>
                                                    <option value="confirmada" {{ $reserva->estado == 'confirmada' ? 'selected' : '' }}>Confirmar Reserva</option>
                                                    <option value="em_preparacao" {{ $reserva->estado == 'em_preparacao' ? 'selected' : '' }}>Mesa em Preparação</option>
                                                    <option value="servido" {{ $reserva->estado == 'servido' ? 'selected' : '' }}>Pedido Servido</option>
                                                    <option value="concluido" {{ $reserva->estado == 'concluido' ? 'selected' : '' }}>Mesa Concluída / Livre</option>
                                                </select>
                                            </form>
                                        @endcan

                                        @can('acesso-total')
                                            <form action="{{ route('reservas.destroy', $reserva->id_reserva) }}" method="POST" onsubmit="return confirm('Tem a certeza que deseja apagar permanentemente esta reserva?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger px-3">Eliminar</button>
                                            </form>
                                        @endcan

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Não existem reservas ou pedidos ativos de momento.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @can('acesso-total')
                <div class="mt-4">
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-warning">Acesso ao Sistema: Gerir Utilizadores (Admin Only)</a>
                </div>
            @endcan

        </div>
    </div>
</div>

</body>
</html>