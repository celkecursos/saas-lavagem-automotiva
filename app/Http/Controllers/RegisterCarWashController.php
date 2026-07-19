<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterCarWashRequest;
use App\Models\CarWash;
use App\Models\User;
use App\Notifications\CarWashRegistrationReceived;
use App\Notifications\NewCarWashPendingApproval;
use App\Support\AdminRecipients;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Cadastro self-service do lava-rápido (task-5, seção 2): cria o
 * responsável (users), o estabelecimento (car_washes, status pending) e
 * o vínculo owner numa transação; dispara verificação de e-mail e loga
 * direto no painel em modo "aguardando aprovação".
 */
class RegisterCarWashController extends Controller
{
    public function create(): View
    {
        return view('partners.register');
    }

    public function store(RegisterCarWashRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        [$user, $carWash] = DB::transaction(function () use ($validated) {
            // role='user': a capacidade de gerenciar o lava-rápido vem do
            // vínculo owner abaixo, não do role (task-3, seção 1).
            $user = User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => $validated['password'],
                'phone' => $validated['owner_phone'],
            ]);

            $carWash = CarWash::create([
                'name' => $validated['car_wash_name'],
                'slug' => $this->uniqueSlug($validated['car_wash_name']),
                'document' => $validated['document'],
                'phone' => $validated['car_wash_phone'] ?? null,
                'email' => $validated['car_wash_email'],
                'address_line' => $validated['address_line'],
                'city' => $validated['city'],
                'state' => Str::upper($validated['state']),
                'zip_code' => $validated['zip_code'],
                'status' => 'pending',
            ]);

            $carWash->users()->attach($user->id, ['role' => 'owner']);

            return [$user, $carWash];
        });

        // Verificação de e-mail padrão do Laravel (MustVerifyEmail) — o
        // cadastro só entra na frente da fila do admin depois de
        // verificado (task-5, seção 2).
        event(new Registered($user));

        $user->notify(new CarWashRegistrationReceived($carWash));
        Notification::send(AdminRecipients::withPermission('car-washes.approve'), new NewCarWashPendingApproval($carWash));

        Auth::login($user);

        return redirect()->route('panel.dashboard')
            ->with('success', 'Cadastro recebido! Verifique seu e-mail. Seu lava-rápido está aguardando aprovação da plataforma.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;

        for ($i = 2; CarWash::withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
