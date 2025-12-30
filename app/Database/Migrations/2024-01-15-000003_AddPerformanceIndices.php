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
        $this->forge->addKey('status', false, false, 'idx_events_status');
        $this->forge->addKey('user_id', false, false, 'idx_events_user_id');
        $this->forge->addKey('venue_city', false, false, 'idx_events_venue_city');
        $this->forge->addKey(['status', 'deleted_at'], false, false, 'idx_events_status_deleted');
        $this->forge->processIndexes('events');
        
        // Índices na tabela event_days
        $this->forge->addKey('event_id', false, false, 'idx_event_days_event_id');
        $this->forge->addKey('event_date', false, false, 'idx_event_days_event_date');
        $this->forge->addKey(['event_id', 'event_date'], false, false, 'idx_event_days_event_date_compound');
        $this->forge->processIndexes('event_days');
        
        // Índices na tabela sectors
        $this->forge->addKey('event_day_id', false, false, 'idx_sectors_event_day_id');
        $this->forge->processIndexes('sectors');
        
        // Índices na tabela queues
        $this->forge->addKey('sector_id', false, false, 'idx_queues_sector_id');
        $this->forge->processIndexes('queues');
        
        // Índices na tabela seats
        $this->forge->addKey('queue_id', false, false, 'idx_seats_queue_id');
        $this->forge->addKey('status', false, false, 'idx_seats_status');
        $this->forge->addKey(['queue_id', 'status'], false, false, 'idx_seats_queue_status');
        $this->forge->processIndexes('seats');
        
        // Índices na tabela seat_bookings
        $this->forge->addKey('seat_id', false, false, 'idx_seat_bookings_seat_id');
        $this->forge->addKey('user_id', false, false, 'idx_seat_bookings_user_id');
        $this->forge->addKey('session_id', false, false, 'idx_seat_bookings_session_id');
        $this->forge->addKey('status', false, false, 'idx_seat_bookings_status');
        $this->forge->addKey('expires_at', false, false, 'idx_seat_bookings_expires_at');
        $this->forge->addKey(['status', 'expires_at'], false, false, 'idx_seat_bookings_status_expires');
        $this->forge->processIndexes('seat_bookings');
        
        // Índices na tabela orders
        $this->forge->addKey('user_id', false, false, 'idx_orders_user_id');
        $this->forge->addKey('event_id', false, false, 'idx_orders_event_id');
        $this->forge->addKey('status', false, false, 'idx_orders_status');
        $this->forge->addKey('created_at', false, false, 'idx_orders_created_at');
        $this->forge->processIndexes('orders');
        
        // Índices na tabela tickets
        $this->forge->addKey('order_id', false, false, 'idx_tickets_order_id');
        $this->forge->addKey('seat_id', false, false, 'idx_tickets_seat_id');
        $this->forge->addKey('user_id', false, false, 'idx_tickets_user_id');
        $this->forge->addKey('ticket_code', false, false, 'idx_tickets_ticket_code');
        $this->forge->addKey('status', false, false, 'idx_tickets_status');
        $this->forge->processIndexes('tickets');
    }

    public function down()
    {
        // Remover índices da tabela events
        $this->db->query('ALTER TABLE events DROP INDEX IF EXISTS idx_events_status');
        $this->db->query('ALTER TABLE events DROP INDEX IF EXISTS idx_events_user_id');
        $this->db->query('ALTER TABLE events DROP INDEX IF EXISTS idx_events_venue_city');
        $this->db->query('ALTER TABLE events DROP INDEX IF EXISTS idx_events_status_deleted');
        
        // Remover índices da tabela event_days
        $this->db->query('ALTER TABLE event_days DROP INDEX IF EXISTS idx_event_days_event_id');
        $this->db->query('ALTER TABLE event_days DROP INDEX IF EXISTS idx_event_days_event_date');
        $this->db->query('ALTER TABLE event_days DROP INDEX IF EXISTS idx_event_days_event_date_compound');
        
        // Remover índices da tabela sectors
        $this->db->query('ALTER TABLE sectors DROP INDEX IF EXISTS idx_sectors_event_day_id');
        
        // Remover índices da tabela queues
        $this->db->query('ALTER TABLE queues DROP INDEX IF EXISTS idx_queues_sector_id');
        
        // Remover índices da tabela seats
        $this->db->query('ALTER TABLE seats DROP INDEX IF EXISTS idx_seats_queue_id');
        $this->db->query('ALTER TABLE seats DROP INDEX IF EXISTS idx_seats_status');
        $this->db->query('ALTER TABLE seats DROP INDEX IF EXISTS idx_seats_queue_status');
        
        // Remover índices da tabela seat_bookings
        $this->db->query('ALTER TABLE seat_bookings DROP INDEX IF EXISTS idx_seat_bookings_seat_id');
        $this->db->query('ALTER TABLE seat_bookings DROP INDEX IF EXISTS idx_seat_bookings_user_id');
        $this->db->query('ALTER TABLE seat_bookings DROP INDEX IF EXISTS idx_seat_bookings_session_id');
        $this->db->query('ALTER TABLE seat_bookings DROP INDEX IF EXISTS idx_seat_bookings_status');
        $this->db->query('ALTER TABLE seat_bookings DROP INDEX IF EXISTS idx_seat_bookings_expires_at');
        $this->db->query('ALTER TABLE seat_bookings DROP INDEX IF EXISTS idx_seat_bookings_status_expires');
        
        // Remover índices da tabela orders
        $this->db->query('ALTER TABLE orders DROP INDEX IF EXISTS idx_orders_user_id');
        $this->db->query('ALTER TABLE orders DROP INDEX IF EXISTS idx_orders_event_id');
        $this->db->query('ALTER TABLE orders DROP INDEX IF EXISTS idx_orders_status');
        $this->db->query('ALTER TABLE orders DROP INDEX IF EXISTS idx_orders_created_at');
        
        // Remover índices da tabela tickets
        $this->db->query('ALTER TABLE tickets DROP INDEX IF EXISTS idx_tickets_order_id');
        $this->db->query('ALTER TABLE tickets DROP INDEX IF EXISTS idx_tickets_seat_id');
        $this->db->query('ALTER TABLE tickets DROP INDEX IF EXISTS idx_tickets_user_id');
        $this->db->query('ALTER TABLE tickets DROP INDEX IF EXISTS idx_tickets_ticket_code');
        $this->db->query('ALTER TABLE tickets DROP INDEX IF EXISTS idx_tickets_status');
    }
}
