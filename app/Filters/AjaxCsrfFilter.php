<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AJAX CSRF Filter
 * 
 * Valida CSRF token em requisições AJAX
 * O token deve ser enviado no header X-CSRF-TOKEN
 */
class AjaxCsrfFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Ignorar métodos seguros
        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return null;
        }

        // Verificar se é requisição AJAX
        if (!$request->isAJAX()) {
            return null;
        }

        $security = \Config\Services::security();
        
        // Obter token do header ou body
        $token = $request->getHeaderLine('X-CSRF-TOKEN');
        
        if (empty($token)) {
            // Tentar obter do body JSON
            $json = $request->getJSON(true);
            $token = $json['csrf_token'] ?? $json[csrf_token()] ?? null;
        }
        
        if (empty($token)) {
            // Tentar obter do POST
            $token = $request->getPost(csrf_token());
        }

        // Validar token
        if (empty($token) || !$security->verify($token)) {
            $response = \Config\Services::response();
            
            return $response->setJSON([
                'success' => false,
                'error' => 'Token de segurança inválido. Recarregue a página.',
                'csrf_error' => true
            ])->setStatusCode(403);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Adicionar novo token CSRF no header da resposta para AJAX
        if ($request->isAJAX()) {
            $response->setHeader('X-CSRF-TOKEN', csrf_hash());
        }
        
        return null;
    }
}
