<?php

use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;
use Database\Seeders\PlanSeeder;

// Ver task-12, e task-13.

test('home renderiza com hero, planos ativos com vantagens e cta primario pro assinante', function () {
    $plan = Plan::factory()->create(['name' => 'Plano Turbo', 'active' => true]);
    PlanFeature::factory()->create(['plan_id' => $plan->id, 'label' => 'Lavagem em qualquer unidade', 'active' => true, 'sort_order' => 1]);
    $inactivePlan = Plan::factory()->create(['name' => 'Plano Descontinuado', 'active' => false]);

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Plano Turbo')
        ->assertSee('Lavagem em qualquer unidade')
        ->assertDontSee('Plano Descontinuado')
        ->assertSee('Ver planos')
        ->assertSee('Quero ser parceiro');
});

test('home lista so lava-rapidos aprovados com clube de lavagem ativo', function () {
    $approved = CarWash::factory()->approved()->create(['name' => 'Lava Rápido Aprovado']);
    CarWashProductSubscription::factory()->active()->create(['car_wash_id' => $approved->id, 'product' => 'clube_lavagem']);

    $pending = CarWash::factory()->create(['name' => 'Lava Rápido Pendente', 'status' => 'pending']);

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Lava Rápido Aprovado')
        ->assertDontSee('Lava Rápido Pendente');
});

test('plano sem vantagens cadastradas nao mostra secao de vantagens nem placeholder', function () {
    Plan::factory()->create(['name' => 'Plano Simples', 'active' => true]);

    $response = $this->get('/');

    $response->assertOk()->assertDontSee('nenhuma vantagem');
});

test('seeder publica os 3 planos da vitrine com vantagens e destaca o do meio', function () {
    $this->seed(PlanSeeder::class);

    $plans = Plan::where('active', true)->orderBy('price_cents')->get();

    expect($plans->pluck('name')->all())->toBe(['Essencial', 'Completo', 'Premium'])
        ->and($plans->every(fn ($plan) => $plan->features()->count() > 0))->toBeTrue();

    $this->get('/')->assertOk()
        ->assertSee('Essencial')
        ->assertSee('Completo')
        ->assertSee('Premium')
        // Só o plano do meio ganha o selo de destaque.
        ->assertSee('Mais vendido');
});

test('header do site mostra entrar quando deslogado', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Entrar')
        ->assertSee('Assinar agora')
        ->assertDontSee('Dashboard');
});

test('header do site mostra o primeiro nome e o menu do usuario logado', function () {
    $user = User::factory()->create(['name' => 'Marcos Antonio da Silva', 'email' => 'marcos@exemplo.com']);

    $response = $this->actingAs($user)->get('/');

    $response->assertOk()
        // Só o primeiro nome — nome composto estouraria a largura do header.
        ->assertSee('Marcos')
        ->assertDontSee('Marcos Antonio da Silva')
        ->assertSee('marcos@exemplo.com')
        ->assertSee('Dashboard')
        ->assertSee('Sair')
        ->assertSee(route('dashboard'), false)
        // Some o CTA de visitante.
        ->assertDontSee('Assinar agora');
});

test('paginas institucionais renderizam', function () {
    $this->get(route('about'))->assertOk()->assertSee('Sobre');
    $this->get(route('terms'))->assertOk()->assertSee('Termos de uso');
    $this->get(route('privacy'))->assertOk()->assertSee('Privacidade');
    $this->get(route('contact'))->assertOk()->assertSee('Contato');
});

test('footer tem os links institucionais e o link discreto de parceria', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee(route('about'), false)
        ->assertSee(route('terms'), false)
        ->assertSee(route('privacy'), false)
        ->assertSee(route('contact'), false)
        ->assertSee(route('partners.register'), false);
});

test('menu de topo nao tem link de seja parceiro', function () {
    $response = $this->get('/');

    $response->assertOk();
    // "Seja parceiro"/"Quero ser parceiro" só aparece 1x, na seção
    // secundária discreta — nunca no menu de topo (task-12, seção 4).
    expect(substr_count($response->getContent(), 'Quero ser parceiro'))->toBe(1);
});

test('sitemap.xml lista as paginas publicas principais', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertSee(route('home'), false)
        ->assertSee(route('plans.index'), false)
        ->assertSee(route('about'), false);

    expect($response->headers->get('Content-Type'))->toContain('application/xml');
});

test('robots.txt bloqueia bots de IA e areas autenticadas', function () {
    // Arquivo estático em public/ — servido pelo webserver, não pelo
    // router do Laravel, então testado direto no filesystem em vez de
    // via $this->get() (que 404 pra arquivos estáticos no test kernel).
    $content = file_get_contents(public_path('robots.txt'));

    expect($content)->toContain('GPTBot')
        ->and($content)->toContain('ClaudeBot')
        ->and($content)->toContain('Disallow: /admin')
        ->and($content)->toContain('Disallow: /painel');
});

test('head inclui meta description e open graph', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('name="description"', false)
        ->assertSee('property="og:title"', false)
        ->assertSee('property="og:image"', false);
});
