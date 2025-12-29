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

        // ==========================================
        // Criar usuário Administrador
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

            echo "✓ Administrador criado: admin@marketplace.com / Admin@123\n";
        } else {
            echo "! Administrador já existe: admin@marketplace.com\n";
        }

        // ==========================================
        // Criar usuário Funcionário
        // ==========================================
        $existingFunc = $users->findByCredentials(['email' => 'funcionario@marketplace.com']);
        
        if ($existingFunc === null) {
            $funcionario = new User([
                'username' => 'funcionario',
                'email'    => 'funcionario@marketplace.com',
                'password' => 'Func@123',
            ]);

            $users->save($funcionario);
            
            // Busca o usuário salvo para obter o ID
            $funcionario = $users->findById($users->getInsertID());
            
            // Adiciona ao grupo de usuários comuns
            $funcionario->addGroup('user');

            echo "✓ Funcionário criado: funcionario@marketplace.com / Func@123\n";
        } else {
            echo "! Funcionário já existe: funcionario@marketplace.com\n";
        }

        echo "\n========================================\n";
        echo "Processo concluído!\n";
        echo "========================================\n";
    }
}
