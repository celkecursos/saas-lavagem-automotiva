@extends('layouts.public')

@section('title', 'Pagamento indisponível')

@section('content')
    <div class="max-w-xl mx-auto px-4 py-16 text-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Pagamento temporariamente indisponível</h1>
        <div class="alert-warning text-left">
            Não foi possível iniciar o pagamento do plano
            <strong>{{ $plan->name }}</strong> agora. Nossa equipe já foi
            avisada — tente novamente em alguns minutos.
        </div>
        <a href="{{ url('/') }}" class="btn-secondary mt-4">Voltar</a>
    </div>
@endsection
