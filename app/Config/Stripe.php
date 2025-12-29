<?php

namespace App\Config;

class Stripe
{
    /**
     * Stripe Secret Key
     */
    public string $secretKey = '';

    /**
     * Stripe Publishable Key
     */
    public string $publishableKey = '';

    /**
     * Stripe Webhook Secret
     */
    public string $webhookSecret = '';

    /**
     * Taxa de serviço da plataforma (em porcentagem)
     */
    public float $platformFeePercent = 10.0;

    /**
     * Moeda padrão
     */
    public string $currency = 'brl';

    /**
     * País padrão
     */
    public string $country = 'BR';

    public function __construct()
    {
        // Carregar do .env
        $this->secretKey = env('STRIPE_SECRET_KEY', '');
        $this->publishableKey = env('STRIPE_PUBLISHABLE_KEY', '');
        $this->webhookSecret = env('STRIPE_WEBHOOK_SECRET', '');
    }
}
