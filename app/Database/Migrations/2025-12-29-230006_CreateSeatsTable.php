<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSeatsTable extends Migration
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
            'row_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'sector_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'seat_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'seat_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'position_x' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'position_y' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['available', 'blocked', 'reserved', 'sold'],
                'default'    => 'available',
            ],
            'is_wheelchair' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'is_companion' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'price_override' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
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
        $this->forge->addKey('row_id');
        $this->forge->addKey('sector_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('row_id', 'rows', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('sector_id', 'sectors', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('seats');
    }

    public function down()
    {
        $this->forge->dropTable('seats');
    }
}
