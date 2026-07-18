<?php

use App\Models\CarWash;
use App\Models\User;
use App\Notifications\CarWashRegistrationReceived;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

// Ver task-13, seção 2.2 — onboarding self-service do lava-rápido.

function validPartnerPayload(array $overrides = []): array
{
    return array_merge([
        'owner_name' => 'Jessica Silva',
        'owner_email' => 'jessica@exemplo.com',
        'owner_phone' => '(41) 99999-0000',
        'password' => 'Senha#Forte1',
        'password_confirmation' => 'Senha#Forte1',
        'car_wash_name' => 'Lava Jato da Jessica',
        'document' => '12345678000199',
        'car_wash_phone' => '(41) 3333-0000',
        'car_wash_email' => 'contato@lavajato.com',
        'address_line' => 'Rua das Flores, 100',
        'city' => 'Curitiba',
        'state' => 'PR',
        'zip_code' => '80000000',
    ], $overrides);
}

test('cadastro cria user, car_wash pending e vinculo owner numa transacao', function () {
    Notification::fake();

    $response = $this->post('/parceiros/cadastro', validPartnerPayload());

    $response->assertRedirect(route('panel.dashboard'));
    $this->assertAuthenticated();

    $user = User::where('email', 'jessica@exemplo.com')->sole();
    $carWash = CarWash::where('document', '12345678000199')->sole();

    expect($user->role)->toBe('user')
        ->and($carWash->status)->toBe('pending')
        ->and($carWash->slug)->toBe('lava-jato-da-jessica')
        ->and($carWash->users()->wherePivot('role', 'owner')->pluck('users.id')->all())
            ->toBe([$user->id]);

    // Verificação de e-mail + confirmação de cadastro recebido (seção 8).
    Notification::assertSentTo($user, VerifyEmail::class);
    Notification::assertSentTo($user, CarWashRegistrationReceived::class);
});

test('CNPJ duplicado e rejeitado com mensagem clara', function () {
    CarWash::factory()->create(['document' => '12345678000199']);

    $this->post('/parceiros/cadastro', validPartnerPayload())
        ->assertSessionHasErrors('document');

    expect(User::where('email', 'jessica@exemplo.com')->exists())->toBeFalse();
});

test('e-mail de responsavel ja usado e rejeitado', function () {
    User::factory()->create(['email' => 'jessica@exemplo.com']);

    $this->post('/parceiros/cadastro', validPartnerPayload())
        ->assertSessionHasErrors('owner_email');
});

test('senha fraca e rejeitada no cadastro de parceiro', function () {
    $this->post('/parceiros/cadastro', validPartnerPayload([
        'password' => 'fraca123',
        'password_confirmation' => 'fraca123',
    ]))->assertSessionHasErrors('password');
});

test('logado, o dono ve o painel em modo aguardando aprovacao', function () {
    Notification::fake();
    $this->post('/parceiros/cadastro', validPartnerPayload());

    $this->get('/painel')
        ->assertOk()
        ->assertSee('em análise');
});
