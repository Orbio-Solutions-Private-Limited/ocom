@php
    $fieldName = 'customAttributes['.$field->id.']';
    $oldValue = old('customAttributes.'.$field->id);
@endphp

@if ($field->type === 'textarea')
    <x-shop::ui.form.textarea
        :name="$fieldName"
        :required="$field->is_required"
        :label="$field->label"
        rows="4"
    >{{ $oldValue }}</x-shop::ui.form.textarea>
@elseif ($field->type === 'select')
    <x-shop::ui.form.select
        :name="$fieldName"
        :required="$field->is_required"
        :label="$field->label"
    >
        <option value="">
            @lang('shop::app.customers.account.rma.create.select')
        </option>

        @foreach ($field->options ?? [] as $option)
            <option
                value="{{ $option->value }}"
                @selected((string) $oldValue === (string) $option->value)
            >
                {{ $option->name }}
            </option>
        @endforeach
    </x-shop::ui.form.select>
@elseif ($field->type === 'multiselect')
    <x-shop::ui.form.select
        :name="$fieldName.'[]'"
        :required="$field->is_required"
        :label="$field->label"
        multiple
    >
        @foreach ($field->options ?? [] as $option)
            <option
                value="{{ $option->value }}"
                @selected(in_array($option->value, (array) $oldValue, false))
            >
                {{ $option->name }}
            </option>
        @endforeach
    </x-shop::ui.form.select>
@elseif (in_array($field->type, ['radio', 'checkbox'], true))
    <div>
        <x-shop::ui.form.label :required="$field->is_required">
            {{ $field->label }}
        </x-shop::ui.form.label>

        <div class="mt-1 space-y-2">
            @foreach ($field->options ?? [] as $option)
                <label class="flex items-center gap-2 text-sm">
                    <input
                        type="{{ $field->type }}"
                        name="{{ $fieldName }}"
                        value="{{ $field->type === 'checkbox' ? $option->value : $option->name }}"
                        @if ($field->type === 'radio' && $field->is_required) required @endif
                    />

                    <span>{{ $option->name }}</span>
                </label>
            @endforeach
        </div>

        <x-shop::ui.form.error :name="$fieldName" />
    </div>
@else
    <x-shop::ui.form.input
        :type="$field->type === 'date' ? 'date' : 'text'"
        :name="$fieldName"
        :required="$field->is_required"
        :label="$field->label"
        :value="$oldValue"
    />
@endif
