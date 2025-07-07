<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class PosSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear algunas sesiones de prueba con datos reales
        $users = \App\Models\User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Create users first.');
            return;
        }

        // Crear sesiones cerradas con ventas
        foreach ($users->take(3) as $user) {
            // Sesión cerrada con ventas
            $closedSession = \App\Models\PosSession::factory()->closed()->create([
                'user_id' => $user->id,
                'opened_at' => now()->subDays(rand(1, 7)),
            ]);

            // Crear algunas facturas para esta sesión
            $invoices = \App\Models\Invoice::factory(rand(5, 15))->create([
                'pos_session_id' => $closedSession->id,
                'user_id' => $user->id,
                'status' => 'paid',
                'payment_method' => Arr::random(['cash', 'card', 'transfer']),
            ]);

            // Sesión abierta actual
            if ($user->id === $users->first()->id) {
                $openSession = \App\Models\PosSession::factory()->open()->create([
                    'user_id' => $user->id,
                    'opened_at' => now()->subHours(rand(2, 8)),
                ]);

                // Algunas ventas en la sesión actual
                \App\Models\Invoice::factory(rand(2, 8))->create([
                    'pos_session_id' => $openSession->id,
                    'user_id' => $user->id,
                    'status' => 'paid',
                    'payment_method' => Arr::random(['cash', 'card']),
                ]);
            }
        }

        $this->command->info('POS Sessions with invoices created successfully!');
    }
}
