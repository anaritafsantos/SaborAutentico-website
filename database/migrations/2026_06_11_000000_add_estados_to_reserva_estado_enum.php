<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL não permite alterar ENUMs via Schema Builder — usa SQL direto.
        // IF NOT EXISTS evita erro se o valor já existir na BD.
        DB::statement("ALTER TYPE reserva_estado ADD VALUE IF NOT EXISTS 'em_preparacao'");
        DB::statement("ALTER TYPE reserva_estado ADD VALUE IF NOT EXISTS 'servido'");
        DB::statement("ALTER TYPE reserva_estado ADD VALUE IF NOT EXISTS 'concluido'");
    }

    public function down(): void
    {
        // PostgreSQL não suporta remover valores de um ENUM de forma segura.
        // Reverter manualmente só é possível recriando o tipo (não recomendado em produção).
    }
};
