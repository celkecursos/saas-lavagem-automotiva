@extends('layouts.public')

@section('title', 'Privacidade — Celke Wash Club')
@section('meta_description', 'Política de privacidade do Celke Wash Club — como tratamos seus dados pessoais, conforme a LGPD.')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-16">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Política de privacidade</h1>

        <div class="prose dark:prose-invert text-gray-700 dark:text-gray-300 space-y-4 text-sm">
            <p>
                Coletamos os dados pessoais necessários pra operar sua assinatura: nome,
                e-mail, telefone, CPF (opcional, conforme o cadastro) e dados de pagamento
                processados diretamente pelo gateway parceiro — nunca armazenamos número de
                cartão completo nos nossos servidores.
            </p>
            <p>
                Seus dados são usados para gerenciar sua assinatura, processar pagamentos,
                confirmar lavagens no lava-rápido escolhido e enviar comunicações sobre sua
                conta. Não vendemos seus dados pessoais a terceiros.
            </p>
            <p>
                Em conformidade com a Lei Geral de Proteção de Dados (LGPD), você pode
                solicitar a qualquer momento a exclusão ou correção dos seus dados através
                do nosso <a href="{{ route('contact') }}" class="text-blue-600 dark:text-blue-400 hover:underline">canal de contato</a>.
            </p>
        </div>
    </div>
@endsection
