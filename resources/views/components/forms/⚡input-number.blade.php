<?php

use Livewire\Attributes\Modelable;
use Livewire\Component;

new class extends Component
{
    #[Modelable]
    public mixed $value = '';

    public $id          = '';
    public $name        = '';
    public $label       = '';
    public $placeholder = '0';
    public $required    = false;
    public $disabled    = false;
    public $readonly    = false;
    public $helpText    = '';
    public $prefix      = '';   // icon class e.g. "fas fa-hashtag"
    public $suffix      = '';   // icon class or unit text e.g. "kg"
    public $min         = null;
    public $max         = null;
    public $step        = null;
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
                        @if(str_contains($prefix, ' ') || str_starts_with($prefix, 'fa'))
                            <i class="{{ $prefix }}"></i>
                        @else
                            {{ $prefix }}
                        @endif
                    </span>
                </div>
            @endif

            <input
                type="number"
                wire:model="value"
                id="{{ $id }}"
                name="{{ $fieldName }}"
                value="{{ $fieldValue }}"
                placeholder="{{ $placeholder }}"
                class="form-control @if($size) form-control-{{ $size }} @endif @error($fieldName) is-invalid @enderror"
                @if(!is_null($min))  min="{{ $min }}"   @endif
                @if(!is_null($max))  max="{{ $max }}"   @endif
                @if(!is_null($step)) step="{{ $step }}" @endif
                @if($required) required @endif
                @if($disabled) disabled @endif
                @if($readonly) readonly @endif
            >

            @if($suffix)
                <div class="input-group-append">
                    <span class="input-group-text">
                        @if(str_contains($suffix, ' ') || str_starts_with($suffix, 'fa'))
                            <i class="{{ $suffix }}"></i>
                        @else
                            {{ $suffix }}
                        @endif
                    </span>
                </div>
            @endif
        </div>

    @else
        <input
            type="number"
            wire:model="value"
            id="{{ $id }}"
            name="{{ $fieldName }}"
            value="{{ $fieldValue }}"
            placeholder="{{ $placeholder }}"
            class="form-control @if($size) form-control-{{ $size }} @endif @error($fieldName) is-invalid @enderror"
            @if(!is_null($min))  min="{{ $min }}"   @endif
            @if(!is_null($max))  max="{{ $max }}"   @endif
            @if(!is_null($step)) step="{{ $step }}" @endif
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
