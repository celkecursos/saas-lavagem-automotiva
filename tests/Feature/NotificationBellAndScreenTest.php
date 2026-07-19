<?php

use App\Models\CarWash;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

// Ver task-19, seções 3 e 4, e task-13.

function makeNotificationFor(User $user, array $data, bool $read = false, ?\Illuminate\Support\Carbon $createdAt = null): DatabaseNotification
{
    return DatabaseNotification::create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\PayoutPaid',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => $data,
        'read_at' => $read ? now() : null,
        'created_at' => $createdAt ?? now(),
    ]);
}

test('sino nao renderiza nada pra visitante deslogado', function () {
    $html = Blade::render('<x-notification-bell />');

    expect(trim($html))->toBe('');
});

test('sino renderiza pra usuario autenticado com link pra ver todas', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $html = Blade::render('<x-notification-bell />');

    expect($html)->toContain('Notificações')
        ->and($html)->toContain('Ver todas')
        ->and($html)->toContain(route('notifications.index'));
});

test('sino presente nos 3 layouts pra usuario autenticado', function (string $layout) {
    $user = User::factory()->create();
    $this->actingAs($user);

    expect(view('layouts.'.$layout)->render())->toContain('notificationBell()');
})->with(['admin', 'car-wash-panel', 'public']);

test('GET /notificacoes em json devolve contador e as ultimas 5, mais recente primeiro', function () {
    $user = User::factory()->create();
    makeNotificationFor($user, ['title' => 'Antiga', 'body' => '', 'url' => '/x'], createdAt: now()->subMinute());
    $recent = makeNotificationFor($user, ['title' => 'Recente', 'body' => '', 'url' => '/y'], createdAt: now());

    $response = $this->actingAs($user)
        ->getJson(route('notifications.index'));

    $response->assertOk()
        ->assertJsonPath('unread_count', 2)
        ->assertJsonPath('items.0.id', $recent->id)
        ->assertJsonPath('items.0.title', 'Recente');
});

test('GET /notificacoes em html lista so as notificacoes do proprio usuario', function () {
    $user = User::factory()->create();
    $outro = User::factory()->create();
    makeNotificationFor($user, ['title' => 'Minha notificacao', 'body' => '', 'url' => '/x']);
    makeNotificationFor($outro, ['title' => 'Notificacao de outro usuario', 'body' => '', 'url' => '/y']);

    $response = $this->actingAs($user)->get(route('notifications.index'));

    $response->assertOk()
        ->assertSee('Minha notificacao')
        ->assertDontSee('Notificacao de outro usuario');
});

test('marcar uma notificacao como lida so funciona pra dona dela', function () {
    $user = User::factory()->create();
    $outro = User::factory()->create();
    $notification = makeNotificationFor($user, ['title' => 'X', 'body' => '', 'url' => '/x']);

    $this->actingAs($outro)
        ->postJson(route('notifications.mark-read', $notification->id))
        ->assertNotFound();

    expect($notification->fresh()->read_at)->toBeNull();

    $this->actingAs($user)
        ->postJson(route('notifications.mark-read', $notification->id))
        ->assertOk();

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('marcar todas como lidas so afeta as do usuario logado', function () {
    $user = User::factory()->create();
    $outro = User::factory()->create();
    $minha = makeNotificationFor($user, ['title' => 'X', 'body' => '', 'url' => '/x']);
    $deOutro = makeNotificationFor($outro, ['title' => 'Y', 'body' => '', 'url' => '/y']);

    $this->actingAs($user)
        ->post(route('notifications.mark-all-read'))
        ->assertRedirect(route('notifications.index'));

    expect($minha->fresh()->read_at)->not->toBeNull()
        ->and($deOutro->fresh()->read_at)->toBeNull();
});

test('tela de notificacoes usa o layout admin pra staff, painel pra lava-rapido e publico pra assinante', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();

    $carWashOwner = User::factory()->create();
    $carWash = CarWash::factory()->approved()->create();
    $carWash->users()->attach($carWashOwner->id, ['role' => 'owner']);

    $subscriber = User::factory()->create();

    $this->actingAs($admin)->get(route('notifications.index'))
        ->assertOk()->assertSee('Admin', false);

    $this->actingAs($carWashOwner)->get(route('notifications.index'))
        ->assertOk()->assertSee('Painel', false);

    $this->actingAs($subscriber)->get(route('notifications.index'))
        ->assertOk()->assertSee('Celke Wash Club');
});
