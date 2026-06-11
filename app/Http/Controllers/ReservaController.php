<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReservaController extends Controller
{
    // Exibir o formulário público no site para os clientes
    public function publicCreate()
    {
        return view('reserva-publica');
    }

    // Gravar a reserva que o cliente fez no site público
    public function publicStore(Request $request)
    {
        $request->validate([
            'cliente_nome' => 'required|string|max:200',
            'cliente_contacto' => 'required|string|max:100',
            'data_reserva' => 'required|date',
            'hora' => 'required',
            'numero_pessoas' => 'required|integer|min:1|max:20',
        ]);

        DB::transaction(function () use ($request) {
            $cliente = Cliente::firstOrCreate(
                ['contacto' => $request->cliente_contacto],
                ['nome' => $request->cliente_nome]
            );

            Reserva::create([
                'id_cliente' => $cliente->id_cliente,
                'data_reserva' => $request->data_reserva,
                'hora' => $request->hora,
                'numero_pessoas' => $request->numero_pessoas,
                'estado' => 'pendente'
            ]);
        });

        return redirect()->route('home')->with('success', 'Reserva solicitada com sucesso!');
    }

    // -------------------------------------------------------
    // PAINEL DE GESTÃO (DASHBOARD DILIGENTE CONSOANTE O CARGO)
    // -------------------------------------------------------
    
    public function index()
    {
        // Carrega todas as reservas fazendo o elo com os clientes
        $reservas = Reserva::join('cliente', 'reserva.id_cliente', '=', 'cliente.id_cliente')
            ->select('reserva.*', 'cliente.nome as cliente_nome', 'cliente.contacto as cliente_contacto')
            ->orderBy('data_reserva', 'desc')
            ->orderBy('hora', 'desc')
            ->get();

        return view('auth.dashboard', compact('reservas')); 
    }

    // Atualizar/Editar o Estado do Pedido e Atendimento (Permitido ao Staff, Patrão e Admin)
    public function atualizarEstado(Request $request, $id)
    {
        if (!Gate::allows('operar-restaurante')) {
            abort(403, 'Não tem permissão para operar o restaurante.');
        }

        $request->validate([
            'estado' => 'required|string'
        ]);

        $reserva = Reserva::findOrFail($id);
        $reserva->update(['estado' => $request->estado]);

        return back()->with('success', 'Estado da reserva/pedido atualizado com sucesso!');
    }

    // Eliminar permanentemente uma reserva (BLOQUEADO AO STAFF - Só Admin/Patrão)
    public function destroy($id)
    {
        if (!Gate::allows('acesso-total')) {
            abort(403, 'Acesso Negado! O Staff não pode eliminar registos.');
        }

        $reserva = Reserva::findOrFail($id);
        $reserva->delete();

        return back()->with('success', 'Reserva eliminada do sistema pelo administrador.');
    }
}