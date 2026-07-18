@extends('layouts.public')

@section('title', 'Assinar '.$plan->name)

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Finalizar assinatura</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Resumo do plano escolhido --}}
            <x-card :title="$plan->name">
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    R$ {{ number_format($plan->price_cents / 100, 2, ',', '.') }}<span class="text-sm font-normal text-gray-500">/mês</span>
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    {{ $plan->wash_quota }} lavagens por ciclo em qualquer lava-rápido da rede.
                </p>
            </x-card>

            {{-- Formulário de cartão: os dados NUNCA vão pro nosso backend
                 em texto puro — PagSeguro.encryptCard() roda no browser e
                 só o blob criptografado é enviado (task-4, seção 5.3). --}}
            <x-card title="Pagamento">
                <form id="checkout-form">
                    <x-form-field label="Nome no cartão" name="holder" autocomplete="cc-name" />
                    <x-form-field label="Número do cartão" name="number" autocomplete="cc-number" inputmode="numeric" />
                    <div class="grid grid-cols-3 gap-3">
                        <x-form-field label="Mês" name="exp_month" placeholder="MM" inputmode="numeric" />
                        <x-form-field label="Ano" name="exp_year" placeholder="AAAA" inputmode="numeric" />
                        <x-form-field label="CVV" name="security_code" inputmode="numeric" autocomplete="cc-csc" />
                    </div>

                    <div id="checkout-error" class="alert-danger hidden"></div>

                    <button type="submit" class="btn-primary w-full mt-2">Assinar</button>
                </form>
            </x-card>
        </div>
    </div>

    {{-- SDK oficial do PagBank (task-4, seção 5.3). --}}
    <script src="https://assets.pagseguro.com.br/checkout-sdk-js/rc/dist/browser/pagseguro.min.js"></script>
    <script>
        document.getElementById('checkout-form').addEventListener('submit', async function (event) {
            event.preventDefault();

            const errorBox = document.getElementById('checkout-error');
            errorBox.classList.add('hidden');

            // Criptografa 100% no browser — número/CVV nunca saem daqui
            // em texto puro (nem pro nosso backend, nem pro PagBank).
            const card = PagSeguro.encryptCard({
                publicKey: @json($publicKey),
                holder: document.getElementById('holder').value,
                number: document.getElementById('number').value.replace(/\s/g, ''),
                expMonth: document.getElementById('exp_month').value,
                expYear: document.getElementById('exp_year').value,
                securityCode: document.getElementById('security_code').value,
            });

            if (card.hasErrors) {
                errorBox.textContent = 'Confira os dados do cartão e tente novamente.';
                errorBox.classList.remove('hidden');
                return;
            }

            const response = await fetch(@json(Route::has('plans.subscribe') ? route('plans.subscribe', $plan) : '#'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ encrypted_card: card.encryptedCard }),
            });

            if (response.ok) {
                const data = await response.json();
                window.location.href = data.redirect ?? '/';
            } else {
                errorBox.textContent = 'Não foi possível processar o pagamento. Tente novamente.';
                errorBox.classList.remove('hidden');
            }
        });
    </script>
@endsection
