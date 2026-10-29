@extends('checkout.layout')

@section('title', 'Doe para uma ONG')

@section('content')
    <h1>Doe para uma ONG</h1>
    <p class="muted">Escolha uma organização e faça sua doação via Pix.</p>

    @if ($ongs->isEmpty())
        <p class="empty">Nenhuma ONG disponível no momento.</p>
    @else
        <ul class="ong-list">
            @foreach ($ongs as $ong)
                <li class="ong-item">
                    <div class="ong-info">
                        <h2>{{ $ong->name }}</h2>
                        @if ($ong->city)
                            <p class="muted">{{ $ong->city }}</p>
                        @endif
                        @if ($ong->description)
                            <p class="ong-desc">{{ \Illuminate\Support\Str::limit($ong->description, 120) }}</p>
                        @endif
                    </div>

                    <form class="ong-donate" method="POST" action="{{ route('checkout.store', $ong->id) }}">
                        @csrf
                        <input type="number" step="0.01" min="1" name="amount" placeholder="Valor (R$)" required>
                        <button type="submit">Doar</button>
                    </form>
                </li>
            @endforeach
        </ul>

        <div class="pagination">
            @if ($ongs->onFirstPage())
                <span class="disabled">Anterior</span>
            @else
                <a href="{{ $ongs->previousPageUrl() }}">Anterior</a>
            @endif

            <span class="current">Página {{ $ongs->currentPage() }} de {{ $ongs->lastPage() }}</span>

            @if ($ongs->hasMorePages())
                <a href="{{ $ongs->nextPageUrl() }}">Próxima</a>
            @else
                <span class="disabled">Próxima</span>
            @endif
        </div>
    @endif
@endsection
