<?php

use App\Models\CarWash;
use App\Models\CarWashInvitation;
use App\Models\User;
use App\Notifications\TeamInvitation;
use App\Notifications\TeamMemberLinked;
use Illuminate\Support\Facades\Notification;

// Ver task-13, seção 2.2 — convite de equipe (task-5, seção 6).

function teamOwnerOf(CarWash $carWash): User
{
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);

    return $owner;
}

test('convidar e-mail de user existente vincula direto como employee, sem invitation', function () {
    Notification::fake();
    $carWash = CarWash::factory()->approved()->create();
    $owner = teamOwnerOf($carWash);
    $existing = User::factory()->create(['email' => 'ja-existe@exemplo.com']);

    $this->actingAs($owner)->post(route('panel.team.invite'), [
        'email' => 'ja-existe@exemplo.com',
    ])->assertRedirect(route('panel.team.index'));

    expect($carWash->users()->wherePivot('role', 'employee')->pluck('users.id')->all())
        ->toBe([$existing->id])
        ->and(CarWashInvitation::count())->toBe(0);

    Notification::assertSentTo($existing, TeamMemberLinked::class);
});

test('convidar e-mail inexistente cria invitation com token unico e validade', function () {
    Notification::fake();
    $carWash = CarWash::factory()->approved()->create();
    $owner = teamOwnerOf($carWash);

    $this->actingAs($owner)->post(route('panel.team.invite'), [
        'email' => 'novato@exemplo.com',
    ]);

    $invitation = CarWashInvitation::sole();

    expect($invitation->email)->toBe('novato@exemplo.com')
        ->and($invitation->token)->not->toBeEmpty()
        ->and($invitation->expires_at->isFuture())->toBeTrue()
        ->and($invitation->accepted_at)->toBeNull();

    Notification::assertSentOnDemand(TeamInvitation::class);
});

test('aceitar convite cria o user (role user) e o vinculo employee', function () {
    Notification::fake();
    $carWash = CarWash::factory()->approved()->create();
    teamOwnerOf($carWash);
    $invitation = CarWashInvitation::create([
        'car_wash_id' => $carWash->id,
        'email' => 'novato@exemplo.com',
        'token' => str_repeat('a', 48),
        'expires_at' => now()->addDays(7),
    ]);

    $this->get(route('invitations.show', $invitation->token))
        ->assertOk()
        ->assertSee($carWash->name);

    $this->post(route('invitations.accept', $invitation->token), [
        'name' => 'Novato Silva',
        'password' => 'Senha#Forte1',
        'password_confirmation' => 'Senha#Forte1',
    ])->assertRedirect(route('panel.dashboard'));

    $user = User::where('email', 'novato@exemplo.com')->sole();

    expect($user->role)->toBe('user')
        ->and($carWash->users()->wherePivot('role', 'employee')->whereKey($user->id)->exists())->toBeTrue()
        ->and($invitation->fresh()->accepted_at)->not->toBeNull();

    $this->assertAuthenticatedAs($user);
});

test('convite expirado nao pode mais ser aceito', function () {
    $carWash = CarWash::factory()->approved()->create();
    teamOwnerOf($carWash);
    $invitation = CarWashInvitation::create([
        'car_wash_id' => $carWash->id,
        'email' => 'atrasado@exemplo.com',
        'token' => str_repeat('b', 48),
        'expires_at' => now()->subDay(),
    ]);

    $this->get(route('invitations.show', $invitation->token))
        ->assertOk()
        ->assertSee('expirou');

    $this->post(route('invitations.accept', $invitation->token), [
        'name' => 'Atrasado',
        'password' => 'Senha#Forte1',
        'password_confirmation' => 'Senha#Forte1',
    ])->assertForbidden();

    expect(User::where('email', 'atrasado@exemplo.com')->exists())->toBeFalse();
});

test('employee nao acessa a tela de equipe nem convida', function () {
    $carWash = CarWash::factory()->approved()->create();
    teamOwnerOf($carWash);
    $employee = User::factory()->create();
    $carWash->users()->attach($employee->id, ['role' => 'employee']);

    $this->actingAs($employee)->get(route('panel.team.index'))->assertForbidden();
    $this->actingAs($employee)->post(route('panel.team.invite'), [
        'email' => 'x@exemplo.com',
    ])->assertForbidden();
});
