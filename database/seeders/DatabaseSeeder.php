<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Conta Admin (Default)
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'], // Podes mudar o email se quiseres
            [
                'name' => 'Admin',
                'password' => Hash::make('admin'),
                'role' => 'admin',
            ]
        );

        // Conta do Patrão (Administrativa)
        User::updateOrCreate(
            ['email' => 'patrao@gmail.com'],
            [
                'name' => 'Patrão',
                'password' => Hash::make('patrao123'),
                'role' => 'admin',
            ]
        );

        // Conta de Staff Exemplo
        User::updateOrCreate(
            ['email' => 'staff@gmail.com'],
            [
                'name' => 'Funcionário 1',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
            ]
        );
    }
}