@php
    $fieldType = $type ?? 'text';
    $fieldKey = str_replace(['[', ']'], ['.', ''], $name);
    $fieldId = 'field-'.str_replace(['[', ']', '.'], '-', $name);
    $fieldValue = old($fieldKey, $value ?? '');
    $control = 'admin-control mt-1.5';
    $invalid = $errors->has($fieldKey);
    $descriptionIds = implode(' ', array_filter([!empty($help) ? $fieldId.'-help' : null, $invalid ? $fieldId.'-error' : null]));
@endphp
<div>
    @if($fieldType === 'checkbox')
        <input type="hidden" name="{{ $name }}" value="0">
        <label class="flex min-h-11 items-center gap-3 text-sm font-semibold" for="{{ $fieldId }}"><input type="checkbox" id="{{ $fieldId }}" name="{{ $name }}" value="1" @checked($fieldValue) class="size-5 accent-teal-800" @if($invalid) aria-invalid="true" @endif @if($descriptionIds) aria-describedby="{{ $descriptionIds }}" @endif>{{ $label }}</label>
    @else
        <label for="{{ $fieldId }}" class="admin-field-label">{{ $label }}</label>
        @if($fieldType === 'textarea')
            <textarea id="{{ $fieldId }}" name="{{ $name }}" rows="{{ $rows ?? 4 }}" class="{{ $control }}" @if($invalid) aria-invalid="true" @endif @if($descriptionIds) aria-describedby="{{ $descriptionIds }}" @endif>{{ $fieldValue }}</textarea>
        @elseif($fieldType === 'select')
            <select id="{{ $fieldId }}" name="{{ $name }}" class="{{ $control }}" @if($invalid) aria-invalid="true" @endif @if($descriptionIds) aria-describedby="{{ $descriptionIds }}" @endif>@foreach($choices ?? [] as $key => $option)<option value="{{ $key }}" @selected((string)$fieldValue === (string)$key)>{{ $option }}</option>@endforeach</select>
        @elseif($fieldType === 'file')
            <input id="{{ $fieldId }}" name="{{ $name }}" type="file" accept="{{ $accept ?? 'image/jpeg,image/png,image/webp' }}" class="{{ $control }}" @if($invalid) aria-invalid="true" @endif @if($descriptionIds) aria-describedby="{{ $descriptionIds }}" @endif>
        @else
            <input id="{{ $fieldId }}" name="{{ $name }}" type="{{ $fieldType }}" value="{{ $fieldValue }}" @if($fieldType === 'number') step="1" @endif class="{{ $control }}" @if($invalid) aria-invalid="true" @endif @if($descriptionIds) aria-describedby="{{ $descriptionIds }}" @endif>
        @endif
    @endif
    @if(!empty($help))<p id="{{ $fieldId }}-help" class="admin-muted mt-2 text-xs leading-relaxed">{{ $help }}</p>@endif
    @error($fieldKey)<p id="{{ $fieldId }}-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
</div>

