<?php

use App\Models\CarWash;
use App\Models\Payout;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;

// Ver task-9, seção 3, e task-13, seção 2.5.

function payoutAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

test('marcar como pago exige referencia e fica registrado em audits', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = payoutAdmin();
    $payout = Payout::factory()->create(['status' => 'pending']);

    // Sem referência: falha.
    $this->actingAs($admin)
        ->post(route('payouts.mark-paid', $payout), [])
        ->assertSessionHasErrors('payment_reference');
    expect($payout->fresh()->status)->toBe('pending');

    $this->actingAs($admin)
        ->post(route('payouts.mark-paid', $payout), ['payment_reference' => 'TED-123456'])
        ->assertRedirect(route('payouts.show', $payout));

    $payout->refresh();

    expect($payout->status)->toBe('paid')
        ->and($payout->payment_reference)->toBe('TED-123456')
        ->and($payout->paid_at)->not->toBeNull();

    expect(DB::table('audits')
        ->where('auditable_type', Payout::class)
        ->where('auditable_id', $payout->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('marcar como falhou muda o status sem exigir nada mais', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = payoutAdmin();
    $payout = Payout::factory()->create(['status' => 'pending']);

    $this->actingAs($admin)
        ->post(route('payouts.mark-failed', $payout))
        ->assertRedirect(route('payouts.show', $payout));

    expect($payout->fresh()->status)->toBe('failed');
});

test('admin sem a permission toma 403', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $payout = Payout::factory()->create();

    $this->actingAs($user)->get(route('payouts.index'))->assertForbidden();
});
