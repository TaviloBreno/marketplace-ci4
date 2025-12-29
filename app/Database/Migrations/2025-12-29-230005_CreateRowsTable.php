<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRowsTable extends Migration
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
            ],
            'seats_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'row_number' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'curve_offset' => [
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
        $this->forge->addKey('sector_id');
        $this->forge->addForeignKey('sector_id', 'sectors', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rows');
    }

    public function down()
    {
        $this->forge->dropTable('rows');
    }
}
