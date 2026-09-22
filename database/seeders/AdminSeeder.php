<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'name' => 'admin',
                'email' => 'admin@almuhajirin.sch.id',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'adminppdb',
                'email' => 'admin@ppdb.sch.id',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'panitia',
                'email' => 'panitia@almuhajirin.sch.id',
                'password' => Hash::make('panitia123'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($admins as $adminData) {
            User::updateOrCreate(
                ['email' => $adminData['email']],
                $adminData
            );
        }

        if ($this->command) {
            $this->command->info('Seeder Admin PPDB berhasil dijalankan!');
            $this->command->line('--------------------------------------------------');
            $this->command->line('Akun 1: username [admin] atau email [admin@almuhajirin.sch.id] | Password: [admin123]');
            $this->command->line('Akun 2: username [adminppdb] atau email [admin@ppdb.sch.id] | Password: [admin123]');
            $this->command->line('Akun 3: username [panitia] atau email [panitia@almuhajirin.sch.id] | Password: [panitia123]');
            $this->command->line('--------------------------------------------------');
        }
    }
}
