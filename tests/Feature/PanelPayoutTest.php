<?php

use App\Models\CarWash;
use App\Models\Payout;
use App\Models\PayoutItem;
use App\Models\User;
use App\Models\WashRedemption;

// Ver task-9, seção 4.

test('lava-rapido ve so os proprios repasses', function () {
    $carWash = CarWash::factory()->approved()->create();
    $otherCarWash = CarWash::factory()->approved()->create();
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);

    Payout::factory()->create(['car_wash_id' => $carWash->id, 'total_amount_cents' => 5000]);
    Payout::factory()->create(['car_wash_id' => $otherCarWash->id]);

    $response = $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->get('/painel/repasses');

    $response->assertOk();
    expect(Payout::where('car_wash_id', $carWash->id)->count())->toBe(1);
});

test('detalhe do repasse lista os payout_items', function () {
    $carWash = CarWash::factory()->approved()->create();
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);
    $payout = Payout::factory()->create(['car_wash_id' => $carWash->id]);
    $redemption = WashRedemption::factory()->completed()->create(['car_wash_id' => $carWash->id]);
    PayoutItem::factory()->create(['payout_id' => $payout->id, 'wash_redemption_id' => $redemption->id, 'amount_cents' => 1500]);

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->get("/painel/repasses/{$payout->id}")
        ->assertOk()
        ->assertSee('#'.$redemption->id);
});

test('lava-rapido nao acessa repasse de outro car_wash', function () {
    $carWash = CarWash::factory()->approved()->create();
    $otherCarWash = CarWash::factory()->approved()->create();
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);
    $payout = Payout::factory()->create(['car_wash_id' => $otherCarWash->id]);

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->get("/painel/repasses/{$payout->id}")
        ->assertForbidden();
});
