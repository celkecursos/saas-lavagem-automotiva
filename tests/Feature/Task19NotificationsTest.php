<?php

use App\Models\CancellationRequest;
use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\ParkingBillingSetting;
use App\Models\ParkingLot;
use App\Models\ParkingRate;
use App\Models\ParkingSession;
use App\Models\Payout;
use App\Models\ReferralReward;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Models\WashRedemption;
use App\Notifications\CancellationRequestDecided;
use App\Notifications\NewCancellationRequest;
use App\Notifications\NewCarWashPendingApproval;
use App\Notifications\NewClubActivationRequest;
use App\Notifications\ParkingBillingChargeFlagged;
use App\Notifications\PayoutPaid;
use App\Notifications\ReferralRewardGranted;
use App\Notifications\WashRedemptionConfirmed;
use App\Services\CancellationRequestResolver;
use App\Services\Referral\ReferralRewardGranter;
use App\Support\AdminRecipients;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

// Ver task-19, seção 2, e task-13.

function task19Admin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

function task19SuperAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Super Admin');

    return $user;
}

test('AdminRecipients inclui Super Admin e quem tem a permission, sem duplicar, sem lancar excecao quando nao ha nada semeado', function () {
    // Sem seed nenhum: nem o role Super Admin existe ainda.
    expect(AdminRecipients::withPermission('car-washes.approve'))->toHaveCount(0);

    $this->seed(DatabaseSeeder::class);
    $superAdmin = task19SuperAdmin();
    $administrador = task19Admin();
    $semPermissao = User::factory()->create();

    $recipientIds = AdminRecipients::withPermission('car-washes.approve')->pluck('id');

    // UserSeeder (task-23) já semeia outros admins — checa só que os
    // dois criados aqui entram e quem não tem permission fica de fora,
    // sem assumir a lista fechada.
    expect($recipientIds)->toContain($superAdmin->id)
        ->and($recipientIds)->toContain($administrador->id)
        ->and($recipientIds)->not->toContain($semPermissao->id)
        ->and($recipientIds->duplicates())->toBeEmpty();
});

test('cadastro de novo lava-rapido notifica os admins com car-washes.approve', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = task19Admin();
    Notification::fake();

    $this->post('/parceiros/cadastro', [
        'owner_name' => 'Jessica Silva',
        'owner_email' => 'jessica@exemplo.com',
        'owner_phone' => '(41) 99999-0000',
        'password' => 'Senha#Forte1',
        'password_confirmation' => 'Senha#Forte1',
        'car_wash_name' => 'Lava Jato da Jessica',
        'document' => '12345678000199',
        'car_wash_phone' => '(41) 3333-0000',
        'car_wash_email' => 'contato@lavajato.com',
        'address_line' => 'Rua das Flores, 100',
        'city' => 'Curitiba',
        'state' => 'PR',
        'zip_code' => '80000000',
    ]);

    Notification::assertSentTo($admin, NewCarWashPendingApproval::class);
});

test('pedido de ativacao do clube notifica os admins com car-wash-product-subscriptions.approve', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = task19Admin();
    $carWash = CarWash::factory()->approved()->create();
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);
    $payoutPlan = \App\Models\PayoutPlan::factory()->create(['active' => true]);
    Notification::fake();

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.products.club.request'), ['payout_plan_id' => $payoutPlan->id]);

    Notification::assertSentTo($admin, NewClubActivationRequest::class);
});

test('solicitacao de cancelamento notifica os admins com cancellation-requests.approve', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = task19Admin();
    $carWash = CarWash::factory()->approved()->create();
    $subscriber = User::factory()->create();
    $cycle = SubscriptionCycle::factory()->create();
    $redemption = WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);
    $cycle->subscription->update(['user_id' => $subscriber->id]);
    Notification::fake();

    $this->actingAs($subscriber)
        ->post(route('wash.request-cancellation', $redemption), ['reason' => 'Motivo qualquer']);

    Notification::assertSentTo($admin, NewCancellationRequest::class);
});

test('decisao do admin sobre cancelamento notifica o dono do lava-rapido', function () {
    $carWash = CarWash::factory()->approved()->create();
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);
    $cycle = SubscriptionCycle::factory()->create();
    $redemption = WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);
    $request = CancellationRequest::factory()->create([
        'requestable_type' => WashRedemption::class,
        'requestable_id' => $redemption->id,
    ]);
    $admin = User::factory()->create();
    Notification::fake();

    (new CancellationRequestResolver)->approve($request, $admin);

    Notification::assertSentTo($owner, CancellationRequestDecided::class);
});

test('repasse marcado como pago notifica o dono do lava-rapido', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = task19Admin();
    $carWash = CarWash::factory()->approved()->create();
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);
    $payout = Payout::factory()->create(['car_wash_id' => $carWash->id, 'status' => 'pending']);
    Notification::fake();

    $this->actingAs($admin)
        ->post(route('payouts.mark-paid', $payout), ['payment_reference' => 'TED-999']);

    Notification::assertSentTo($owner, PayoutPaid::class);
});

test('cobranca de estacionamento sinalizada notifica os admins com parking-billing-charges.index', function () {
    Carbon::setTestNow('2026-02-15 12:00:00');
    $this->seed(DatabaseSeeder::class);
    $admin = task19Admin();

    $carWash = CarWash::factory()->approved()->create();
    CarWashProductSubscription::factory()->active()->create([
        'car_wash_id' => $carWash->id,
        'product' => 'estacionamento',
    ]);
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);
    $lot = ParkingLot::factory()->create(['car_wash_id' => $carWash->id, 'total_spots' => 1]);
    $rate = ParkingRate::factory()->create(['parking_lot_id' => $lot->id]);

    // total_spots=1, padrao max_turns_per_day_per_spot=6, fevereiro
    // tem 28 dias -> teto plausivel = 168; 200 sessoes estoura.
    ParkingSession::factory()->closed()->count(200)->create([
        'parking_lot_id' => $lot->id,
        'parking_rate_id' => $rate->id,
        'exit_at' => now()->subMonthNoOverflow(),
        'amount_charged_cents' => 100,
    ]);

    Notification::fake();

    $this->artisan('parking-billing:evaluate');

    Notification::assertSentTo($admin, ParkingBillingChargeFlagged::class);
});

test('confirmacao de lavagem notifica o assinante', function () {
    $carWash = CarWash::factory()->approved()->create();
    $employee = User::factory()->create();
    $carWash->users()->attach($employee->id, ['role' => 'employee']);
    $subscriber = User::factory()->create();
    $cycle = SubscriptionCycle::factory()->create(['quota_total' => 4, 'quota_used' => 0]);
    $cycle->subscription->update(['user_id' => $subscriber->id]);
    WashRedemption::factory()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
        'confirmation_code' => '654321',
    ]);
    Notification::fake();

    $this->actingAs($employee)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.washes.confirm.store'), ['confirmation_code' => '654321']);

    Notification::assertSentTo($subscriber, WashRedemptionConfirmed::class);
});

test('bonus de indicacao concedido notifica o indicador', function () {
    $referrer = User::factory()->create();
    $cycle = SubscriptionCycle::factory()->create();
    $cycle->subscription->update(['user_id' => $referrer->id]);
    ReferralReward::factory()->qualified()->create(['referrer_user_id' => $referrer->id]);
    Notification::fake();

    ReferralRewardGranter::grantPendingRewardsFor($cycle);

    Notification::assertSentTo($referrer, ReferralRewardGranted::class);
});
