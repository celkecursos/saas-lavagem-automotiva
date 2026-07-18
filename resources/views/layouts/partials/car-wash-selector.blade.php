{{-- Seletor de "lava-rápido atual" — só aparece quando o usuário tem
     mais de 1 car_wash vinculado (task-5, seção 7 / task-14, seção 3).
     O contexto vive na sessão (current_car_wash_id), resolvido pelo
     middleware SetCurrentCarWash. --}}
@auth
    @php
        $linkedCarWashes = \Illuminate\Support\Facades\DB::table('car_wash_users')
            ->join('car_washes', 'car_washes.id', '=', 'car_wash_users.car_wash_id')
            ->where('car_wash_users.user_id', auth()->id())
            ->whereNull('car_washes.deleted_at')
            ->orderBy('car_washes.name')
            ->get(['car_washes.id', 'car_washes.name']);
    @endphp

    @if ($linkedCarWashes->count() > 1 && Route::has('panel.car-wash.switch'))
        <form method="POST" action="{{ route('panel.car-wash.switch') }}">
            @csrf
            <select name="car_wash_id" onchange="this.form.submit()"
                    class="rounded border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundseconddark text-sm text-gray-700 dark:text-gray-200">
                @foreach ($linkedCarWashes as $carWash)
                    <option value="{{ $carWash->id }}" @selected(session('current_car_wash_id') == $carWash->id)>
                        {{ $carWash->name }}
                    </option>
                @endforeach
            </select>
        </form>
    @endif
@endauth
