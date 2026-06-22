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
    public $required    = false;
    public $disabled    = false;
    public $multiple    = false;
    public $helpText    = '';
    public $placeholder = '-- Select an option --';
    public $size        = '';
    public $searchable  = false;
    public $searchMin   = 0;
    public $allowClear  = false;
    public $inModal     = false;
    public $optionValue = 'id';
    public $optionLabel = 'name';
    public $encryptModel = true;

    /**
     * Options format (four accepted shapes):
     *
     * 1. Flat list:      ['active', 'inactive']
     * 2. Key-value:      [['value'=>1,'label'=>'Admin'], ...]
     * 3. Eloquent/obj:   User::all()  — uses $optionValue / $optionLabel
     * 4. Option groups:  [['group'=>'Fruits','options'=>[...]], ...]
     */
    public $options = [];

    public function encryptVal(mixed $val): string
    {
        return encrypt((string) $val);
    }

    /**
     * Resolve the comparison value for @selected checks.
     *
     * Priority (highest → lowest):
     *   1. old($fieldName)   — regular form redirect after validation failure
     *   2. $this->value      — Livewire wire:model binding
     *
     * In Livewire, old() is always null (no redirect), so $this->value wins.
     * In a regular form, old() holds what was submitted — the encrypted string
     * for model options, or the plain value for arrays/scalars — so it wins.
     *
     * After resolving the base, we attempt to decrypt it (both paths may hold
     * an encrypted payload when encryptModel is true).
     */
    public function rawValue(): mixed
    {
        $fieldName = $this->name ?: $this->id;

        // old() returns null in Livewire (no redirect); non-null after a
        // regular-form redirect. Use it when available, fall back to $value.
        $base = old($fieldName) ?? $this->value;

        if (! $this->encryptModel) {
            return $base;
        }

        // Multi-select: $base may be an array of encrypted strings
        if (is_array($base)) {
            return array_map(function ($v) {
                try {
                    return decrypt((string) $v);
                } catch (\Throwable) {
                    return $v;   // already raw
                }
            }, $base);
        }

        try {
            return decrypt((string) $base);
        } catch (\Throwable) {
            return $base;        // already raw (initial render or plain value)
        }
    }

    /**
     * Unique HTML element id — plain on a normal page, SHA-256-derived
     * inside a modal to avoid id collisions across multiple instances.
     */
    public function elementId(): string
    {
        if (! $this->inModal) {
            return $this->id;
        }

        return 'sel_' . substr(hash('sha256', $this->id . '::' . $this->getId()), 0, 20);
    }
};
?>

@php
    $elId      = $this->elementId();
    $fieldName = $name ?: $id;
    $rawVal    = $this->rawValue();   // decrypted value for @selected checks
@endphp

<div
    class="form-group"
    x-data="{
        select2: null,
        elId: '{{ $elId }}',

        init() {
            @if($searchable)
                this.initSelect2();

                Livewire.hook('commit', ({ succeed }) => {
                    succeed(() => this.$nextTick(() => this.reinit()));
                });
            @endif
        },

        initSelect2() {
            const el = document.getElementById(this.elId);

            if (!el) {
                console.warn('[SelectField] Element not found: #' + this.elId);
                return;
            }
            if (typeof $ === 'undefined' || !$.fn.select2) {
                console.warn('[SelectField] jQuery / Select2 not loaded.');
                return;
            }

            const $modal         = $(el).closest('.modal');
            const dropdownParent = $modal.length ? $modal : $(document.body);

            this.select2 = $(el).select2({
                width          : '100%',
                placeholder    : '{{ addslashes($placeholder) }}',
                allowClear     : {{ $allowClear ? 'true' : 'false' }},
                minimumResultsForSearch: {{ $searchMin }},
                disabled       : {{ $disabled ? 'true' : 'false' }},
                dropdownParent : dropdownParent,
            });

            // Bridge Select2 → Livewire (native events are swallowed by Select2)
            $(el).on('change.select2', () => $wire.set('value', $(el).val()));
        },

        reinit() { this.destroy(); this.initSelect2(); },

        destroy() {
            if (this.select2) {
                try {
                    const el = document.getElementById(this.elId);
                    if (el) $(el).off('change.select2').select2('destroy');
                } catch (e) {}
                this.select2 = null;
            }
        }
    }"
    x-init="init()"
    @alpine:destroy="destroy()"
>

    @if($label)
        <label for="{{ $elId }}">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    <div @if($searchable) wire:ignore @endif>
        <select
            id="{{ $elId }}"
            name="{{ $fieldName }}{{ $multiple ? '[]' : '' }}"
            class="form-control @if($size) form-control-{{ $size }} @endif @error($fieldName) is-invalid @enderror"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($multiple) multiple @endif
        >
            @if($placeholder && !$multiple)
                <option value="">{{ $placeholder }}</option>
            @endif

            @foreach($options as $option)

                {{-- ── 1. Option group ──────────────────────────────── --}}
                @if(is_array($option) && isset($option['group']))
                    <optgroup label="{{ $option['group'] }}">
                        @foreach($option['options'] as $child)
                            <option
                                value="{{ $child['value'] ?? $child }}"
                                @selected(
                                    ($multiple && in_array($child['value'] ?? $child, (array) $rawVal)) ||
                                    (!$multiple && ($child['value'] ?? $child) == $rawVal)
                                )
                            >{{ $child['label'] ?? $child }}</option>
                        @endforeach
                    </optgroup>

                {{-- ── 2. Eloquent model / stdClass object ──────────── --}}
                {{--
                    Raw model ID is encrypted before writing to HTML.
                    @selected compares raw IDs on both sides:
                      $rawOptVal  = plain ID from the model
                      $rawVal     = decrypted from wire:model OR old()
                --}}
                @elseif(is_object($option))
                    @php
                        $rawOptVal      = $option->{$optionValue};
                        $renderedOptVal = $encryptModel
                            ? $this->encryptVal($rawOptVal)
                            : $rawOptVal;
                    @endphp
                    <option
                        value="{{ $renderedOptVal }}"
                        @selected(
                            ($multiple && in_array($rawOptVal, (array) $rawVal)) ||
                            (!$multiple && $rawOptVal == $rawVal)
                        )
                    >{{ $option->{$optionLabel} }}</option>

                {{-- ── 3. Key-value array ───────────────────────────── --}}
                @elseif(is_array($option))
                    <option
                        value="{{ $option['value'] }}"
                        @selected(
                            ($multiple && in_array($option['value'], (array) $rawVal)) ||
                            (!$multiple && $option['value'] == $rawVal)
                        )
                        @if(!empty($option['disabled'])) disabled @endif
                    >{{ $option['label'] }}</option>

                {{-- ── 4. Flat scalar ──────────────────────────────── --}}
                @else
                    <option
                        value="{{ $option }}"
                        @selected(
                            ($multiple && in_array($option, (array) $rawVal)) ||
                            (!$multiple && $option == $rawVal)
                        )
                    >{{ $option }}</option>
                @endif

            @endforeach
        </select>
    </div>

    @error($fieldName)
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror

    @if($helpText)
        <small class="form-text text-muted">{{ $helpText }}</small>
    @endif

</div>
