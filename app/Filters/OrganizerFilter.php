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
            return redirect()->to('login')->with('error', 'Você precisa fazer login para acessar esta área.');
        }

        $user = auth()->user();

        // Verifica se é organizador
        if (!$user->is_organizer) {
            return redirect()->to('organizer/become')->with('info', 'Você precisa se tornar um organizador para acessar esta área.');
        }

        // Verifica status da conta Stripe
        if ($user->stripe_account_status !== 'active') {
            return redirect()->to('organizer/account-status')->with('warning', 'Sua conta de organizador ainda não está ativa.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Não faz nada após a requisição
    }
}
