{{-- Wrapper de label + input + erro de validação — padroniza os
     formulários das tasks 4/5/7/9/10/11 (task-14, seção 6).

     Uso:
       <x-form-field label="Nome" name="name" :value="old('name')" required />
       <x-form-field label="Estado" name="state">
           <select name="state" id="state" class="...">...</select>
       </x-form-field>  (slot substitui o input padrão)
--}}
@props(['label', 'name', 'type' => 'text', 'value' => null])

<div {{ $attributes->only('class')->merge(['class' => 'mb-4']) }}>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $label }}</label>

    @if ($slot->isEmpty())
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
               value="{{ old($name, $value) }}"
               {{ $attributes->except('class') }}
               class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-gray-900 dark:text-gray-100 text-sm focus:border-blue-500 focus:ring-blue-500 @error($name) border-red-500 @enderror">
    @else
        {{ $slot }}
    @endif

    @error($name)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
