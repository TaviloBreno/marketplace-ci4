<?php

namespace App\Services;

use App\Config\Stripe as StripeConfig;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class OrganizerService
{
    protected StripeClient $stripe;
    protected StripeConfig $config;

    public function __construct()
    {
        $this->config = new StripeConfig();
        $this->stripe = new StripeClient($this->config->secretKey);
    }

    /**
     * Cria uma conta Connect para o organizador
     */
    public function createConnectAccount(array $data): array
    {
        try {
            // Criar conta Express no Stripe
            $account = $this->stripe->accounts->create([
                'type'         => 'express',
                'country'      => $this->config->country,
                'email'        => $data['email'],
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers'     => ['requested' => true],
                ],
                'business_type' => $data['business_type'] ?? 'individual',
                'business_profile' => [
                    'name' => $data['company_name'] ?? null,
                    'mcc'  => '7922', // Theatrical Producers and Ticket Agencies
                    'url'  => $data['website'] ?? null,
                ],
                'metadata' => [
                    'user_id'      => $data['user_id'],
                    'company_name' => $data['company_name'] ?? '',
                ],
            ]);

            return [
                'success'    => true,
                'account_id' => $account->id,
                'account'    => $account,
            ];
        } catch (ApiErrorException $e) {
            log_message('error', 'Stripe Account Creation Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Cria link de onboarding para o organizador
     */
    public function createAccountLink(string $accountId, string $returnUrl, string $refreshUrl): array
    {
        try {
            $accountLink = $this->stripe->accountLinks->create([
                'account'     => $accountId,
                'return_url'  => $returnUrl,
                'refresh_url' => $refreshUrl,
                'type'        => 'account_onboarding',
            ]);

            return [
                'success' => true,
                'url'     => $accountLink->url,
            ];
        } catch (ApiErrorException $e) {
            log_message('error', 'Stripe Account Link Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Verifica o status da conta do organizador
     */
    public function checkAccountStatus(string $accountId): array
    {
        try {
            $account = $this->stripe->accounts->retrieve($accountId);

            $status = 'pending';
            
            if ($account->charges_enabled && $account->payouts_enabled) {
                $status = 'active';
            } elseif ($account->requirements->disabled_reason) {
                $status = 'restricted';
            }

            return [
                'success'         => true,
                'status'          => $status,
                'charges_enabled' => $account->charges_enabled,
                'payouts_enabled' => $account->payouts_enabled,
                'requirements'    => $account->requirements,
                'account'         => $account,
            ];
        } catch (ApiErrorException $e) {
            log_message('error', 'Stripe Account Check Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Cria um login link para o dashboard do Stripe
     */
    public function createLoginLink(string $accountId): array
    {
        try {
            $loginLink = $this->stripe->accounts->createLoginLink($accountId);

            return [
                'success' => true,
                'url'     => $loginLink->url,
            ];
        } catch (ApiErrorException $e) {
            log_message('error', 'Stripe Login Link Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Processa o cadastro de organizador
     */
    public function processOrganizerRegistration(int $userId, array $data): array
    {
        $users = auth()->getProvider();
        $user = $users->findById($userId);

        if (!$user) {
            return [
                'success' => false,
                'error'   => 'Usuário não encontrado.',
            ];
        }

        // Criar conta no Stripe
        $stripeResult = $this->createConnectAccount([
            'email'         => $user->email,
            'user_id'       => $userId,
            'company_name'  => $data['company_name'],
            'business_type' => $data['business_type'] ?? 'individual',
            'website'       => $data['website'] ?? null,
        ]);

        if (!$stripeResult['success']) {
            return $stripeResult;
        }

        // Atualizar usuário
        $user->fill([
            'is_organizer'          => 1,
            'stripe_account_id'     => $stripeResult['account_id'],
            'stripe_account_status' => 'pending',
            'company_name'          => $data['company_name'],
            'document'              => $data['document'] ?? null,
            'phone'                 => $data['phone'] ?? null,
            'address'               => $data['address'] ?? null,
            'city'                  => $data['city'] ?? null,
            'state'                 => $data['state'] ?? null,
            'zip_code'              => $data['zip_code'] ?? null,
        ]);

        $users->save($user);

        // Criar link de onboarding
        $linkResult = $this->createAccountLink(
            $stripeResult['account_id'],
            base_url('organizer/onboarding-complete'),
            base_url('organizer/onboarding-refresh')
        );

        return [
            'success'        => true,
            'account_id'     => $stripeResult['account_id'],
            'onboarding_url' => $linkResult['url'] ?? null,
        ];
    }

    /**
     * Atualiza o status da conta do organizador
     */
    public function updateOrganizerStatus(int $userId): array
    {
        $users = auth()->getProvider();
        $user = $users->findById($userId);

        if (!$user || !$user->stripe_account_id) {
            return [
                'success' => false,
                'error'   => 'Conta Stripe não encontrada.',
            ];
        }

        $statusResult = $this->checkAccountStatus($user->stripe_account_id);

        if (!$statusResult['success']) {
            return $statusResult;
        }

        // Atualizar status no banco
        $user->fill([
            'stripe_account_status' => $statusResult['status'],
        ]);

        $users->save($user);

        return [
            'success' => true,
            'status'  => $statusResult['status'],
        ];
    }

    /**
     * Obtém o saldo da conta do organizador
     */
    public function getAccountBalance(string $accountId): array
    {
        try {
            $balance = $this->stripe->balance->retrieve([
                'stripe_account' => $accountId,
            ]);

            $available = 0;
            $pending = 0;

            foreach ($balance->available as $bal) {
                if ($bal->currency === $this->config->currency) {
                    $available = $bal->amount / 100;
                }
            }

            foreach ($balance->pending as $bal) {
                if ($bal->currency === $this->config->currency) {
                    $pending = $bal->amount / 100;
                }
            }

            return [
                'success'   => true,
                'available' => $available,
                'pending'   => $pending,
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }
}
