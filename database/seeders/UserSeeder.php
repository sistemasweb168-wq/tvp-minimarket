<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('nombre', 'Administrador')->first();
        $gerente = Role::where('nombre', 'Gerente')->first();
        $cajero = Role::where('nombre', 'Cajero')->first();

        User::create([
            'name' => 'Administrador del Sistema',
            'username' => 'admin',
            'email' => 'admin@tpvminimarket.com',
            'password' => Hash::make('admin123'),
            'role_id' => $admin->id,
            'telefono' => '999-888-777',
            'activo' => true,
        ]);

        User::create([
            'name' => 'Gerente Demo',
            'username' => 'gerente',
            'email' => 'gerente@tpvminimarket.com',
            'password' => Hash::make('gerente123'),
            'role_id' => $gerente->id,
            'activo' => true,
        ]);

        User::create([
            'name' => 'Cajero Demo',
            'username' => 'cajero',
            'email' => 'cajero@tpvminimarket.com',
            'password' => Hash::make('cajero123'),
            'role_id' => $cajero->id,
            'activo' => true,
        ]);
    }
}
