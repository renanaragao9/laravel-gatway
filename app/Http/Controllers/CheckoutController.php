<?php

namespace App\Http\Controllers;

use App\Models\Ong;
use App\Models\Payment;
use App\Services\Payments\PaymentProcessor;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function show(Ong $ong)
    {
        abort_unless($ong->active, 404);

        return view('checkout.show', ['ong' => $ong]);
    }

    public function store(Request $request, Ong $ong, PaymentProcessor $processor)
    {
        abort_unless($ong->active, 404);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payer_name' => ['nullable', 'string', 'max:255'],
            'payer_email' => ['nullable', 'email', 'max:255'],
        ]);

        $amountCents = (int) round(((float) $data['amount']) * 100);

        [$payment, $charge] = $processor->createCharge(
            ong: $ong,
            amountCents: $amountCents,
            payerName: $data['payer_name'] ?? null,
            payerEmail: $data['payer_email'] ?? null,
        );

        return redirect()->route('checkout.pay', [
            'ong' => $ong->id,
            'payment' => $payment->id,
        ]);
    }

    public function pay(Ong $ong, Payment $payment)
    {
        abort_unless($payment->ong_id === $ong->id, 404);

        return view('checkout.pay', [
            'ong' => $ong,
            'payment' => $payment,
        ]);
    }
}
