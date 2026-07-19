<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Resolve quem deve receber uma notificação de admin pra um evento
 * (task-19, seção 2) — mesma permission de quem pode agir na fila
 * correspondente (task-11). Super Admin não tem permissions explícitas
 * (libera tudo via Gate::before, task-3 seção 6), então é sempre incluído
 * à parte de qualquer permission checada.
 *
 * Usa whereHas() em vez dos scopes role()/permission() do
 * spatie/laravel-permission de propósito: esses scopes lançam
 * RoleDoesNotExist/PermissionDoesNotExist quando o nome não está
 * semeado ainda (comum em testes que não rodam o DatabaseSeeder
 * completo) — aqui a ausência deve resultar em "ninguém", não em
 * exceção.
 */
class AdminRecipients
{
    public static function withPermission(string $permission): Collection
    {
        $superAdmins = User::whereHas('roles', fn ($query) => $query->where('name', 'Super Admin'))->get();

        $withPermission = User::whereHas(
            'permissions',
            fn ($query) => $query->where('name', $permission),
        )->orWhereHas(
            'roles.permissions',
            fn ($query) => $query->where('name', $permission),
        )->get();

        return $superAdmins->merge($withPermission)
            ->unique('id')
            ->values();
    }
}
