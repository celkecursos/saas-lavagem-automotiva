<?php

use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\PayoutPlan;
use App\Models\User;
use App\Notifications\ClubActivationApproved;
use App\Notifications\ClubActivationRejected;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Notification;

// Ver task-13, seções 2.2 e 2.5 — clube de lavagem: escolher payout_plan
// é self-service, ATIVAR exige aprovação do admin (task-5, seção 5).

function clubOwnerOf(CarWash $carWash): User
{
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);

    return $owner;
}

function clubAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

test('solicitar clube sem escolher payout_plan e bloqueado', function () {
    $carWash = CarWash::factory()->approved()->create();
    $owner = clubOwnerOf($carWash);

    $this->actingAs($owner)
        ->post(route('panel.products.club.request'))
        ->assertSessionHasErrors('payout_plan_id');

    expect($carWash->productSubscriptions()->count())->toBe(0);
});

test('solicitacao com payout_plan entra como pending, nunca active direto', function () {
    $carWash = CarWash::factory()->approved()->create();
    $owner = clubOwnerOf($carWash);
    $payoutPlan = PayoutPlan::factory()->create();

    $this->actingAs($owner)->post(route('panel.products.club.request'), [
        'payout_plan_id' => $payoutPlan->id,
    ])->assertRedirect(route('panel.products.index'));

    $subscription = $carWash->productSubscriptions()->sole();

    expect($subscription->product)->toBe('clube_lavagem')
        ->and($subscription->status)->toBe('pending')
        ->and($subscription->payout_plan_id)->toBe($payoutPlan->id)
        ->and($subscription->activated_at)->toBeNull();
});

test('admin aprova a solicitacao: active, approved_by gravado, dono notificado', function () {
    Notification::fake();
    $this->seed(DatabaseSeeder::class);
    $admin = clubAdmin();
    $carWash = CarWash::factory()->approved()->create();
    $owner = clubOwnerOf($carWash);
    $subscription = CarWashProductSubscription::factory()->create([
        'car_wash_id' => $carWash->id,
        'product' => 'clube_lavagem',
        'status' => 'pending',
        'payout_plan_id' => PayoutPlan::factory()->create()->id,
    ]);

    $this->actingAs($admin)
        ->post(route('car-wash-product-subscriptions.approve', $subscription))
        ->assertRedirect(route('car-wash-product-subscriptions.index'));

    $subscription->refresh();

    expect($subscription->status)->toBe('active')
        ->and($subscription->activated_at)->not->toBeNull()
        ->and($subscription->approved_by)->toBe($admin->id);

    Notification::assertSentTo($owner, ClubActivationApproved::class);
});

test('admin rejeita a solicitacao: canceled e dono notificado', function () {
    Notification::fake();
    $this->seed(DatabaseSeeder::class);
    $admin = clubAdmin();
    $carWash = CarWash::factory()->approved()->create();
    $owner = clubOwnerOf($carWash);
    $subscription = CarWashProductSubscription::factory()->create([
        'car_wash_id' => $carWash->id,
        'product' => 'clube_lavagem',
        'status' => 'pending',
        'payout_plan_id' => PayoutPlan::factory()->create()->id,
    ]);

    $this->actingAs($admin)->post(route('car-wash-product-subscriptions.reject', $subscription));

    expect($subscription->fresh()->status)->toBe('canceled');
    Notification::assertSentTo($owner, ClubActivationRejected::class);
});

test('trocar de payout_plan volta pra pending ate nova aprovacao', function () {
    $carWash = CarWash::factory()->approved()->create();
    $owner = clubOwnerOf($carWash);
    $original = PayoutPlan::factory()->create();
    $novo = PayoutPlan::factory()->create();
    CarWashProductSubscription::factory()->create([
        'car_wash_id' => $carWash->id,
        'product' => 'clube_lavagem',
        'status' => 'active',
        'activated_at' => now(),
        'payout_plan_id' => $original->id,
    ]);

    $this->actingAs($owner)->post(route('panel.products.club.request'), [
        'payout_plan_id' => $novo->id,
    ]);

    $subscription = $carWash->productSubscriptions()->sole();

    expect($subscription->status)->toBe('pending')
        ->and($subscription->payout_plan_id)->toBe($novo->id)
        ->and($subscription->approved_by)->toBeNull();
});

test('payout_plan inativo do catalogo nao pode ser escolhido', function () {
    $carWash = CarWash::factory()->approved()->create();
    $owner = clubOwnerOf($carWash);
    $inactive = PayoutPlan::factory()->create(['active' => false]);

    $this->actingAs($owner)->post(route('panel.products.club.request'), [
        'payout_plan_id' => $inactive->id,
    ])->assertStatus(422);
});
