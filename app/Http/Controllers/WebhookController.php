<?php

namespace App\Http\Controllers;

use App\Models\WebhookEvent;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\Payments\PaymentProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function handle(
        Request $request,
        string $provider,
        PaymentGatewayManager $manager,
        PaymentProcessor $processor,
    ): JsonResponse {
        $gateway = $manager->resolve($provider);

        if (! $gateway->verifyWebhookSignature($request)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = $request->all();
        $result = $gateway->parseWebhook($payload);

        $alreadyProcessed = ! WebhookEvent::query()
            ->where('provider', $provider)
            ->where('external_id', $result->eventId)
            ->doesntExist();

        if ($alreadyProcessed) {
            return response()->json(['message' => 'Already processed'], 200);
        }

        DB::transaction(function () use ($provider, $result, $payload, $processor) {
            WebhookEvent::create([
                'provider' => $provider,
                'event_type' => $result->eventType,
                'external_id' => $result->eventId,
                'payload' => $payload,
                'signature_valid' => true,
                'processed_at' => now(),
            ]);

            $processor->confirmFromWebhook($result);
        });

        return response()->json(['message' => 'ok'], 200);
    }
}
