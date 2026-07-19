@extends('layouts.car-wash-panel')

@section('title', 'Pagar cobrança — Painel')

@section('content')
    <div class="max-w-lg">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Pagar cobrança do estacionamento</h1>

        <x-card title="Resumo" class="mb-4">
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                R$ {{ number_format($charge->fee_amount_cents / 100, 2, ',', '.') }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Período: {{ $charge->period_start->format('d/m/Y') }} – {{ $charge->period_end->format('d/m/Y') }}
            </p>
        </x-card>

        {{-- Mesmo fluxo de checkout embedded da task-4 (seção 5.3) — os
             dados do cartão nunca chegam ao nosso backend em texto puro. --}}
        <x-card title="Pagamento">
            <form id="charge-checkout-form">
                <x-form-field label="Nome no cartão" name="holder" autocomplete="cc-name" />
                <x-form-field label="Número do cartão" name="number" autocomplete="cc-number" inputmode="numeric" />
                <div class="grid grid-cols-3 gap-3">
                    <x-form-field label="Mês" name="exp_month" placeholder="MM" inputmode="numeric" />
                    <x-form-field label="Ano" name="exp_year" placeholder="AAAA" inputmode="numeric" />
                    <x-form-field label="CVV" name="security_code" inputmode="numeric" autocomplete="cc-csc" />
                </div>

                <div id="charge-checkout-error" class="alert-danger hidden"></div>

                <button type="submit" class="btn-primary w-full mt-2">Pagar</button>
            </form>
        </x-card>
    </div>

    <script src="https://assets.pagseguro.com.br/checkout-sdk-js/rc/dist/browser/pagseguro.min.js"></script>
    <script>
        document.getElementById('charge-checkout-form').addEventListener('submit', async function (event) {
            event.preventDefault();

            const errorBox = document.getElementById('charge-checkout-error');
            errorBox.classList.add('hidden');

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

            const response = await fetch(@json(route('panel.parking.charges.pay', $charge)), {
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
