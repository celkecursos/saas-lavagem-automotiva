<?php

use App\Models\PayoutPlan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;

// Ver task-9, seção 1, e task-11, seção 4.

function payoutPlanAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

test('admin cria plano de repasse do catalogo', function () {
    // O PayoutPlanSeeder (task-5) já semeia 4 planos padrão — usa
    // categoria/nível inéditos pra não colidir.
    $this->seed(DatabaseSeeder::class);
    $admin = payoutPlanAdmin();

    $this->actingAs($admin)->post(route('payout-plans.store'), [
        'category' => 'Master',
        'level' => 3,
        'label' => 'Master Nível 3',
        'base_price_cents' => 6000,
        'active' => '1',
    ])->assertRedirect(route('payout-plans.index'));

    $plan = PayoutPlan::where('label', 'Master Nível 3')->sole();
    expect($plan->base_price_cents)->toBe(6000);
});

test('editar plano de repasse gera auditoria (afeta calculo de todos os lava-rapidos)', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = payoutPlanAdmin();
    $payoutPlan = PayoutPlan::factory()->create(['base_price_cents' => 2000]);

    $this->actingAs($admin)->put(route('payout-plans.update', $payoutPlan), [
        'category' => $payoutPlan->category,
        'level' => $payoutPlan->level,
        'label' => $payoutPlan->label,
        'base_price_cents' => 3000,
        'active' => '1',
    ]);

    expect($payoutPlan->fresh()->base_price_cents)->toBe(3000);

    expect(DB::table('audits')
        ->where('auditable_type', PayoutPlan::class)
        ->where('auditable_id', $payoutPlan->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('admin sem a permission toma 403', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();

    $this->actingAs($user)->get(route('payout-plans.index'))->assertForbidden();
});
