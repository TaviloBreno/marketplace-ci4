<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventsTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'banner' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'venue_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'venue_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'venue_city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'venue_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 2,
            ],
            'venue_zip_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'published', 'cancelled', 'finished'],
                'default'    => 'draft',
            ],
            'is_featured' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'max_tickets_per_purchase' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 10,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addUniqueKey('slug');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('events');
    }

    public function down()
    {
        $this->forge->dropTable('events');
    }
}
