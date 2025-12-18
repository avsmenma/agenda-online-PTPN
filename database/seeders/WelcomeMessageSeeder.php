<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\WelcomeMessage;
use Illuminate\Database\Seeder;

final class WelcomeMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $messages = [
            [
                'module' => 'IbuA',
                'message' => 'Selamat datang, Ibu Tarapul',
                'type' => WelcomeMessage::TYPE_PERSONAL,
                'is_active' => true,
            ],
            [
                'module' => 'ibuB',
                'message' => 'Selamat datang, Team Verifikasi',
                'type' => WelcomeMessage::TYPE_TEAM,
                'is_active' => true,
            ],
            [
                'module' => 'perpajakan',
                'message' => 'Selamat datang, Team Perpajakan',
                'type' => WelcomeMessage::TYPE_TEAM,
                'is_active' => true,
            ],
            [
                'module' => 'akutansi',
                'message' => 'Selamat datang, Team Akutansi',
                'type' => WelcomeMessage::TYPE_TEAM,
                'is_active' => true,
            ],
            [
                'module' => 'pembayaran',
                'message' => '{greeting}, Team Pembayaran!',
                'type' => WelcomeMessage::TYPE_TEAM,
                'is_active' => true,
            ],
            [
                'module' => 'general',
                'message' => '{greeting}! Selamat datang di Agenda Online PTPN',
                'type' => WelcomeMessage::TYPE_GENERAL,
                'is_active' => true,
            ],
        ];

        foreach ($messages as $message) {
            WelcomeMessage::updateOrCreate(
                ['module' => $message['module'], 'type' => $message['type']],
                [
                    'message' => $message['message'],
                    'is_active' => $message['is_active'],
                ]
            );
        }

        $this->command->info('✅ Welcome messages seeded successfully!');
    }
}
