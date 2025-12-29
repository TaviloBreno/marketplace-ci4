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
            'stripe_onboarding_complete' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'stripe_account_status',
            ],
            'company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'stripe_onboarding_complete',
            ],
            'company_document' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'company_name',
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'company_document',
            ],
            'address' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'phone',
            ],
        ]);

        // Índices
        $this->forge->addKey('is_organizer', false, false, 'idx_is_organizer');
        $this->forge->addKey('stripe_account_id', false, false, 'idx_stripe_account_id');
    }

    public function down()
    {
        $this->forge->dropColumn('users', [
            'is_organizer',
            'stripe_account_id',
            'stripe_account_status',
            'stripe_onboarding_complete',
            'company_name',
            'company_document',
            'phone',
            'address',
        ]);
    }
}
