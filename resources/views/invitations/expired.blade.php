@extends('layouts.public')

@section('title', 'Convite indisponível — Celke Wash Club')

@section('content')
    <div class="max-w-md mx-auto px-4 py-16 text-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Convite indisponível</h1>
        <div class="alert-warning text-left">
            @if ($invitation->accepted_at)
                Este convite já foi aceito.
            @else
                Este convite expirou. Peça ao responsável pelo lava-rápido
                pra enviar um novo.
            @endif
        </div>
        <a href="{{ url('/') }}" class="btn-secondary mt-4">Voltar</a>
    </div>
@endsection
