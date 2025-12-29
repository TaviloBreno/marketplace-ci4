<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class OrganizerFilter implements FilterInterface
{
    /**
     * Verifica se o usuário é um organizador
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Verifica se está logado
        if (!auth()->loggedIn()) {
            return redirect()->to('login')->with('error', 'Você precisa estar logado para acessar esta área.');
        }

        $user = auth()->user();
        
        // Verifica se é organizador
        if (!$user->is_organizer) {
            return redirect()->to('organizer/register')->with('info', 'Você precisa se cadastrar como organizador para acessar esta área.');
        }

        // Verifica se a conta Stripe está configurada
        if (empty($user->stripe_account_id)) {
            return redirect()->to('organizer/stripe/connect')->with('info', 'Você precisa conectar sua conta Stripe para continuar.');
        }

        // Verifica se o onboarding foi completado
        if (!$user->stripe_onboarding_complete) {
            return redirect()->to('organizer/stripe/onboarding')->with('info', 'Complete a configuração da sua conta Stripe para continuar.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Não faz nada após a requisição
    }
}
