<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQueuesTable extends Migration
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
            'sector_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'comment'    => 'Ex: A, B, C ou 1, 2, 3',
            ],
            'total_seats' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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
            'curve_angle' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Ângulo da curva em graus (0 = reta)',
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
        $this->forge->addKey('sector_id');
        $this->forge->addForeignKey('sector_id', 'sectors', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('queues');
    }

    public function down()
    {
        $this->forge->dropTable('queues');
    }
}
