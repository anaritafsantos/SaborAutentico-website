<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// --- 1. PÁGINA PRINCIPAL PUBLICITÁRIA ---
Route::get('/', function () {
    return view('welcome');
})->name('home');

// --- 2. SOLICITAÇÃO DE RESERVA PELO CLIENTE (Sem Login) ---
Route::get('/fazer-reserva', [ReservaController::class, 'publicCreate'])->name('reserva.publica');
Route::post('/fazer-reserva', [ReservaController::class, 'publicStore'])->name('reserva.publica.store');

// --- 3. CONTROLO DE AUTENTICAÇÃO DA EQUIPA ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- 4. PAINEL DE GESTÃO DO RESTAURANTE (Protegido por Autenticação) ---
Route::middleware(['auth'])->group(function () {
    
    // Entrar no Dashboard Geral
    Route::get('/dashboard', [ReservaController::class, 'index'])->name('reservas.index');
    
    // Ação Única Interativa para Atualizar Estados das Mesas/Pedidos
    Route::post('/reservas/{id}/confirmar', [ReservaController::class, 'atualizarEstado'])->name('reservas.confirmar');
    
    // Ação Destrutiva (Bloqueada ao Staff)
    Route::delete('/reservas/{id}', [ReservaController::class, 'destroy'])->name('reservas.destroy');

    // Gestão de Utilizadores (apenas Admin e Patrão)
    Route::middleware('can:acesso-total')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});