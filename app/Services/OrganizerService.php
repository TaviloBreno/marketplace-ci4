<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Account;
use Stripe\AccountLink;

class OrganizerService
{
    protected $stripe;

    public function __construct()
    {
        // Configura a API Key do Stripe
        Stripe::setApiKey(env('STRIPE_SECRET_KEY', ''));
    }

    /**
     * Cria uma conta Stripe Connect para o organizador
     */
    public function createStripeAccount($user): Account
    {
        $account = Account::create([
            'type'    => 'express', // ou 'standard' para mais controle
            'country' => 'BR',
            'email'   => $user->email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers'     => ['requested' => true],
            ],
            'business_type' => 'individual', // ou 'company'
            'business_profile' => [
                'name' => $user->company_name ?? $user->username,
                'mcc'  => '7922', // Código para entretenimento/eventos
                'url'  => base_url(),
            ],
            'metadata' => [
                'user_id'   => $user->id,
                'user_email' => $user->email,
            ],
        ]);

        return $account;
    }

    /**
     * Cria o link de onboarding do Stripe
     */
    public function createOnboardingLink(string $accountId, string $returnUrl, string $refreshUrl): string
    {
        $accountLink = AccountLink::create([
            'account'     => $accountId,
            'refresh_url' => $refreshUrl,
            'return_url'  => $returnUrl,
            'type'        => 'account_onboarding',
        ]);

        return $accountLink->url;
    }

    /**
     * Verifica o status da conta Stripe
     */
    public function checkAccountStatus(string $accountId): array
    {
        $account = Account::retrieve($accountId);

        $chargesEnabled = $account->charges_enabled ?? false;
        $payoutsEnabled = $account->payouts_enabled ?? false;
        $detailsSubmitted = $account->details_submitted ?? false;

        // Determina o status
        if ($chargesEnabled && $payoutsEnabled) {
            $status = 'active';
        } elseif ($detailsSubmitted) {
            $status = 'restricted';
        } else {
            $status = 'pending';
        }

        // Verifica requisitos pendentes
        $requirements = $account->requirements ?? null;
        $pendingVerification = [];
        $errors = [];

        if ($requirements) {
            $pendingVerification = $requirements->currently_due ?? [];
            $errors = $requirements->errors ?? [];
        }

        return [
            'status'               => $status,
            'charges_enabled'      => $chargesEnabled,
            'payouts_enabled'      => $payoutsEnabled,
            'details_submitted'    => $detailsSubmitted,
            'onboarding_complete'  => $chargesEnabled && $detailsSubmitted,
            'pending_verification' => $pendingVerification,
            'errors'               => $errors,
        ];
    }

    /**
     * Cria um link do dashboard Stripe para o organizador
     */
    public function createDashboardLink(string $accountId): string
    {
        $loginLink = \Stripe\Account::createLoginLink($accountId);
        return $loginLink->url;
    }

    /**
     * Obtém o balanço da conta do organizador
     */
    public function getAccountBalance(string $accountId): array
    {
        $balance = \Stripe\Balance::retrieve([
            'stripe_account' => $accountId,
        ]);

        $available = 0;
        $pending = 0;

        foreach ($balance->available as $bal) {
            if ($bal->currency === 'brl') {
                $available = $bal->amount / 100;
            }
        }

        foreach ($balance->pending as $bal) {
            if ($bal->currency === 'brl') {
                $pending = $bal->amount / 100;
            }
        }

        return [
            'available' => $available,
            'pending'   => $pending,
            'currency'  => 'BRL',
        ];
    }

    /**
     * Cria um Payment Intent para um pedido
     */
    public function createPaymentIntent(float $amount, string $organizerAccountId, array $metadata = []): \Stripe\PaymentIntent
    {
        // Calcula a taxa da plataforma (ex: 10%)
        $platformFee = $amount * 0.10;
        
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount'               => (int) ($amount * 100), // Em centavos
            'currency'             => 'brl',
            'payment_method_types' => ['card'],
            'application_fee_amount' => (int) ($platformFee * 100),
            'transfer_data' => [
                'destination' => $organizerAccountId,
            ],
            'metadata' => $metadata,
        ]);

        return $paymentIntent;
    }

    /**
     * Confirma um Payment Intent
     */
    public function confirmPaymentIntent(string $paymentIntentId): \Stripe\PaymentIntent
    {
        return \Stripe\PaymentIntent::retrieve($paymentIntentId);
    }

    /**
     * Processa reembolso
     */
    public function refund(string $chargeId, float $amount = null): \Stripe\Refund
    {
        $params = ['charge' => $chargeId];
        
        if ($amount !== null) {
            $params['amount'] = (int) ($amount * 100);
        }

        return \Stripe\Refund::create($params);
    }

    /**
     * Lista transferências para o organizador
     */
    public function listTransfers(string $accountId, int $limit = 10): array
    {
        $transfers = \Stripe\Transfer::all([
            'destination' => $accountId,
            'limit'       => $limit,
        ]);

        return $transfers->data;
    }
}
