<?php

use Livewire\Attributes\Modelable;
use Livewire\Component;

new class extends Component
{
    #[Modelable]
    public $value = '';

    public $id          = '';
    public $name        = '';
    public $label       = '';
    public $placeholder = '';
    public $required    = false;
    public $disabled    = false;
    public $readonly    = false;
    public $helpText    = '';
    public $rows        = 4;
    public $maxlength   = 0;      // 0 = unlimited
    public $showCount   = false;
    public $size        = '';     // 'sm' | '' | 'lg'

    public $fieldValue = '';

    public function mount(): void
    {
        $this->value      = old($this->name ?: $this->id, $this->value);
        $this->fieldValue = $this->value;
    }
};
?>

<div class="form-group">

    @php $fieldName = $name ?: $id; @endphp

    @if($label)
        <label @if($id) for="{{ $id }}" @endif>
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
            @if($showCount && $maxlength > 0)
                <span class="float-right">
                    <small class="text-muted">
                        <span id="{{ $id }}_count">{{ strlen($value) }}</span>/{{ $maxlength }}
                    </small>
                </span>
            @endif
        </label>
    @endif

    <textarea
        wire:model="value"
        id="{{ $id }}"
        name="{{ $fieldName }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        class="form-control @if($size) form-control-{{ $size }} @endif @error($fieldName) is-invalid @enderror"
        @if($showCount && $maxlength > 0)
            x-on:input="document.getElementById('{{ $id }}_count').textContent = $event.target.value.length"
        @endif
        @if($maxlength > 0) maxlength="{{ $maxlength }}" @endif
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
    >{{ $fieldValue }}</textarea>

    @error($fieldName)
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror

    @if($helpText)
        <small class="form-text text-muted">{{ $helpText }}</small>
    @endif

</div>
