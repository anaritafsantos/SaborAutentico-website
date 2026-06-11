<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Utilizadores — Sabor Autêntico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0d0b09; color: #f5f0e8; font-family: 'Montserrat', sans-serif; }
        .navbar { background-color: #161210; border-bottom: 1px solid #c9a84c33; }
        .card { background-color: #161210; border: 1px solid #c9a84c33; color: #f5f0e8; }
        .table { color: #f5f0e8; border-color: #c9a84c22; }
        .table thead { background-color: #c0392b; color: white; }
        .table-hover tbody tr:hover { background-color: #221a16; color: #fff; }
        .user-badge { font-size: 0.7rem; padding: 6px 12px; border-radius: 4px; font-weight: bold; }
        .badge-admin { background: #c0392b; color: white; }
        .badge-patrao { background: #c9a84c; color: #0d0b09; }
        .badge-staff { background: #2980b9; color: white; }
        .btn-gold { background-color: #c9a84c; color: #0d0b09; border: none; font-weight: 600; }
        .btn-gold:hover { background-color: #b0913c; color: #0d0b09; }
        .btn-red { background-color: #c0392b; color: white; border: none; }
        .btn-red:hover { background-color: #a93226; color: white; }
        .modal-content { background-color: #161210; color: #f5f0e8; border: 1px solid #c9a84c33; }
        .modal-header { border-bottom: 1px solid #c9a84c22; }
        .modal-footer { border-top: 1px solid #c9a84c22; }
        .form-control, .form-select {
            background-color: #0d0b09; color: #f5f0e8;
            border: 1px solid #c9a84c55;
        }
        .form-control:focus, .form-select:focus {
            background-color: #0d0b09; color: #f5f0e8;
            border-color: #c9a84c; box-shadow: 0 0 0 0.2rem rgba(201,168,76,.15);
        }
        .form-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(245,240,232,.6); }
        .btn-close { filter: invert(1); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg mb-4">
    <div class="container">
        <a class="navbar-brand text-white" href="{{ route('reservas.index') }}">
            Sabor <span style="color:#c9a84c;">Autêntico</span> | Utilizadores
        </a>
        <div class="d-flex align-items-center gap-3">
            <span>Olá, <strong>{{ Auth::user()->name }}</strong></span>
            <a href="{{ route('reservas.index') }}" class="btn btn-outline-light btn-sm">← Dashboard</a>
            <form action="{{ route('logout') }}" method="POST">
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

    @if($errors->any())
        <div class="alert alert-danger bg-danger text-white border-0 shadow">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Gestão de Utilizadores</h2>
        <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#modalCriar">
            + Novo Utilizador
        </button>
    </div>

    <div class="card p-4 shadow-lg">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-center">
                    <th class="text-start">Nome</th>
                    <th>Email</th>
                    <th>Função</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="text-center">
                        <td class="text-start fw-bold">
                            {{ $user->name }}
                            @if($user->id === Auth::id())
                                <span class="badge bg-secondary ms-1" style="font-size:0.6rem;">VOCÊ</span>
                            @endif
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="user-badge badge-admin">ADMIN</span>
                            @elseif($user->role === 'patrao')
                                <span class="user-badge badge-patrao">PATRÃO</span>
                            @else
                                <span class="user-badge badge-staff">STAFF</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center">
                                <button class="btn btn-sm btn-gold"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditar{{ $user->id }}">
                                    Editar
                                </button>

                                @if($user->id !== Auth::id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                          onsubmit="return confirm('Eliminar {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-red">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Modal Editar --}}
                    <div class="modal fade" id="modalEditar{{ $user->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Editar Utilizador</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Nome</label>
                                            <input type="text" name="name" class="form-control"
                                                   value="{{ $user->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control"
                                                   value="{{ $user->email }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Função</label>
                                            <select name="role" class="form-select" required>
                                                <option value="admin"  {{ $user->role === 'admin'  ? 'selected' : '' }}>Administrador</option>
                                                <option value="patrao" {{ $user->role === 'patrao' ? 'selected' : '' }}>Patrão</option>
                                                <option value="staff"  {{ $user->role === 'staff'  ? 'selected' : '' }}>Staff</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-gold btn-sm">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Nenhum utilizador encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Criar --}}
<div class="modal fade" id="modalCriar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Novo Utilizador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Palavra-passe</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Função</label>
                        <select name="role" class="form-select" required>
                            <option value="staff"  {{ old('role') === 'staff'  ? 'selected' : '' }}>Staff</option>
                            <option value="patrao" {{ old('role') === 'patrao' ? 'selected' : '' }}>Patrão</option>
                            <option value="admin"  {{ old('role') === 'admin'  ? 'selected' : '' }}>Administrador</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-red btn-sm">Criar Utilizador</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@if($errors->any() && old('_method') !== 'PUT')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            new bootstrap.Modal(document.getElementById('modalCriar')).show();
        });
    </script>
@endif

</body>
</html>
