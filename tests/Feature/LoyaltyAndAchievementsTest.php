<?php

use App\Models\Achievement;
use App\Models\CarWash;
use App\Models\LoyaltyPointsLedgerEntry;
use App\Models\LoyaltyRedemption;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use App\Models\PaymentMethodToken;
use App\Models\Plan;
use App\Models\ReferralReward;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Models\WashRedemption;
use App\Notifications\AchievementUnlocked;
use App\Services\Payment\PagSeguroGateway;
use App\Services\Subscription\SubscriptionActivator;
use Database\Seeders\AchievementSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

// Ver task-20, e task-13.

function loyaltyAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

function activeSubscriptionFor(User $user, array $planOverrides = []): Subscription
{
    $plan = Plan::factory()->create($planOverrides);

    return Subscription::factory()->for($user)->for($plan, 'plan')->active()->create();
}

function employeeOfCarWash(CarWash $carWash): User
{
    $employee = User::factory()->create();
    $carWash->users()->attach($employee->id, ['role' => 'employee']);

    return $employee;
}

test('confirmar a 1a lavagem desbloqueia first_wash e lanca pontos no ledger', function () {
    $this->seed(AchievementSeeder::class);
    Notification::fake();

    $user = User::factory()->create();
    $carWash = CarWash::factory()->approved()->create();
    $employee = employeeOfCarWash($carWash);
    $cycle = SubscriptionCycle::factory()->create(['quota_total' => 10, 'quota_used' => 0]);
    $cycle->subscription->update(['user_id' => $user->id]);
    $redemption = WashRedemption::factory()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
        'confirmation_code' => '111111',
    ]);

    $this->actingAs($employee)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.washes.confirm.store'), ['confirmation_code' => '111111']);

    $achievement = Achievement::where('code', 'first_wash')->sole();

    expect($user->userAchievements()->where('achievement_id', $achievement->id)->exists())->toBeTrue()
        ->and(LoyaltyPointsLedgerEntry::balanceFor($user->id))->toBe($achievement->points_reward);

    Notification::assertSentTo($user, AchievementUnlocked::class);
});

test('confirmar a mesma pessoa 10 lavagens desbloqueia first_wash e 10_washes, sem duplicar first_wash', function () {
    $this->seed(AchievementSeeder::class);
    $user = User::factory()->create();
    $carWash = CarWash::factory()->approved()->create();
    $employee = employeeOfCarWash($carWash);
    $cycle = SubscriptionCycle::factory()->create(['quota_total' => 20, 'quota_used' => 0]);
    $cycle->subscription->update(['user_id' => $user->id]);

    for ($i = 0; $i < 10; $i++) {
        $code = (string) (200000 + $i);
        WashRedemption::factory()->create([
            'subscription_cycle_id' => $cycle->id,
            'car_wash_id' => $carWash->id,
            'confirmation_code' => $code,
        ]);

        $this->actingAs($employee)
            ->withSession(['current_car_wash_id' => $carWash->id])
            ->post(route('panel.washes.confirm.store'), ['confirmation_code' => $code]);
    }

    expect($user->userAchievements()->count())->toBe(2); // first_wash + 10_washes
    expect(\App\Models\UserAchievement::where('user_id', $user->id)
        ->whereHas('achievement', fn ($q) => $q->where('code', 'first_wash'))
        ->count())->toBe(1);
});

test('5a avaliacao desbloqueia 5_ratings', function () {
    $this->seed(AchievementSeeder::class);
    $user = User::factory()->create();
    $carWash = CarWash::factory()->approved()->create();
    $subscription = activeSubscriptionFor($user);
    $cycle = SubscriptionCycle::factory()->create(['subscription_id' => $subscription->id]);

    for ($i = 0; $i < 5; $i++) {
        $redemption = WashRedemption::factory()->completed()->create([
            'subscription_cycle_id' => $cycle->id,
            'car_wash_id' => $carWash->id,
        ]);

        $this->actingAs($user)->post(route('wash.rate', $redemption), ['score' => 90]);
    }

    $achievement = Achievement::where('code', '5_ratings')->sole();
    expect($user->userAchievements()->where('achievement_id', $achievement->id)->exists())->toBeTrue();
});

test('3a indicacao granted desbloqueia 3_referrals', function () {
    $this->seed(AchievementSeeder::class);
    $referrer = User::factory()->create();
    $plan = Plan::factory()->create(['wash_quota' => 4]);
    $referrerSubscription = Subscription::factory()->for($referrer)->for($plan, 'plan')->create();
    ReferralReward::factory()->qualified()->count(3)->create(['referrer_user_id' => $referrer->id]);

    $order = Order::factory()->initial()->for($referrer)->create([
        'payable_type' => Subscription::class,
        'payable_id' => $referrerSubscription->id,
    ]);

    SubscriptionActivator::activateFromInitialOrder($order);

    $achievement = Achievement::where('code', '3_referrals')->sole();
    expect($referrer->userAchievements()->where('achievement_id', $achievement->id)->exists())->toBeTrue();
});

test('assinatura mais antiga com 1 ano completo desbloqueia 1_year_member na ativacao', function () {
    $this->seed(AchievementSeeder::class);
    $this->travelTo(now()->subYear()->subDay());
    $user = User::factory()->create();
    $oldPlan = Plan::factory()->create();
    // Assinatura antiga já existente (ex: cancelada há muito tempo), sem
    // ser a que está ativando agora — é ELA que conta pro aniversário.
    Subscription::factory()->for($user)->for($oldPlan, 'plan')->create(['status' => 'canceled']);

    $this->travelBack();

    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->create();
    $order = Order::factory()->initial()->for($user)->create([
        'payable_type' => Subscription::class,
        'payable_id' => $subscription->id,
    ]);

    SubscriptionActivator::activateFromInitialOrder($order);

    $achievement = Achievement::where('code', '1_year_member')->sole();
    expect($user->userAchievements()->where('achievement_id', $achievement->id)->exists())->toBeTrue();
});

test('assinatura com menos de 1 ano nao desbloqueia 1_year_member', function () {
    $this->seed(AchievementSeeder::class);
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->create();
    $order = Order::factory()->initial()->for($user)->create([
        'payable_type' => Subscription::class,
        'payable_id' => $subscription->id,
    ]);

    SubscriptionActivator::activateFromInitialOrder($order);

    expect($user->userAchievements()->count())->toBe(0);
});

test('resgatar free_wash gasta pontos e soma +1 na cota do ciclo atual', function () {
    $user = User::factory()->create();
    $subscription = activeSubscriptionFor($user, ['wash_quota' => 4]);
    $cycle = SubscriptionCycle::factory()->create(['subscription_id' => $subscription->id, 'quota_total' => 4, 'quota_used' => 0]);
    LoyaltyPointsLedgerEntry::create(['user_id' => $user->id, 'points' => 100, 'reason' => 'achievement', 'created_at' => now()]);
    $reward = LoyaltyRedemption::create(['name' => '1 lavagem grátis', 'points_cost' => 50, 'reward_type' => 'free_wash', 'active' => true]);

    $this->actingAs($user)
        ->post(route('loyalty.shop.redeem', $reward))
        ->assertRedirect(route('loyalty.shop'));

    expect(LoyaltyPointsLedgerEntry::balanceFor($user->id))->toBe(50)
        ->and($cycle->fresh()->quota_total)->toBe(5);

    $claim = \App\Models\LoyaltyRedemptionClaim::sole();
    expect($claim->points_spent)->toBe(50)
        ->and($claim->applied_at)->not->toBeNull();
});

test('resgatar discount_next_renewal grava o desconto pendente na assinatura', function () {
    $user = User::factory()->create();
    $subscription = activeSubscriptionFor($user);
    LoyaltyPointsLedgerEntry::create(['user_id' => $user->id, 'points' => 100, 'reason' => 'achievement', 'created_at' => now()]);
    $reward = LoyaltyRedemption::create([
        'name' => '10% de desconto',
        'points_cost' => 80,
        'reward_type' => 'discount_next_renewal',
        'discount_percent' => 10,
        'active' => true,
    ]);

    $this->actingAs($user)->post(route('loyalty.shop.redeem', $reward));

    expect((float) $subscription->fresh()->pending_renewal_discount_percent)->toBe(10.0);
});

test('nao pode resgatar sem saldo suficiente', function () {
    $user = User::factory()->create();
    activeSubscriptionFor($user);
    $reward = LoyaltyRedemption::create(['name' => 'x', 'points_cost' => 100, 'reward_type' => 'free_wash', 'active' => true]);

    $this->actingAs($user)
        ->post(route('loyalty.shop.redeem', $reward))
        ->assertSessionHas('error');

    expect(\App\Models\LoyaltyRedemptionClaim::count())->toBe(0);
});

test('desconto pendente e aplicado e zerado na proxima renovacao', function () {
    Http::fake(['*' => Http::response(['id' => 'O', 'charges' => [['id' => 'C', 'status' => 'PAID']]], 201)]);
    $type = PaymentGatewayType::factory()->create(['slug' => 'pagseguro', 'service_class' => PagSeguroGateway::class]);
    $gateway = PaymentGateway::factory()->for($type, 'type')->active()->create(['credentials' => ['token' => 'x']]);

    $user = User::factory()->create();
    $plan = Plan::factory()->create(['price_cents' => 10000]);
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->active()->create([
        'current_period_end' => now()->subDay(),
        'pending_renewal_discount_percent' => 10,
    ]);
    PaymentMethodToken::create(['user_id' => $user->id, 'payment_gateway_id' => $gateway->id, 'token' => 'CARD_X']);

    $this->artisan('subscriptions:renew')->assertSuccessful();

    $order = Order::where('payable_id', $subscription->id)->where('payable_type', Subscription::class)->sole();
    expect($order->amount_cents)->toBe(9000)
        ->and($subscription->fresh()->pending_renewal_discount_percent)->toBeNull();
});

test('admin faz CRUD de achievements', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = loyaltyAdmin();

    $this->actingAs($admin)
        ->post(route('achievements.store'), [
            'code' => 'custom_code',
            'name' => 'Conquista Custom',
            'description' => 'Desc',
            'icon' => '🎯',
            'points_reward' => 5,
            'active' => '1',
        ])
        ->assertRedirect(route('achievements.index'));

    $achievement = Achievement::where('code', 'custom_code')->sole();

    $this->actingAs($admin)
        ->put(route('achievements.update', $achievement), [
            'code' => 'custom_code',
            'name' => 'Conquista Editada',
            'description' => 'Desc',
            'icon' => '🎯',
            'points_reward' => 8,
            'active' => '1',
        ])
        ->assertRedirect(route('achievements.index'));

    expect($achievement->fresh()->name)->toBe('Conquista Editada')
        ->and($achievement->fresh()->points_reward)->toBe(8);
});

test('admin faz CRUD de loyalty-redemptions e mudanca de points_cost fica em audits', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = loyaltyAdmin();

    $this->actingAs($admin)
        ->post(route('loyalty-redemptions.store'), [
            'name' => '1 lavagem grátis',
            'points_cost' => 50,
            'reward_type' => 'free_wash',
            'active' => '1',
        ])
        ->assertRedirect(route('loyalty-redemptions.index'));

    $redemption = LoyaltyRedemption::sole();

    $this->actingAs($admin)
        ->put(route('loyalty-redemptions.update', $redemption), [
            'name' => '1 lavagem grátis',
            'points_cost' => 70,
            'reward_type' => 'free_wash',
            'active' => '1',
        ])
        ->assertRedirect(route('loyalty-redemptions.index'));

    expect($redemption->fresh()->points_cost)->toBe(70);
    expect(\Illuminate\Support\Facades\DB::table('audits')
        ->where('auditable_type', LoyaltyRedemption::class)
        ->where('auditable_id', $redemption->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('discount_percent e obrigatorio quando reward_type e discount_next_renewal', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = loyaltyAdmin();

    $this->actingAs($admin)
        ->post(route('loyalty-redemptions.store'), [
            'name' => 'Desconto',
            'points_cost' => 50,
            'reward_type' => 'discount_next_renewal',
        ])
        ->assertSessionHasErrors('discount_percent');
});

test('tela minha fidelidade mostra saldo, conquistas desbloqueadas/bloqueadas e extrato', function () {
    $this->seed(AchievementSeeder::class);
    $user = User::factory()->create();
    $achievement = Achievement::where('code', 'first_wash')->sole();
    \App\Models\UserAchievement::create(['user_id' => $user->id, 'achievement_id' => $achievement->id, 'unlocked_at' => now()]);
    LoyaltyPointsLedgerEntry::create(['user_id' => $user->id, 'points' => 10, 'reason' => 'achievement', 'created_at' => now()]);

    $response = $this->actingAs($user)->get(route('loyalty.index'));

    $response->assertOk()
        ->assertSee('10')
        ->assertSee('Primeira Lavagem')
        ->assertSee('Cliente Fiel'); // 10_washes ainda bloqueada
});
