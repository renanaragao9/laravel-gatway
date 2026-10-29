@extends('checkout.layout')

@section('title', 'Pagamento Pix')

@section('content')
    <h1>Pagamento via Pix</h1>
    <p class="muted">Doação para <strong>{{ $ong->name }}</strong></p>
    <p>
        Valor: <strong>R$ {{ number_format($payment->amount_cents / 100, 2, ',', '.') }}</strong><br>
        Status: <span class="badge">{{ $payment->status }}</span>
    </p>

    @if ($payment->pix_qr_base64)
        <div class="qr">
            <img src="data:image/png;base64,{{ $payment->pix_qr_base64 }}" alt="QR Code Pix">
        </div>
    @endif

    @if ($payment->pix_payload)
        <label>Pix copia e cola</label>
        <div class="code">{{ $payment->pix_payload }}</div>
    @endif

    <p class="muted" style="margin-top:1.5rem">
        Após o pagamento, a confirmação chega automaticamente pelo gateway.
    </p>
@endsection
