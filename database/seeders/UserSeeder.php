<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario Administrador
        User::create([
            'name' => 'Jeremy',
            'email' => 'jeremy.castillo@supercarnes.com',
            'password' => Hash::make('Jeremy0940$'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Usuario Regular
        User::create([
            'name' => 'Regular User',
            'email' => 'admin@supercarnes.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Usuario de Solo Lectura
        User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@supercarnes.com.com',
            'password' => Hash::make('password'),
            'role' => 'viewer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('✓ Usuarios creados exitosamente');
    }
}