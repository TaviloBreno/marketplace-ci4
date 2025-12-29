<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSeatBookingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'seat_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'event_day_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'session_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Para reservas temporárias de usuários não logados',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['reserved', 'confirmed', 'cancelled', 'expired'],
                'default'    => 'reserved',
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'reserved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'confirmed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'payment_intent_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('seat_id');
        $this->forge->addKey('event_day_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey(['seat_id', 'event_day_id']);
        $this->forge->addForeignKey('seat_id', 'seats', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('event_day_id', 'event_days', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('seat_bookings');
    }

    public function down()
    {
        $this->forge->dropTable('seat_bookings');
    }
}
