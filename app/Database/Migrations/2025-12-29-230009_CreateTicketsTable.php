<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTicketsTable extends Migration
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
            'order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'seat_booking_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'ticket_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'qr_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'holder_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'holder_document' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'used', 'cancelled', 'transferred'],
                'default'    => 'active',
            ],
            'used_at' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addKey('order_id');
        $this->forge->addKey('seat_booking_id');
        $this->forge->addUniqueKey('ticket_code');
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('seat_booking_id', 'seat_bookings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tickets');
    }

    public function down()
    {
        $this->forge->dropTable('tickets');
    }
}
