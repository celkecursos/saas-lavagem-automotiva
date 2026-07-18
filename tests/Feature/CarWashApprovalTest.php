<?php

use App\Models\CarWash;
use App\Models\User;
use App\Notifications\CarWashApproved;
use App\Notifications\CarWashRejected;
use App\Notifications\CarWashSuspended;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

// Ver task-13, seção 2.2 — fila de aprovação do admin (task-5, seção 4).

function makeApprovalAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

function makeCarWashWithOwner(array $overrides = []): array
{
    $owner = User::factory()->create();
    $carWash = CarWash::factory()->create($overrides);
    $carWash->users()->attach($owner->id, ['role' => 'owner']);

    return [$carWash, $owner];
}

test('approve grava status, approved_at/by, audita e notifica o dono', function () {
    Notification::fake();
    $this->seed(DatabaseSeeder::class);
    $admin = makeApprovalAdmin();
    [$carWash, $owner] = makeCarWashWithOwner();

    $this->actingAs($admin)
        ->post(route('car-washes.approve', $carWash))
        ->assertRedirect(route('car-washes.show', $carWash));

    $carWash->refresh();

    expect($carWash->status)->toBe('approved')
        ->and($carWash->approved_at)->not->toBeNull()
        ->and($carWash->approved_by)->toBe($admin->id);

    Notification::assertSentTo($owner, CarWashApproved::class);

    expect(DB::table('audits')
        ->where('auditable_type', CarWash::class)
        ->where('auditable_id', $carWash->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('reject exige o motivo e o envia pro dono', function () {
    Notification::fake();
    $this->seed(DatabaseSeeder::class);
    $admin = makeApprovalAdmin();
    [$carWash, $owner] = makeCarWashWithOwner();

    // Sem motivo: falha na validação, nada muda.
    $this->actingAs($admin)
        ->post(route('car-washes.reject', $carWash))
        ->assertSessionHasErrors('rejection_reason');
    expect($carWash->fresh()->status)->toBe('pending');

    $this->actingAs($admin)->post(route('car-washes.reject', $carWash), [
        'rejection_reason' => 'Documento ilegível',
    ]);

    expect($carWash->fresh()->status)->toBe('rejected')
        ->and($carWash->fresh()->rejection_reason)->toBe('Documento ilegível');

    Notification::assertSentTo($owner, CarWashRejected::class);
});

test('suspend tira lava-rapido aprovado do ar e notifica', function () {
    Notification::fake();
    $this->seed(DatabaseSeeder::class);
    $admin = makeApprovalAdmin();
    [$carWash, $owner] = makeCarWashWithOwner(['status' => 'approved']);

    $this->actingAs($admin)->post(route('car-washes.suspend', $carWash));

    expect($carWash->fresh()->status)->toBe('suspended');
    Notification::assertSentTo($owner, CarWashSuspended::class);
});

test('admin sem a permission de aprovar toma 403', function () {
    $this->seed(DatabaseSeeder::class);
    $semPermissao = User::factory()->create();
    $semPermissao->forceFill(['role' => 'admin'])->save();
    [$carWash] = makeCarWashWithOwner();

    $this->actingAs($semPermissao)
        ->post(route('car-washes.approve', $carWash))
        ->assertForbidden();
});

test('fila padrao mostra pendentes com e-mail verificado primeiro', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = makeApprovalAdmin();

    $verifiedOwner = User::factory()->create(['email_verified_at' => now()]);
    $unverifiedOwner = User::factory()->unverified()->create();

    $unverifiedFirst = CarWash::factory()->create(['name' => 'Sem Verificacao', 'created_at' => now()->subDay()]);
    $unverifiedFirst->users()->attach($unverifiedOwner->id, ['role' => 'owner']);

    $verified = CarWash::factory()->create(['name' => 'Com Verificacao']);
    $verified->users()->attach($verifiedOwner->id, ['role' => 'owner']);

    $response = $this->actingAs($admin)->get(route('car-washes.index'));

    $response->assertOk()->assertSeeInOrder(['Com Verificacao', 'Sem Verificacao']);
});

test('dono corrige cadastro rejeitado e reenvia (volta pra pending)', function () {
    [$carWash, $owner] = makeCarWashWithOwner([
        'status' => 'rejected',
        'rejection_reason' => 'Endereço incompleto',
    ]);

    $this->actingAs($owner)->get('/painel')->assertSee('Corrigir e reenviar cadastro');

    $this->actingAs($owner)->put(route('panel.registration.update'), [
        'name' => $carWash->name,
        'document' => $carWash->document,
        'phone' => $carWash->phone,
        'email' => $carWash->email,
        'address_line' => 'Rua Corrigida, 200 — fundos',
        'city' => $carWash->city,
        'state' => $carWash->state,
        'zip_code' => $carWash->zip_code,
    ])->assertRedirect(route('panel.dashboard'));

    $carWash->refresh();

    expect($carWash->status)->toBe('pending')
        ->and($carWash->rejection_reason)->toBeNull()
        ->and($carWash->address_line)->toBe('Rua Corrigida, 200 — fundos');
});

test('funcionario nao corrige cadastro — so o owner', function () {
    [$carWash] = makeCarWashWithOwner(['status' => 'rejected', 'rejection_reason' => 'x']);
    $employee = User::factory()->create();
    $carWash->users()->attach($employee->id, ['role' => 'employee']);

    $this->actingAs($employee)
        ->get(route('panel.registration.edit'))
        ->assertForbidden();
});
