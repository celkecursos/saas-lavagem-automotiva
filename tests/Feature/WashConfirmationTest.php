<?php

use App\Models\CarWash;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Models\WashRedemption;

// Ver task-13, seção 2.4 — confirmação pelo funcionário.

function employeeOfWashCarWash(CarWash $carWash): User
{
    $employee = User::factory()->create();
    $carWash->users()->attach($employee->id, ['role' => 'employee']);

    return $employee;
}

test('confirmar com codigo certo + car_wash correto debita exatamente 1', function () {
    $carWash = CarWash::factory()->approved()->create();
    $employee = employeeOfWashCarWash($carWash);
    $cycle = SubscriptionCycle::factory()->create(['quota_total' => 4, 'quota_used' => 0]);
    $redemption = WashRedemption::factory()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
        'confirmation_code' => '123456',
    ]);

    session(['current_car_wash_id' => $carWash->id]);

    $this->actingAs($employee)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.washes.confirm.store'), ['confirmation_code' => '123456'])
        ->assertRedirect(route('panel.washes.confirm'));

    $redemption->refresh();

    expect($redemption->status)->toBe('completed')
        ->and($redemption->redeemed_at)->not->toBeNull()
        ->and($redemption->confirmed_by_user_id)->toBe($employee->id)
        ->and($cycle->fresh()->quota_used)->toBe(1);
});

test('codigo de um lava-rapido nao pode ser confirmado por outro car_wash_user', function () {
    $ownerCarWash = CarWash::factory()->approved()->create();
    $otherCarWash = CarWash::factory()->approved()->create();
    $employeeOfOther = employeeOfWashCarWash($otherCarWash);
    $cycle = SubscriptionCycle::factory()->create(['quota_total' => 4]);
    $redemption = WashRedemption::factory()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $ownerCarWash->id,
        'confirmation_code' => '111111',
    ]);

    $this->actingAs($employeeOfOther)
        ->withSession(['current_car_wash_id' => $otherCarWash->id])
        ->post(route('panel.washes.confirm.store'), ['confirmation_code' => '111111'])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($redemption->fresh()->status)->toBe('requested')
        ->and($cycle->fresh()->quota_used)->toBe(0);
});

test('codigo expirado nao e mais aceito', function () {
    $carWash = CarWash::factory()->approved()->create();
    $employee = employeeOfWashCarWash($carWash);
    $cycle = SubscriptionCycle::factory()->create(['quota_total' => 4]);
    WashRedemption::factory()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
        'confirmation_code' => '222222',
        'code_expires_at' => now()->subMinute(),
    ]);

    $this->actingAs($employee)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.washes.confirm.store'), ['confirmation_code' => '222222'])
        ->assertSessionHas('error');

    expect($cycle->fresh()->quota_used)->toBe(0);
});

test('codigo ja confirmado nao pode ser confirmado de novo', function () {
    $carWash = CarWash::factory()->approved()->create();
    $employee = employeeOfWashCarWash($carWash);
    $cycle = SubscriptionCycle::factory()->create(['quota_total' => 4, 'quota_used' => 1]);
    WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
        'confirmation_code' => '333333',
    ]);

    $this->actingAs($employee)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.washes.confirm.store'), ['confirmation_code' => '333333'])
        ->assertSessionHas('error');

    expect($cycle->fresh()->quota_used)->toBe(1);
});
