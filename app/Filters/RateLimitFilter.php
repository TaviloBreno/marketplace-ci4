<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Rate Limit Filter
 * 
 * Limita o número de requisições por IP para prevenir abusos
 */
class RateLimitFilter implements FilterInterface
{
    /**
     * Número máximo de requisições permitidas
     */
    protected int $maxRequests = 60;

    /**
     * Janela de tempo em segundos (1 minuto)
     */
    protected int $timeWindow = 60;

    /**
     * Prefixo da chave no cache
     */
    protected string $cachePrefix = 'rate_limit_';

    /**
     * @param IncomingRequest $request
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Configurar limites personalizados baseado nos argumentos
        if (!empty($arguments)) {
            if (isset($arguments[0])) {
                $this->maxRequests = (int) $arguments[0];
            }
            if (isset($arguments[1])) {
                $this->timeWindow = (int) $arguments[1];
            }
        }

        $cache = \Config\Services::cache();
        $ip = $request->getIPAddress();
        $path = $request->getPath();
        
        // Criar chave única para IP + rota
        $key = $this->cachePrefix . md5($ip . '_' . $path);
        
        // Obter dados atuais
        $data = $cache->get($key);
        
        if ($data === null) {
            // Primeira requisição
            $data = [
                'count' => 1,
                'reset_at' => time() + $this->timeWindow
            ];
            $cache->save($key, $data, $this->timeWindow);
        } else {
            // Verificar se a janela de tempo expirou
            if (time() > $data['reset_at']) {
                $data = [
                    'count' => 1,
                    'reset_at' => time() + $this->timeWindow
                ];
                $cache->save($key, $data, $this->timeWindow);
            } else {
                // Incrementar contador
                $data['count']++;
                $cache->save($key, $data, $data['reset_at'] - time());
            }
        }

        // Verificar limite
        if ($data['count'] > $this->maxRequests) {
            $response = \Config\Services::response();
            
            // Headers de rate limit
            $response->setHeader('X-RateLimit-Limit', (string) $this->maxRequests);
            $response->setHeader('X-RateLimit-Remaining', '0');
            $response->setHeader('X-RateLimit-Reset', (string) $data['reset_at']);
            $response->setHeader('Retry-After', (string) ($data['reset_at'] - time()));
            
            // Verificar se é requisição AJAX
            if ($request->isAJAX()) {
                return $response->setJSON([
                    'success' => false,
                    'error' => 'Muitas requisições. Tente novamente em alguns segundos.',
                    'retry_after' => $data['reset_at'] - time()
                ])->setStatusCode(429);
            }
            
            return $response
                ->setStatusCode(429)
                ->setBody('Muitas requisições. Por favor, aguarde um momento.');
        }

        // Adicionar headers informativos
        $remaining = max(0, $this->maxRequests - $data['count']);
        $response = \Config\Services::response();
        $response->setHeader('X-RateLimit-Limit', (string) $this->maxRequests);
        $response->setHeader('X-RateLimit-Remaining', (string) $remaining);
        $response->setHeader('X-RateLimit-Reset', (string) $data['reset_at']);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
