@extends('checkout.layout')

@section('title', 'Doar para ' . $ong->name)

@section('content')
    <h1>Doar para {{ $ong->name }}</h1>
    @if ($ong->description)
        <p class="muted">{{ $ong->description }}</p>
    @endif

    <form method="POST" action="{{ route('checkout.store', $ong->id) }}">
        @csrf

        <label for="amount">Valor (R$)</label>
        <input type="number" step="0.01" min="1" id="amount" name="amount" value="{{ old('amount') }}" required>
        @error('amount') <div class="error">{{ $message }}</div> @enderror

        <label for="payer_name">Seu nome (opcional)</label>
        <input type="text" id="payer_name" name="payer_name" value="{{ old('payer_name') }}">
        @error('payer_name') <div class="error">{{ $message }}</div> @enderror

        <label for="payer_email">Seu e-mail (opcional)</label>
        <input type="email" id="payer_email" name="payer_email" value="{{ old('payer_email') }}">
        @error('payer_email') <div class="error">{{ $message }}</div> @enderror

        <button type="submit">Gerar Pix</button>
    </form>
@endsection
