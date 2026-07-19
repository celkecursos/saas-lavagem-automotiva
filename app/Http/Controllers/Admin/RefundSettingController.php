<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Janela estendida de reembolso self-service depois dos 7 dias fixos
 * do CDC (task-21, seção 1). Singleton.
 */
class RefundSettingController extends Controller
{
    public function edit(): View
    {
        $settings = RefundSetting::current();

        return view('admin.refund-settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'extended_self_service_enabled' => ['nullable', 'boolean'],
            'extended_self_service_until_days' => ['nullable', 'integer', 'min:1'],
        ]);

        RefundSetting::current()->update([
            'extended_self_service_enabled' => (bool) ($validated['extended_self_service_enabled'] ?? false),
            'extended_self_service_until_days' => $validated['extended_self_service_until_days'] ?? null,
        ]);

        return redirect()->route('refund-settings.edit')->with('success', 'Configurações salvas.');
    }
}
