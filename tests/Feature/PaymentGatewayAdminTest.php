<?php

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

// Ver task-13, seções 2.7 (permissões) e 2.8 (auditoria da troca de
// gateway ativo).

function makeGatewayAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

test('admin sem a permission toma 403 na listagem de gateways', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();

    $this->actingAs($user)->get('/admin/gateways-pagamento')->assertForbidden();
});

test('admin com permission lista e cria gateway', function () {
    $this->seed(DatabaseSeeder::class);
    $type = PaymentGatewayType::factory()->create(['name' => 'PagSeguro / PagBank']);
    $admin = makeGatewayAdmin();

    $this->actingAs($admin)->get('/admin/gateways-pagamento')->assertOk();

    $this->actingAs($admin)->post('/admin/gateways-pagamento', [
        'payment_gateway_type_id' => $type->id,
        'label' => 'PagSeguro - testes',
        'sandbox_mode' => '1',
        'credentials' => ['token' => 'tok-123'],
    ])->assertRedirect(route('payment-gateways.index'));

    $gateway = PaymentGateway::first();

    expect($gateway->label)->toBe('PagSeguro - testes')
        ->and($gateway->is_active)->toBeFalse()
        ->and($gateway->credentials['token'])->toBe('tok-123');
});

test('ativar um gateway desativa os demais e gera auditoria', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = makeGatewayAdmin();
    $first = PaymentGateway::factory()->active()->create();
    $second = PaymentGateway::factory()->create();

    $this->actingAs($admin)
        ->post("/admin/gateways-pagamento/{$second->id}/ativar")
        ->assertRedirect(route('payment-gateways.index'));

    expect($first->fresh()->is_active)->toBeFalse()
        ->and($second->fresh()->is_active)->toBeTrue();

    // Troca de gateway ativo é rastreável em audits (task-13, seção 2.8).
    expect(DB::table('audits')
        ->where('auditable_type', PaymentGateway::class)
        ->where('auditable_id', $second->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('update com token em branco mantem as credenciais atuais', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = makeGatewayAdmin();
    $gateway = PaymentGateway::factory()->create([
        'credentials' => ['token' => 'tok-original'],
    ]);

    $this->actingAs($admin)->put("/admin/gateways-pagamento/{$gateway->id}", [
        'label' => 'Novo rótulo',
        'sandbox_mode' => '1',
        'credentials' => ['token' => ''],
    ])->assertRedirect(route('payment-gateways.index'));

    expect($gateway->fresh()->credentials['token'])->toBe('tok-original')
        ->and($gateway->fresh()->label)->toBe('Novo rótulo');
});
