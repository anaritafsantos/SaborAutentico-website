<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id('id_reserva');
            $table->string('cliente_nome');      // Nome do cliente (público)
            $table->string('cliente_contacto');  // Telemóvel (público)
            $table->date('data_reserva');
            $table->time('hora');
            $table->integer('numero_pessoas');
            $table->string('estado')->default('pendente'); // pendente, confirmada, cancelada
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('reservas');
    }
};