<?php

use App\Models\Plan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

// Ver task-11, seção 4.

function planAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

test('admin cria plano', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = planAdmin();

    $this->actingAs($admin)->post(route('payment-plans.store'), [
        'name' => 'Plano Turbo',
        'slug' => 'plano-turbo',
        'price_cents' => 5990,
        'wash_quota' => 6,
        'quota_period' => 'monthly',
    ])->assertRedirect(route('payment-plans.index'));

    $plan = Plan::sole();
    // Checkbox não enviado no teste -> fica de fora do validated() ->
    // usa o default da coluna (true).
    expect($plan->name)->toBe('Plano Turbo')
        ->and($plan->active)->toBeTrue();
});

test('admin edita plano e a mudanca gera auditoria', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = planAdmin();
    $plan = Plan::factory()->create(['price_cents' => 4990]);

    $this->actingAs($admin)->put(route('payment-plans.update', $plan), [
        'name' => $plan->name,
        'price_cents' => 6990,
        'wash_quota' => $plan->wash_quota,
        'quota_period' => $plan->quota_period,
    ])->assertRedirect(route('payment-plans.edit', $plan->fresh()));

    expect($plan->fresh()->price_cents)->toBe(6990);

    expect(\Illuminate\Support\Facades\DB::table('audits')
        ->where('auditable_type', Plan::class)
        ->where('auditable_id', $plan->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('editar plano ja em uso por assinante ativo so afeta o proximo ciclo (subscription_cycles congela)', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = planAdmin();
    $plan = Plan::factory()->create(['wash_quota' => 4]);
    $subscription = \App\Models\Subscription::factory()->for($plan, 'plan')->active()->create();
    $cycle = \App\Models\SubscriptionCycle::factory()->create([
        'subscription_id' => $subscription->id,
        'quota_total' => 4,
    ]);

    $this->actingAs($admin)->put(route('payment-plans.update', $plan), [
        'name' => $plan->name,
        'price_cents' => $plan->price_cents,
        'wash_quota' => 10,
        'quota_period' => $plan->quota_period,
    ]);

    // Ciclo já criado continua com a cota congelada.
    expect($cycle->fresh()->quota_total)->toBe(4)
        ->and($plan->fresh()->wash_quota)->toBe(10);
});

test('admin sem a permission toma 403', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();

    $this->actingAs($user)->get(route('payment-plans.index'))->assertForbidden();
});
