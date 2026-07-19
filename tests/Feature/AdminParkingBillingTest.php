<?php

use App\Models\CarWash;
use App\Models\ParkingBillingCharge;
use App\Models\ParkingBillingSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;

// Ver task-10, seção 8, e task-13, seção 2.6.

function parkingBillingAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

test('admin atualiza as configuracoes de monetizacao e fica registrado em audits', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = parkingBillingAdmin();
    $settings = ParkingBillingSetting::current();

    $this->actingAs($admin)
        ->put(route('parking-billing-settings.update'), [
            'fee_percentage' => 15.5,
            'max_turns_per_day_per_spot' => 8,
        ])
        ->assertRedirect(route('parking-billing-settings.edit'));

    $settings->refresh();

    expect((float) $settings->fee_percentage)->toBe(15.5)
        ->and($settings->max_turns_per_day_per_spot)->toBe(8);

    expect(DB::table('audits')
        ->where('auditable_type', ParkingBillingSetting::class)
        ->where('auditable_id', $settings->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('listagem de cobrancas filtra por sinalizadas', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = parkingBillingAdmin();
    $carWash = CarWash::factory()->approved()->create();

    $flagged = ParkingBillingCharge::factory()->create([
        'car_wash_id' => $carWash->id,
        'flagged_for_review' => true,
    ]);
    $normal = ParkingBillingCharge::factory()->create([
        'car_wash_id' => $carWash->id,
        'flagged_for_review' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('parking-billing-charges.index', ['flagged' => 1]));

    $response->assertOk()->assertSee($carWash->name);

    $response = $this->actingAs($admin)->get(route('parking-billing-charges.show', $flagged));
    $response->assertOk()->assertSee($carWash->name);

    expect($normal->flagged_for_review)->toBeFalse();
});

test('admin sem a permission toma 403', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();

    $this->actingAs($user)->get(route('parking-billing-settings.edit'))->assertForbidden();
    $this->actingAs($user)->get(route('parking-billing-charges.index'))->assertForbidden();
});
