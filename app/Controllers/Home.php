<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        // Se o usuário estiver logado, redireciona para a dashboard
        if (auth()->loggedIn()) {
            return redirect()->to('dashboard');
        }
        
        // Caso contrário, redireciona para o login
        return redirect()->to('login');
    }
}
