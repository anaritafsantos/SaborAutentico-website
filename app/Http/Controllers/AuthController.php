<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Mostrar o formulário de Login da Equipa
    public function showLogin() {
        return view('auth.login');
    }

    // Processar o Login
    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            // AQUI ESTAVA O ERRO! Agora manda para o /dashboard correto:
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['email' => 'As credenciais estão incorretas.']);
    }

    // Fazer Logout
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // Quando sai, volta para a página inicial pública do restaurante
        return redirect('/');
    }
}