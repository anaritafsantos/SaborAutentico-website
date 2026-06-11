<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Cliente;
use App\Models\Mesa;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;

class ClienteAuthController extends Controller
{
    public function showLogin() {
        if (Auth::guard('cliente')->check()) return redirect()->route('cliente.dashboard');
        return view('cliente.login');
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'contacto' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('cliente')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('cliente.dashboard'));
        }

        return back()->withErrors(['contacto' => 'Dados inválidos.']);
    }

    public function showRegister() {
        return view('cliente.register');
    }

    public function register(Request $request) {
        $request->validate([
            'nome' => 'required|string|max:255',
            'contacto' => 'required|string|unique:cliente,contacto',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $cliente = Cliente::create([
            'nome' => $request->nome,
            'contacto' => $request->contacto,
            'password' => Hash::make($request->password),
        ]);

        Auth::guard('cliente')->login($cliente);
        return redirect()->route('cliente.dashboard');
    }

    public function logout(Request $request) {
        Auth::guard('cliente')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function dashboard() {
        $cliente = Auth::guard('cliente')->user();
        $reservas = Reserva::where('id_cliente', $cliente->id_cliente)->get();
        return view('cliente.dashboard', compact('cliente', 'reservas'));
    }

    public function createReserva() {
        $mesas = Mesa::where('estado', 'livre')->get();
        return view('cliente.reserva-create', compact('mesas'));
    }

    public function storeReserva(Request $request) {
        $request->validate([
            'data_reserva' => 'required|date',
            'hora' => 'required',
            'numero_pessoas' => 'required|integer',
        ]);

        Reserva::create([
            'id_cliente' => Auth::guard('cliente')->id(),
            'data_reserva' => $request->data_reserva,
            'hora' => $request->hora,
            'numero_pessoas' => $request->numero_pessoas,
            'estado' => 'pendente',
        ]);

        return redirect()->route('cliente.dashboard')->with('success', 'Reserva criada!');
    }
}