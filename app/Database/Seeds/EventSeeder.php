<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run()
    {
        // Buscar usuário organizador (admin)
        $userModel = auth()->getProvider();
        $admin = $userModel->findByCredentials(['email' => 'admin@marketplace.com']);
        
        if (!$admin) {
            CLI::error('Usuário admin não encontrado. Execute UserSeeder primeiro.');
            return;
        }

        $userId = $admin->id;

        // Verificar se já existe evento
        $eventModel = model('EventModel');
        $existingEvent = $eventModel->where('user_id', $userId)->first();
        
        if ($existingEvent) {
            CLI::write('Eventos já existem para este usuário.', 'yellow');
            return;
        }

        // Criar evento de exemplo
        $eventData = [
            'user_id'       => $userId,
            'title'         => 'Show Rock in Rio - Teste',
            'slug'          => 'show-rock-in-rio-teste',
            'description'   => 'Um grande show de rock para testar o sistema de marketplace. Venha curtir uma noite incrível com as melhores bandas!',
            'venue_name'    => 'Cidade do Rock',
            'venue_address' => 'Av. Salvador Allende, 6555',
            'venue_city'    => 'Rio de Janeiro',
            'venue_state'   => 'RJ',
            'venue_zip_code'=> '22783-127',
            'category'      => 'show',
            'status'        => 'published',
            'is_featured'   => 1,
            'max_tickets_per_purchase' => 6,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $eventId = $eventModel->insert($eventData);

        if (!$eventId) {
            CLI::error('Erro ao criar evento: ' . implode(', ', $eventModel->errors()));
            return;
        }

        CLI::write("Evento criado com ID: {$eventId}", 'green');

        // Criar dias do evento
        $eventDayModel = model('EventDayModel');
        $days = [
            [
                'event_id'   => $eventId,
                'event_date' => date('Y-m-d', strtotime('+7 days')),
                'start_time' => '19:00:00',
                'end_time'   => '23:00:00',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'event_id'   => $eventId,
                'event_date' => date('Y-m-d', strtotime('+8 days')),
                'start_time' => '19:00:00',
                'end_time'   => '23:00:00',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($days as $day) {
            $eventDayModel->insert($day);
        }

        CLI::write('Dias do evento criados.', 'green');

        // Criar setores
        $sectorModel = model('SectorModel');
        $sectors = [
            [
                'event_id'   => $eventId,
                'name'       => 'Pista Premium',
                'price'      => 350.00,
                'color'      => '#6366f1',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'event_id'   => $eventId,
                'name'       => 'Pista',
                'price'      => 200.00,
                'color'      => '#22c55e',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'event_id'   => $eventId,
                'name'       => 'Arquibancada',
                'price'      => 100.00,
                'color'      => '#f59e0b',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $sectorIds = [];
        foreach ($sectors as $sector) {
            $sectorIds[] = $sectorModel->insert($sector);
        }

        CLI::write('Setores criados.', 'green');

        // Criar filas para cada setor
        $queueModel = model('QueueModel');
        $seatModel = model('SeatModel');

        foreach ($sectorIds as $index => $sectorId) {
            $queueCount = $index === 0 ? 2 : 3; // Premium tem menos filas
            
            for ($q = 1; $q <= $queueCount; $q++) {
                $queueData = [
                    'sector_id'  => $sectorId,
                    'name'       => 'Fila ' . chr(64 + $q), // A, B, C...
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $queueId = $queueModel->insert($queueData);

                // Criar assentos para cada fila
                $seatCount = $index === 0 ? 8 : 10; // Premium tem menos assentos
                
                for ($s = 1; $s <= $seatCount; $s++) {
                    $seatData = [
                        'queue_id'   => $queueId,
                        'code'       => chr(64 + $q) . str_pad($s, 2, '0', STR_PAD_LEFT),
                        'position_x' => ($s - 1) * 40,
                        'position_y' => ($q - 1) * 40,
                        'status'     => 'available',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];

                    $seatModel->insert($seatData);
                }
            }
        }

        CLI::write('Filas e assentos criados.', 'green');

        // Criar segundo evento (Teatro)
        $eventData2 = [
            'user_id'       => $userId,
            'title'         => 'O Fantasma da Ópera',
            'slug'          => 'o-fantasma-da-opera',
            'description'   => 'O clássico musical da Broadway chega ao Brasil! Uma história de amor, mistério e música que encanta gerações.',
            'venue_name'    => 'Teatro Municipal',
            'venue_address' => 'Praça Floriano, s/n',
            'venue_city'    => 'São Paulo',
            'venue_state'   => 'SP',
            'venue_zip_code'=> '01031-050',
            'category'      => 'teatro',
            'status'        => 'published',
            'is_featured'   => 1,
            'max_tickets_per_purchase' => 4,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $eventId2 = $eventModel->insert($eventData2);

        // Dias do evento 2
        for ($i = 14; $i <= 21; $i += 7) {
            $eventDayModel->insert([
                'event_id'   => $eventId2,
                'event_date' => date('Y-m-d', strtotime("+{$i} days")),
                'start_time' => '20:00:00',
                'end_time'   => '22:30:00',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Setores do evento 2
        $sectorTeatro1 = $sectorModel->insert([
            'event_id'   => $eventId2,
            'name'       => 'Plateia VIP',
            'price'      => 450.00,
            'color'      => '#ec4899',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $sectorTeatro2 = $sectorModel->insert([
            'event_id'   => $eventId2,
            'name'       => 'Plateia',
            'price'      => 280.00,
            'color'      => '#8b5cf6',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Filas e assentos do teatro
        foreach ([$sectorTeatro1, $sectorTeatro2] as $idx => $sectorId) {
            for ($q = 1; $q <= 4; $q++) {
                $queueId = $queueModel->insert([
                    'sector_id'  => $sectorId,
                    'name'       => 'Fila ' . $q,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                for ($s = 1; $s <= 12; $s++) {
                    $seatModel->insert([
                        'queue_id'   => $queueId,
                        'code'       => $q . '-' . str_pad($s, 2, '0', STR_PAD_LEFT),
                        'position_x' => ($s - 1) * 35,
                        'position_y' => ($q - 1) * 35,
                        'status'     => 'available',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        CLI::write("Evento Teatro criado com ID: {$eventId2}", 'green');

        // Criar terceiro evento (Esporte)
        $eventData3 = [
            'user_id'       => $userId,
            'title'         => 'Final do Campeonato Brasileiro',
            'slug'          => 'final-campeonato-brasileiro',
            'description'   => 'A grande final do Campeonato Brasileiro de Futebol! Não perca esse momento histórico.',
            'venue_name'    => 'Estádio Maracanã',
            'venue_address' => 'Av. Pres. Castelo Branco',
            'venue_city'    => 'Rio de Janeiro',
            'venue_state'   => 'RJ',
            'venue_zip_code'=> '20271-130',
            'category'      => 'esporte',
            'status'        => 'published',
            'is_featured'   => 0,
            'max_tickets_per_purchase' => 4,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $eventId3 = $eventModel->insert($eventData3);

        $eventDayModel->insert([
            'event_id'   => $eventId3,
            'event_date' => date('Y-m-d', strtotime('+30 days')),
            'start_time' => '16:00:00',
            'end_time'   => '18:00:00',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $sectorEsporte = $sectorModel->insert([
            'event_id'   => $eventId3,
            'name'       => 'Arquibancada Norte',
            'price'      => 150.00,
            'color'      => '#ef4444',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        for ($q = 1; $q <= 5; $q++) {
            $queueId = $queueModel->insert([
                'sector_id'  => $sectorEsporte,
                'name'       => 'Setor ' . chr(64 + $q),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            for ($s = 1; $s <= 15; $s++) {
                $seatModel->insert([
                    'queue_id'   => $queueId,
                    'code'       => chr(64 + $q) . $s,
                    'position_x' => ($s - 1) * 30,
                    'position_y' => ($q - 1) * 30,
                    'status'     => 'available',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        CLI::write("Evento Esporte criado com ID: {$eventId3}", 'green');

        CLI::write('========================================', 'white');
        CLI::write('Seeder de eventos concluído!', 'green');
        CLI::write('========================================', 'white');
        CLI::write('Eventos criados: 3', 'white');
        CLI::write('- Show Rock in Rio (show)', 'white');
        CLI::write('- O Fantasma da Ópera (teatro)', 'white');
        CLI::write('- Final Campeonato Brasileiro (esporte)', 'white');
    }
}
