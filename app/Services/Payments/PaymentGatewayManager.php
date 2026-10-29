<?php

namespace App\Services\Payments;

use App\Models\GatewayAccount;
use App\Services\Payments\Contracts\PaymentGateway;
use App\Services\Payments\Drivers\AsaasGateway;
use App\Services\Payments\Exceptions\GatewayException;

class PaymentGatewayManager
{
    public function resolve(?string $provider = null): PaymentGateway
    {
        $provider ??= config('payments.default');

        $account = GatewayAccount::query()
            ->where('provider', $provider)
            ->where('active', true)
            ->first();

        if (! $account) {
            throw new GatewayException("Nenhuma conta de gateway ativa para o provedor [{$provider}].");
        }

        return $this->make($account);
    }

    public function make(GatewayAccount $account): PaymentGateway
    {
        return match ($account->provider) {
            'asaas' => $this->makeAsaas($account),
            default => throw new GatewayException("Provedor de gateway não suportado: [{$account->provider}]."),
        };
    }

    private function makeAsaas(GatewayAccount $account): AsaasGateway
    {
        $credentials = $account->credentials ?? [];

        $apiKey = $credentials['api_key'] ?? null;

        if (! $apiKey) {
            throw new GatewayException('Asaas: credencial [api_key] ausente na conta de gateway.');
        }

        $baseUrl = config("payments.providers.asaas.base_url.{$account->mode}");

        if (! $baseUrl) {
            throw new GatewayException("Asaas: base_url não configurada para o ambiente [{$account->mode}].");
        }

        return new AsaasGateway(
            apiKey: $apiKey,
            baseUrl: $baseUrl,
            webhookToken: $credentials['webhook_token'] ?? null,
            mode: $account->mode,
        );
    }
}
