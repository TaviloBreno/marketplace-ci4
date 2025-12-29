<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ResetUserPasswords extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:reset-passwords';
    protected $description = 'Reseta as senhas dos usuários de teste';

    public function run(array $params)
    {
        $users = auth()->getProvider();

        // Reset senha do admin
        $admin = $users->findByCredentials(['email' => 'admin@marketplace.com']);
        if ($admin) {
            $admin->fill(['password' => 'Admin@123']);
            $users->save($admin);
            
            // Garante que está no grupo superadmin
            if (!$admin->inGroup('superadmin')) {
                $admin->addGroup('superadmin');
            }
            
            CLI::write('✓ Admin: admin@marketplace.com / Admin@123', 'green');
        }

        // Reset senha do funcionário
        $funcionario = $users->findByCredentials(['email' => 'funcionario@marketplace.com']);
        if ($funcionario) {
            $funcionario->fill(['password' => 'Func@123']);
            $users->save($funcionario);
            
            // Garante que está no grupo user
            if (!$funcionario->inGroup('user')) {
                $funcionario->addGroup('user');
            }
            
            CLI::write('✓ Funcionário: funcionario@marketplace.com / Func@123', 'green');
        }

        CLI::write('');
        CLI::write('========================================', 'yellow');
        CLI::write('Senhas resetadas com sucesso!', 'yellow');
        CLI::write('========================================', 'yellow');
    }
}
