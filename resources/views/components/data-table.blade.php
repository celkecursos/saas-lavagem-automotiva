{{-- Tabela com paginação, cabeçalho ordenável e estado vazio embutido
     (task-14, seção 6).

     Uso:
       <x-data-table :rows="$carWashes" empty-message="Nenhum lava-rápido">
           <x-slot:head>
               <x-data-table.th field="name" sortable>Nome</x-data-table.th>
               <x-data-table.th>Status</x-data-table.th>
           </x-slot:head>
           @foreach ($carWashes as $carWash)
               <tr>...</tr>
           @endforeach
       </x-data-table>
--}}
@props(['rows', 'emptyMessage' => 'Nenhum registro encontrado'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-backgroundseconddark rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden']) }}>
    @if (count($rows) === 0)
        <x-empty-state :message="$emptyMessage" />
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                <thead class="bg-gray-50 dark:bg-backgroundthirddark">
                    <tr>
                        {{ $head }}
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                    {{ $slot }}
                </tbody>
            </table>
        </div>

        @if ($rows instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-800">
                {{ $rows->links() }}
            </div>
        @endif
    @endif
</div>
