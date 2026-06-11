<!DOCTYPE html>
<html lang="pt">
<head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-100 py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded shadow">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Gestão de Reservas</h2>
                <form action="{{ route('logout') }}" method="POST">@csrf <button class="text-red-600 underline">Sair</button></form>
            </div>
            <a href="{{ route('reservas.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">Nova Reserva</a>
            <table class="w-full mt-6 text-left border">
                <tr class="bg-gray-100"><th class="p-2 border">Data/Hora</th><th class="p-2 border">Cliente</th><th class="p-2 border">Estado</th></tr>
                @foreach($reservas as $reserva)
                <tr>
                    <td class="p-2 border">{{ \Carbon\Carbon::parse($reserva->data_reserva)->format('d/m/Y') }} às {{ $reserva->hora }}</td>
                    <td class="p-2 border">{{ $reserva->cliente->nome ?? '-' }}</td>
                    <td class="p-2 border uppercase">{{ $reserva->estado }}</td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
</body>
</html>