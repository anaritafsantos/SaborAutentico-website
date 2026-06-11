<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(['email' => 'admin@saborautentico.pt'], [
            'name' => 'Administrador',
            'password' => Hash::make('admin123'),
            'role' => 'admin'
        ]);

        User::updateOrCreate(['email' => 'patrao@saborautentico.pt'], [
            'name' => 'Gerência',
            'password' => Hash::make('patrao123'),
            'role' => 'patrao'
        ]);

        User::updateOrCreate(['email' => 'staff@saborautentico.pt'], [
            'name' => 'Empregado de Mesa',
            'password' => Hash::make('staff123'),
            'role' => 'staff'
        ]);
    }
}