<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adiciona índices para otimização de performance
 * 
 * Índices nas colunas mais frequentemente consultadas
 * para melhorar a velocidade das queries
 */
class AddPerformanceIndices extends Migration
{
    public function up()
    {
        // Índices na tabela events
        $this->createIndexIfNotExists('events', 'idx_events_status', 'status');
        $this->createIndexIfNotExists('events', 'idx_events_user_id', 'user_id');
        $this->createIndexIfNotExists('events', 'idx_events_venue_city', 'venue_city');
        
        // Índices na tabela event_days
        $this->createIndexIfNotExists('event_days', 'idx_event_days_event_id', 'event_id');
        $this->createIndexIfNotExists('event_days', 'idx_event_days_event_date', 'event_date');
        
        // Índices na tabela sectors
        $this->createIndexIfNotExists('sectors', 'idx_sectors_event_id', 'event_id');
        
        // Índices na tabela queues
        $this->createIndexIfNotExists('queues', 'idx_queues_sector_id', 'sector_id');
        
        // Índices na tabela seats
        $this->createIndexIfNotExists('seats', 'idx_seats_queue_id', 'queue_id');
        $this->createIndexIfNotExists('seats', 'idx_seats_status', 'status');
        
        // Índices na tabela seat_bookings
        $this->createIndexIfNotExists('seat_bookings', 'idx_seat_bookings_seat_id', 'seat_id');
        $this->createIndexIfNotExists('seat_bookings', 'idx_seat_bookings_user_id', 'user_id');
        $this->createIndexIfNotExists('seat_bookings', 'idx_seat_bookings_session_id', 'session_id');
        $this->createIndexIfNotExists('seat_bookings', 'idx_seat_bookings_status', 'status');
        $this->createIndexIfNotExists('seat_bookings', 'idx_seat_bookings_expires_at', 'expires_at');
        
        // Índices na tabela orders
        $this->createIndexIfNotExists('orders', 'idx_orders_user_id', 'user_id');
        $this->createIndexIfNotExists('orders', 'idx_orders_event_id', 'event_id');
        $this->createIndexIfNotExists('orders', 'idx_orders_status', 'status');
        $this->createIndexIfNotExists('orders', 'idx_orders_created_at', 'created_at');
        
        // Índices na tabela tickets
        $this->createIndexIfNotExists('tickets', 'idx_tickets_order_id', 'order_id');
        $this->createIndexIfNotExists('tickets', 'idx_tickets_seat_booking_id', 'seat_booking_id');
        $this->createIndexIfNotExists('tickets', 'idx_tickets_ticket_code', 'ticket_code');
        $this->createIndexIfNotExists('tickets', 'idx_tickets_status', 'status');
    }

    public function down()
    {
        // Remover índices da tabela events
        $this->dropIndexIfExists('events', 'idx_events_status');
        $this->dropIndexIfExists('events', 'idx_events_user_id');
        $this->dropIndexIfExists('events', 'idx_events_venue_city');
        
        // Remover índices da tabela event_days
        $this->dropIndexIfExists('event_days', 'idx_event_days_event_id');
        $this->dropIndexIfExists('event_days', 'idx_event_days_event_date');
        
        // Remover índices da tabela sectors
        $this->dropIndexIfExists('sectors', 'idx_sectors_event_id');
        
        // Remover índices da tabela queues
        $this->dropIndexIfExists('queues', 'idx_queues_sector_id');
        
        // Remover índices da tabela seats
        $this->dropIndexIfExists('seats', 'idx_seats_queue_id');
        $this->dropIndexIfExists('seats', 'idx_seats_status');
        
        // Remover índices da tabela seat_bookings
        $this->dropIndexIfExists('seat_bookings', 'idx_seat_bookings_seat_id');
        $this->dropIndexIfExists('seat_bookings', 'idx_seat_bookings_user_id');
        $this->dropIndexIfExists('seat_bookings', 'idx_seat_bookings_session_id');
        $this->dropIndexIfExists('seat_bookings', 'idx_seat_bookings_status');
        $this->dropIndexIfExists('seat_bookings', 'idx_seat_bookings_expires_at');
        
        // Remover índices da tabela orders
        $this->dropIndexIfExists('orders', 'idx_orders_user_id');
        $this->dropIndexIfExists('orders', 'idx_orders_event_id');
        $this->dropIndexIfExists('orders', 'idx_orders_status');
        $this->dropIndexIfExists('orders', 'idx_orders_created_at');
        
        // Remover índices da tabela tickets
        $this->dropIndexIfExists('tickets', 'idx_tickets_order_id');
        $this->dropIndexIfExists('tickets', 'idx_tickets_seat_booking_id');
        $this->dropIndexIfExists('tickets', 'idx_tickets_ticket_code');
        $this->dropIndexIfExists('tickets', 'idx_tickets_status');
    }
    
    /**
     * Cria um índice se ele não existir
     */
    private function createIndexIfNotExists(string $table, string $indexName, string $columns): void
    {
        $query = $this->db->query("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        
        if ($query->getNumRows() === 0) {
            $this->db->query("CREATE INDEX {$indexName} ON {$table}({$columns})");
        }
    }
    
    /**
     * Remove um índice se ele existir
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $query = $this->db->query("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        
        if ($query->getNumRows() > 0) {
            $this->db->query("DROP INDEX {$indexName} ON {$table}");
        }
    }
}
