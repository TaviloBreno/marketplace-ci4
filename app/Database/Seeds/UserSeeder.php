<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Obtém o provider de usuários do Shield
        $users = auth()->getProvider();
        $db = \Config\Database::connect();

        // ==========================================
        // Criar usuário Administrador/Organizador
        // ==========================================
        $existingAdmin = $users->findByCredentials(['email' => 'admin@marketplace.com']);
        
        if ($existingAdmin === null) {
            $admin = new User([
                'username' => 'admin',
                'email'    => 'admin@marketplace.com',
                'password' => 'Admin@123',
            ]);

            $users->save($admin);
            
            // Busca o usuário salvo para obter o ID
            $admin = $users->findById($users->getInsertID());
            
            // Adiciona ao grupo de administradores
            $admin->addGroup('superadmin');

            // Tornar organizador
            $db->table('users')->where('id', $admin->id)->update([
                'is_organizer'          => 1,
                'stripe_account_status' => 'active', // Simular conta ativa para testes
                'company_name'          => 'Eventos Admin Ltda',
                'document'              => '12.345.678/0001-90',
                'phone'                 => '(11) 99999-9999',
                'address'               => 'Rua dos Eventos, 123',
                'city'                  => 'São Paulo',
                'state'                 => 'SP',
                'zip_code'              => '01310-100',
            ]);

            echo "✓ Administrador/Organizador criado: admin@marketplace.com / Admin@123\n";
        } else {
            echo "! Administrador já existe: admin@marketplace.com\n";
            
            // Garantir que é organizador
            $db->table('users')->where('id', $existingAdmin->id)->update([
                'is_organizer'          => 1,
                'stripe_account_status' => 'active',
                'company_name'          => 'Eventos Admin Ltda',
                'document'              => '12.345.678/0001-90',
                'phone'                 => '(11) 99999-9999',
                'address'               => 'Rua dos Eventos, 123',
                'city'                  => 'São Paulo',
                'state'                 => 'SP',
                'zip_code'              => '01310-100',
            ]);
            echo "  → Atualizado como organizador\n";
        }

        // ==========================================
        // Criar usuário Cliente
        // ==========================================
        $existingFunc = $users->findByCredentials(['email' => 'cliente@marketplace.com']);
        
        if ($existingFunc === null) {
            $cliente = new User([
                'username' => 'cliente',
                'email'    => 'cliente@marketplace.com',
                'password' => 'Cliente@123',
            ]);

            $users->save($cliente);
            
            // Busca o usuário salvo para obter o ID
            $cliente = $users->findById($users->getInsertID());
            
            // Adiciona ao grupo de usuários comuns
            $cliente->addGroup('user');

            echo "✓ Cliente criado: cliente@marketplace.com / Cliente@123\n";
        } else {
            echo "! Cliente já existe: cliente@marketplace.com\n";
        }

        echo "\n========================================\n";
        echo "Processo concluído!\n";
        echo "========================================\n";
        echo "\nUsuários disponíveis:\n";
        echo "  Organizador: admin@marketplace.com / Admin@123\n";
        echo "  Cliente:     cliente@marketplace.com / Cliente@123\n";
        echo "========================================\n";
    }
}
