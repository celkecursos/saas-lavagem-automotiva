<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

// Ver task-7, seção 1 — cadastro self-service do assinante.

test('tela de registro renderiza', function () {
    $this->get('/registro')->assertOk();
});

test('cadastro cria user, dispara verificacao de e-mail e loga direto', function () {
    Notification::fake();

    $response = $this->post('/registro', [
        'name' => 'Gabrielly Souza',
        'email' => 'gabrielly@exemplo.com',
        'phone' => '(41) 98888-0000',
        'password' => 'Senha#Forte1',
        'password_confirmation' => 'Senha#Forte1',
    ]);

    $response->assertRedirect(route('plans.index'));
    $this->assertAuthenticated();

    $user = User::where('email', 'gabrielly@exemplo.com')->sole();

    expect($user->role)->toBe('user')
        ->and($user->phone)->toBe('(41) 98888-0000');

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('cpf e opcional no cadastro do assinante', function () {
    $this->post('/registro', [
        'name' => 'Sem CPF',
        'email' => 'semcpf@exemplo.com',
        'phone' => '(41) 90000-0000',
        'password' => 'Senha#Forte1',
        'password_confirmation' => 'Senha#Forte1',
    ])->assertSessionDoesntHaveErrors('cpf');

    expect(User::where('email', 'semcpf@exemplo.com')->sole()->cpf)->toBeNull();
});

test('senha fraca e rejeitada no cadastro do assinante', function () {
    $this->post('/registro', [
        'name' => 'Fraco',
        'email' => 'fraco@exemplo.com',
        'phone' => '(41) 90000-0000',
        'password' => 'fraca123',
        'password_confirmation' => 'fraca123',
    ])->assertSessionHasErrors('password');
});

test('e-mail duplicado e rejeitado no cadastro do assinante', function () {
    User::factory()->create(['email' => 'gabrielly@exemplo.com']);

    $this->post('/registro', [
        'name' => 'Duplicado',
        'email' => 'gabrielly@exemplo.com',
        'phone' => '(41) 90000-0000',
        'password' => 'Senha#Forte1',
        'password_confirmation' => 'Senha#Forte1',
    ])->assertSessionHasErrors('email');
});
