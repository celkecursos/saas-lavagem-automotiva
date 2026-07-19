<?php

use App\Models\SubscriptionCycle;
use App\Models\WashRedemption;

// Ver task-8, seção 2, passo 5.

test('expira requested vencido, sem debitar cota', function () {
    $cycle = SubscriptionCycle::factory()->create(['quota_total' => 4, 'quota_used' => 0]);
    $expired = WashRedemption::factory()->create([
        'subscription_cycle_id' => $cycle->id,
        'code_expires_at' => now()->subMinute(),
    ]);

    $this->artisan('wash-redemptions:expire')->assertSuccessful();

    expect($expired->fresh()->status)->toBe('expired')
        ->and($cycle->fresh()->quota_used)->toBe(0);
});

test('nao mexe em codigo ainda valido', function () {
    $valid = WashRedemption::factory()->create(['code_expires_at' => now()->addMinutes(5)]);

    $this->artisan('wash-redemptions:expire');

    expect($valid->fresh()->status)->toBe('requested');
});

test('nao mexe em codigo ja completed ou canceled mesmo se code_expires_at no passado', function () {
    $completed = WashRedemption::factory()->completed()->create(['code_expires_at' => now()->subDay()]);
    $canceled = WashRedemption::factory()->create(['status' => 'canceled', 'code_expires_at' => now()->subDay()]);

    $this->artisan('wash-redemptions:expire');

    expect($completed->fresh()->status)->toBe('completed')
        ->and($canceled->fresh()->status)->toBe('canceled');
});
