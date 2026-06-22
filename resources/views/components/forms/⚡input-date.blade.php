<?php

use Livewire\Attributes\Modelable;
use Livewire\Component;

new class extends Component
{
    #[Modelable]
    public $value = '';

    public $id       = '';
    public $name     = '';
    public $label    = '';
    public $required = false;
    public $disabled = false;
    public $readonly = false;
    public $helpText = '';
    public $type     = 'date';   // 'date' | 'datetime-local' | 'time' | 'month' | 'week'
    public $min      = '';
    public $max      = '';
    public $size     = '';       // 'sm' | '' | 'lg'

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

    <div class="input-group @if($size) input-group-{{ $size }} @endif">
        <div class="input-group-prepend">
            <span class="input-group-text">
                @switch($type)
                    @case('time')
                        <i class="fas fa-clock"></i>
                        @break
                    @case('month')
                    @case('week')
                        <i class="fas fa-calendar-alt"></i>
                        @break
                    @default
                        <i class="fas fa-calendar-day"></i>
                @endswitch
            </span>
        </div>

        <input
            type="{{ $type }}"
            wire:model="value"
            id="{{ $id }}"
            name="{{ $fieldName }}"
            value="{{ $fieldValue }}"
            class="form-control @if($size) form-control-{{ $size }} @endif @error($fieldName) is-invalid @enderror"
            @if($min) min="{{ $min }}" @endif
            @if($max) max="{{ $max }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
        >
    </div>

    @error($fieldName)
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror

    @if($helpText)
        <small class="form-text text-muted">{{ $helpText }}</small>
    @endif

</div>
