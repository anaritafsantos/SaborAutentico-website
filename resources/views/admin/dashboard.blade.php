<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Gestão - Sabor Autêntico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0d0b09; color: #f5f0e8; font-family: 'Montserrat', sans-serif; }
        .navbar { background-color: #161210; border-bottom: 1px solid #c9a84c33; }
        .card { background-color: #161210; border: 1px solid #c9a84c33; color: #f5f0e8; }
        .table { color: #f5f0e8; }
        .table thead { background-color: #c0392b; color: white; }
        .status-pendente { color: #c9a84c; font-weight: bold; }
        .status-confirmada { color: #27ae60; font-weight: bold; }
        .btn-confirm { background-color: #27ae60; border: none; color: white; }
        .btn-delete { background-color: #c0392b; border: none; color: white; }
        .user-badge { font-size: 0.7rem; padding: 4px 8px; border-radius: 4px; background: #c9a84c; color: #0d0b09; font-weight: bold; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg mb-4">
    <div class="container">
        <a class="navbar-brand text-white" href="#">Sabor <span>Autêntico</span> | Gestão</a>
        <div class="d-flex align-items-center">
            <span class="me-3">Olá, <strong>{{ Auth::user()->name }}</strong> </span>
            <span class="user-badge me-3">{{ strtoupper(Auth::user()->role) }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">Sair</button>
            </form>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">Lista de Reservas</h2>

            @if(session('success'))
                <div class="alert alert-success bg-success text-white border-0">{{ session('success') }}</div>
            @endif

            <div class="card p-4">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Contacto</th>
                            <th>Data/Hora</th>
                            <th>Pessoas</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservas as $reserva)
                            <tr class="align-middle">
                                <td>{{ $reserva->cliente_nome }}</td>
                                <td>{{ $reserva->cliente_contacto }}</td>
                                <td>{{ $reserva->data_reserva }} às {{ $reserva->hora }}</td>
                                <td>{{ $reserva->numero_pessoas }}</td>
                                <td class="status-{{ $reserva->estado }}">{{ ucfirst($reserva->estado) }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @if($reserva->estado == 'pendente')
                                            <form action="{{ route('reservas.confirmar', $reserva->id_reserva) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-confirm">Confirmar</button>
                                            </form>
                                        @endif

                                        {{-- SÓ ADMIN/PATRÃO PODE ELIMINAR --}}
                                        @can('acesso-total')
                                            <form action="{{ route('reservas.destroy', $reserva->id_reserva) }}" method="POST" onsubmit="return confirm('Tem a certeza?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-delete">Eliminar</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Nenhuma reserva encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @can('acesso-total')
                <div class="mt-4">
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-warning">Gerir Utilizadores (Admin Only)</a>
                </div>
            @endcan
        </div>
    </div>
</div>

</body>
</html>