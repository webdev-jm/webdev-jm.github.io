<?php

use Livewire\Component;
use Livewire\Attributes\Modelable;

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
    public $prefix      = '';   // icon class e.g. "fas fa-user"
    public $suffix      = '';   // icon class e.g. "fas fa-check"
    public $size        = '';   // 'sm' | '' | 'lg'

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
        </label>
    @endif

    @if($prefix || $suffix)
        <div class="input-group @if($size) input-group-{{ $size }} @endif">

            @if($prefix)
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="{{ $prefix }}"></i>
                    </span>
                </div>
            @endif

            <input
                type="text"
                wire:model="value"
                id="{{ $id }}"
                name="{{ $fieldName }}"
                value="{{ $fieldValue }}"
                placeholder="{{ $placeholder }}"
                class="form-control @if($size) form-control-{{ $size }} @endif @error($fieldName) is-invalid @enderror"
                @if($required) required @endif
                @if($disabled) disabled @endif
                @if($readonly) readonly @endif
            >

            @if($suffix)
                <div class="input-group-append">
                    <span class="input-group-text">
                        <i class="{{ $suffix }}"></i>
                    </span>
                </div>
            @endif
        </div>

    @else
        <input
            type="text"
            wire:model="value"
            id="{{ $id }}"
            name="{{ $fieldName }}"
            value="{{ $fieldValue }}"
            placeholder="{{ $placeholder }}"
            class="form-control @if($size) form-control-{{ $size }} @endif @error($fieldName) is-invalid @enderror"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
        >
    @endif

    @error($fieldName)
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror

    @if($helpText)
        <small class="form-text text-muted">{{ $helpText }}</small>
    @endif

</div>
