@props(['label', 'name', 'values' => [], 'textarea' => false, 'required' => ['uz', 'ru']])

<fieldset class="space-y-3">
    <legend class="mb-1 text-sm font-medium">{{ $label }}</legend>
    <div class="grid gap-3 md:grid-cols-3">
        @foreach (['uz' => "O'zbekcha", 'ru' => 'Русский', 'en' => 'English'] as $locale => $language)
            <x-admin.field :label="$language.(in_array($locale, $required) ? ' *' : '')" :name="$name.'.'.$locale">
                @if ($textarea)
                    <textarea name="{{ $name }}[{{ $locale }}]" rows="3" class="admin-input">{{ old($name.'.'.$locale, $values[$locale] ?? '') }}</textarea>
                @else
                    <input type="text" name="{{ $name }}[{{ $locale }}]" value="{{ old($name.'.'.$locale, $values[$locale] ?? '') }}" class="admin-input" @required(in_array($locale, $required))>
                @endif
            </x-admin.field>
        @endforeach
    </div>
</fieldset>
