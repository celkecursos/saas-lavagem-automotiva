<?php

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;

// Ver task-11, seção 4, e task-13, seção 2.7.

function planFeatureAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

test('admin cria vantagem e ela aparece na vitrine na proxima requisicao', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = planFeatureAdmin();
    $plan = Plan::factory()->create(['active' => true]);

    $this->actingAs($admin)->post(route('payment-plans.features.store', $plan), [
        'label' => 'Suporte prioritário',
    ])->assertRedirect(route('payment-plans.edit', $plan));

    $feature = PlanFeature::sole();
    expect($feature->label)->toBe('Suporte prioritário')
        ->and($feature->active)->toBeTrue();

    $this->get('/planos')->assertSee('Suporte prioritário');
});

test('desativar vantagem some da vitrine mas continua editavel no admin', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = planFeatureAdmin();
    $plan = Plan::factory()->create(['active' => true]);
    $feature = PlanFeature::factory()->create(['plan_id' => $plan->id, 'label' => 'Desconto parceiro', 'active' => true]);

    $this->actingAs($admin)->put(route('payment-plans.features.update', [$plan, $feature]), [
        'label' => 'Desconto parceiro',
        'active' => '0',
    ]);

    expect($feature->fresh()->active)->toBeFalse();
    $this->get('/planos')->assertDontSee('Desconto parceiro');
    $this->actingAs($admin)->get(route('payment-plans.edit', $plan))->assertSee('Desconto parceiro');
});

test('reordenar troca o sort_order com o vizinho', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = planFeatureAdmin();
    $plan = Plan::factory()->create();
    $first = PlanFeature::factory()->create(['plan_id' => $plan->id, 'sort_order' => 1]);
    $second = PlanFeature::factory()->create(['plan_id' => $plan->id, 'sort_order' => 2]);

    $this->actingAs($admin)->post(route('payment-plans.features.move', [$plan, $second]), [
        'direction' => 'up',
    ]);

    expect($first->fresh()->sort_order)->toBe(2)
        ->and($second->fresh()->sort_order)->toBe(1);
});

test('editar plan_features NAO gera registro em audits', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = planFeatureAdmin();
    $plan = Plan::factory()->create();
    $feature = PlanFeature::factory()->create(['plan_id' => $plan->id]);

    $this->actingAs($admin)->put(route('payment-plans.features.update', [$plan, $feature]), [
        'label' => 'Novo texto',
        'active' => '1',
    ]);

    expect(DB::table('audits')->where('auditable_type', PlanFeature::class)->count())->toBe(0);
});

test('remover vantagem apaga o registro', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = planFeatureAdmin();
    $plan = Plan::factory()->create();
    $feature = PlanFeature::factory()->create(['plan_id' => $plan->id]);

    $this->actingAs($admin)->delete(route('payment-plans.features.destroy', [$plan, $feature]));

    expect(PlanFeature::count())->toBe(0);
});
