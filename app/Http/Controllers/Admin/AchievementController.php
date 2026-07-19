<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAchievementRequest;
use App\Models\Achievement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Catálogo de conquistas (task-20, seção 6) — mesmo padrão de
 * payout-plans.*.
 */
class AchievementController extends Controller
{
    public function index(): View
    {
        $achievements = Achievement::orderBy('name')->paginate(15);

        return view('admin.achievements.index', compact('achievements'));
    }

    public function create(): View
    {
        return view('admin.achievements.create');
    }

    public function store(StoreAchievementRequest $request): RedirectResponse
    {
        Achievement::create($request->validated());

        return redirect()->route('achievements.index')->with('success', 'Conquista criada.');
    }

    public function edit(Achievement $achievement): View
    {
        return view('admin.achievements.edit', compact('achievement'));
    }

    public function update(StoreAchievementRequest $request, Achievement $achievement): RedirectResponse
    {
        $achievement->update($request->validated());

        return redirect()->route('achievements.index')->with('success', 'Conquista atualizada.');
    }
}
