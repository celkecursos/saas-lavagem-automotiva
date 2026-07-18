<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterSubscriberRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Cadastro self-service do assinante (task-7, seção 1). A capacidade de
 * assinante vem de ter uma subscriptions, não do role (task-3, seção 1).
 */
class RegisterSubscriberController extends Controller
{
    public function create(): View
    {
        return view('subscriber.register');
    }

    public function store(RegisterSubscriberRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'cpf' => $validated['cpf'] ?? null,
            'password' => $validated['password'],
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('plans.index');
    }
}
