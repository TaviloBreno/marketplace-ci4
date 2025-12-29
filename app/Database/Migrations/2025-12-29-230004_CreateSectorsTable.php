<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSectorsTable extends Migration
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
            'event_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
                'default'    => '#3498db',
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'capacity' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'is_numbered' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1 = assentos numerados, 0 = pista/área geral',
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
            'width' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 200,
            ],
            'height' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 100,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addKey('event_id');
        $this->forge->addForeignKey('event_id', 'events', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sectors');
    }

    public function down()
    {
        $this->forge->dropTable('sectors');
    }
}
