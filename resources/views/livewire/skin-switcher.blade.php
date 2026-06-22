
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="skinDropdown" role="button"
           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
           title="Switch Skin">
            <i class="fas fa-palette"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="skinDropdown">
            @foreach($skins as $key => $skin)
                <a class="dropdown-item d-flex align-items-center gap-2 {{ auth()->user()->skin === $key ? 'active font-weight-bold' : '' }}"
                   href="#"
                   wire:click.prevent="switchSkin('{{ $key }}')">
                    <i class="{{ $skin['icon'] }} fa-fw mr-2"></i>
                    {{ $skin['label'] }}
                    @if(auth()->user()->skin === $key)
                        <i class="fas fa-check ml-auto text-success"></i>
                    @endif
                </a>
            @endforeach
        </div>
    </li>
