<?php

namespace App\Libraries;

use Psr\Log\LoggerInterface;

/**
 * AuditLogger - Log de ações sensíveis
 * 
 * Registra ações importantes do sistema para
 * auditoria, debug e segurança
 */
class AuditLogger
{
    protected LoggerInterface $logger;
    
    // Categorias de ações
    const CATEGORY_AUTH = 'AUTH';
    const CATEGORY_ORDER = 'ORDER';
    const CATEGORY_PAYMENT = 'PAYMENT';
    const CATEGORY_REFUND = 'REFUND';
    const CATEGORY_EVENT = 'EVENT';
    const CATEGORY_USER = 'USER';
    const CATEGORY_ADMIN = 'ADMIN';
    const CATEGORY_SECURITY = 'SECURITY';

    public function __construct()
    {
        $this->logger = \Config\Services::logger();
    }

    /**
     * Log de autenticação
     */
    public function logAuth(string $action, ?int $userId = null, array $context = []): void
    {
        $this->log(self::CATEGORY_AUTH, $action, $userId, $context);
    }

    /**
     * Log de pedido
     */
    public function logOrder(string $action, ?int $userId = null, array $context = []): void
    {
        $this->log(self::CATEGORY_ORDER, $action, $userId, $context);
    }

    /**
     * Log de pagamento
     */
    public function logPayment(string $action, ?int $userId = null, array $context = []): void
    {
        $this->log(self::CATEGORY_PAYMENT, $action, $userId, $context);
    }

    /**
     * Log de reembolso
     */
    public function logRefund(string $action, ?int $userId = null, array $context = []): void
    {
        $this->log(self::CATEGORY_REFUND, $action, $userId, $context);
    }

    /**
     * Log de evento
     */
    public function logEvent(string $action, ?int $userId = null, array $context = []): void
    {
        $this->log(self::CATEGORY_EVENT, $action, $userId, $context);
    }

    /**
     * Log de usuário
     */
    public function logUser(string $action, ?int $userId = null, array $context = []): void
    {
        $this->log(self::CATEGORY_USER, $action, $userId, $context);
    }

    /**
     * Log de admin
     */
    public function logAdmin(string $action, ?int $userId = null, array $context = []): void
    {
        $this->log(self::CATEGORY_ADMIN, $action, $userId, $context);
    }

    /**
     * Log de segurança
     */
    public function logSecurity(string $action, ?int $userId = null, array $context = []): void
    {
        $this->log(self::CATEGORY_SECURITY, $action, $userId, $context, 'warning');
    }

    /**
     * Log genérico
     */
    protected function log(
        string $category, 
        string $action, 
        ?int $userId = null, 
        array $context = [],
        string $level = 'info'
    ): void {
        $request = \Config\Services::request();
        
        $logData = [
            'category' => $category,
            'action' => $action,
            'user_id' => $userId ?? $this->getCurrentUserId(),
            'ip' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
            'url' => current_url(),
            'method' => $request->getMethod(),
            'timestamp' => date('Y-m-d H:i:s'),
            'context' => $context,
        ];

        $message = sprintf(
            '[%s] %s | User: %s | IP: %s | %s',
            $category,
            $action,
            $logData['user_id'] ?? 'guest',
            $logData['ip'],
            !empty($context) ? json_encode($context) : ''
        );

        $this->logger->{$level}($message, $logData);
    }

    /**
     * Obter ID do usuário atual
     */
    protected function getCurrentUserId(): ?int
    {
        if (function_exists('auth') && auth()->loggedIn()) {
            return auth()->id();
        }
        return null;
    }

    /**
     * Log de compra de ingresso
     */
    public function logTicketPurchase(int $orderId, int $eventId, float $amount, array $tickets = []): void
    {
        $this->logOrder('TICKET_PURCHASE', null, [
            'order_id' => $orderId,
            'event_id' => $eventId,
            'amount' => $amount,
            'ticket_count' => count($tickets),
            'tickets' => array_map(fn($t) => $t['id'] ?? $t, $tickets),
        ]);
    }

    /**
     * Log de solicitação de reembolso
     */
    public function logRefundRequest(int $orderId, float $amount, string $reason = ''): void
    {
        $this->logRefund('REFUND_REQUEST', null, [
            'order_id' => $orderId,
            'amount' => $amount,
            'reason' => $reason,
        ]);
    }

    /**
     * Log de reembolso processado
     */
    public function logRefundProcessed(int $orderId, float $amount, string $status): void
    {
        $this->logRefund('REFUND_PROCESSED', null, [
            'order_id' => $orderId,
            'amount' => $amount,
            'status' => $status,
        ]);
    }

    /**
     * Log de falha de pagamento
     */
    public function logPaymentFailure(int $orderId, string $error, array $details = []): void
    {
        $this->logPayment('PAYMENT_FAILED', null, [
            'order_id' => $orderId,
            'error' => $error,
            'details' => $details,
        ]);
    }

    /**
     * Log de pagamento bem sucedido
     */
    public function logPaymentSuccess(int $orderId, float $amount, string $transactionId): void
    {
        $this->logPayment('PAYMENT_SUCCESS', null, [
            'order_id' => $orderId,
            'amount' => $amount,
            'transaction_id' => $transactionId,
        ]);
    }

    /**
     * Log de tentativa de acesso não autorizado
     */
    public function logUnauthorizedAccess(string $resource, string $details = ''): void
    {
        $this->logSecurity('UNAUTHORIZED_ACCESS', null, [
            'resource' => $resource,
            'details' => $details,
        ]);
    }

    /**
     * Log de rate limit excedido
     */
    public function logRateLimitExceeded(string $endpoint): void
    {
        $this->logSecurity('RATE_LIMIT_EXCEEDED', null, [
            'endpoint' => $endpoint,
        ]);
    }

    /**
     * Log de alteração de dados sensíveis
     */
    public function logDataChange(string $entity, int $entityId, array $changes): void
    {
        $this->logAdmin('DATA_CHANGE', null, [
            'entity' => $entity,
            'entity_id' => $entityId,
            'changes' => $changes,
        ]);
    }
}
