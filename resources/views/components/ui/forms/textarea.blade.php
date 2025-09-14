@props([
    'name' => '',
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'required' => false,
    'disabled' => false,
    'rows' => 3,
    'error' => null,
    'class' => '',
    'inputClass' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
    'labelClass' => 'block text-sm font-medium text-gray-700'
])

<div class="{{ $class }}">
    @if($label)
        <label for="{{ $name }}" class="{{ $labelClass }}">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <textarea 
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        class="{{ $inputClass }} @error($name) border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
        {{ $attributes }}
    >{{ $value }}</textarea>
    
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $error ?? $message }}</p>
    @enderror
</div>
