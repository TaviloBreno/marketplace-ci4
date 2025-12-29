<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrganizerFieldsToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'is_organizer' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'active',
            ],
            'stripe_account_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'is_organizer',
            ],
            'stripe_account_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'active', 'restricted', 'rejected'],
                'default'    => 'pending',
                'after'      => 'stripe_account_id',
            ],
            'company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'stripe_account_status',
            ],
            'document' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'company_name',
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'document',
            ],
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'phone',
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'address',
            ],
            'state' => [
                'type'       => 'VARCHAR',
                'constraint' => 2,
                'null'       => true,
                'after'      => 'city',
            ],
            'zip_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'state',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', [
            'is_organizer',
            'stripe_account_id',
            'stripe_account_status',
            'company_name',
            'document',
            'phone',
            'address',
            'city',
            'state',
            'zip_code',
        ]);
    }
}
