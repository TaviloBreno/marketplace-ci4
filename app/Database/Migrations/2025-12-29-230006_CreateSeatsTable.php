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
            'queue_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'number' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Ex: A-12, VIP-1',
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
                'constraint' => ['available', 'blocked', 'maintenance'],
                'default'    => 'available',
            ],
            'is_accessible' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => 'Assento para PCD',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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
        $this->forge->addKey('queue_id');
        $this->forge->addForeignKey('queue_id', 'queues', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('seats');
    }

    public function down()
    {
        $this->forge->dropTable('seats');
    }
}
