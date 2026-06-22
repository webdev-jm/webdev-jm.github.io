@extends('adminlte::page')

{{-- Extend and customize the browser title --}}

@section('title')
    {{ config('adminlte.title') }}
    @hasSection('subtitle') | @yield('subtitle') @endif
@stop

@auth
    @section('content_top_nav_right')

        @if(config('adminlte.features.chat_message'))
            <livewire:chat-app/>
        @endif

        @if(config('adminlte.features.online_users'))
            <!-- Online Users — hidden on mobile (< md) -->
            <li class="nav-item">
                <a href="#" class="nav-link" id="btn-online-users">
                    <i class="fa fa-user"></i>
                    <span class="navbar-badge">
                        <i class="fa fa-circle text-success"></i>
                    </span>
                </a>
            </li>
        @endif

        <!-- language toggle — flag always visible, locale text hidden on xs -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                @php
                    $locale = app()->getLocale();
                    $flags = ['en' => 'us', 'ja' => 'jp', 'zh-CN' => 'cn'];
                @endphp
                <span class="fi fi-{{ $flags[$locale] ?? 'us' }}"></span><span class="d-none d-sm-inline ml-1">{{ strtoupper($locale) }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="langDropdown">
                <a class="dropdown-item" href="{{ route('lang.switch', 'en') }}">
                    <span class="fi fi-us shadow"></span> English
                </a>
                <a class="dropdown-item" href="{{ route('lang.switch', 'ja') }}">
                    <span class="fi fi-jp shadow"></span> Japanese
                </a>
                <a class="dropdown-item" href="{{ route('lang.switch', 'zh-CN') }}">
                    <span class="fi fi-cn shadow"></span> Chinese
                </a>
            </div>
        </li>

        @if(config('adminlte.features.tickets'))
            <!-- Tickets -->
            <livewire:tickets.navbar-icon />
        @endif

        @if(config('adminlte.features.skin_switcher'))
            <livewire:skin-switcher />
        @endif

        @if(config('adminlte.features.dark_mode'))
            {{-- Dark mode toggle --}}
            <livewire:darkmode-toggle />
        @endif

        @if(config('adminlte.features.notifications'))
            <!-- Notifications Dropdown Menu -->
            <livewire:notification/>
        @endif
    @endsection
@endauth

{{-- Extend and customize the page content header --}}

@section('content_header')
    @hasSection('content_header_title')
        <h1 class="text-muted pb-2">
            @yield('content_header_title')

            @hasSection('content_header_subtitle')
                <small class="text-dark">
                    <i class="fas fa-xs fa-angle-right text-muted"></i>
                    @yield('content_header_subtitle')
                </small>
            @endif
        </h1>
    @endif
@stop

{{-- Rename section content to content_body --}}

@section('content')
    {{-- IMPERSONATION BANNER --}}
    @if(session()->has('impersonate_original_id'))
        <div class="alert alert-warning text-center rounded-0 mb-2 shadow-sm" style="position: relative; z-index: 1000; border-left: none;">
            <i class="fas fa-user-secret mr-2"></i>
            You are currently impersonating <strong>{{ Auth::user()->name }}</strong>.
            Any actions you take will be logged under their account.
            <a href="{{ route('impersonate.leave') }}" class="btn btn-sm btn-dark ml-3 shadow-sm rounded-pill px-3">
                <i class="fas fa-sign-out-alt mr-1"></i> Leave Impersonation
            </a>
        </div>
    @endif

    {{-- Offline status banner --}}
    @if(config('laravelpwa.offline_sync_enabled', true))
    <div
        x-data="{
            isOnline: navigator.onLine,
            pending: 0,
            justSynced: false,
            init() {
                window.addEventListener('offline:status-changed', e => { this.isOnline = e.detail.online; });
                window.addEventListener('offline:queued', e => { this.pending = e.detail.count; });
                window.addEventListener('offline:synced', e => {
                    this.pending = 0;
                    this.justSynced = true;
                    setTimeout(() => { this.justSynced = false; }, 4000);
                });
                if (window.offlineQueue) {
                    window.offlineQueue.getPendingCount().then(n => { this.pending = n; });
                }
            }
        }"
        x-show="!isOnline || pending > 0 || justSynced"
        x-cloak
        :class="{
            'bg-danger text-white': !isOnline,
            'bg-warning': isOnline && pending > 0,
            'bg-success text-white': justSynced
        }"
        class="text-center py-2 small"
        style="position: sticky; top: 0; z-index: 1050;"
    >
        <template x-if="!isOnline">
            <span><i class="fas fa-wifi mr-1"></i> You are offline — actions are being saved locally.</span>
        </template>
        <template x-if="isOnline && pending > 0">
            <span><i class="fas fa-sync-alt fa-spin mr-1"></i> Syncing <strong x-text="pending"></strong> queued action(s)…</span>
        </template>
        <template x-if="justSynced">
            <span><i class="fas fa-check-circle mr-1"></i> All actions synced successfully.</span>
        </template>
    </div>
    @endif

    @yield('content_body')

    <!-- DELETE MODAL -->
    <div class="modal fade" id="modal-delete">
        <div class="modal-dialog">
            <livewire:delete-model/>
        </div>
    </div>

    <div class="modal fade" id="online-users-modal" aria-hidden="true">
        <div class="modal-dialog">
            <livewire:online-users/>
        </div>
    </div>
@stop

{{-- Create a common footer --}}

@section('footer')
    <div class="float-right text-dark">
        Version: {{ config('app.version', '1.0.0') }}
    </div>

    <strong>
        <a href="{{ config('app.company_url', '#') }}"  class="text-dark">
            {{ config('app.company_name', 'My company') }}
        </a>
    </strong>
@stop

{{-- Setup Custom Preloader Content --}}

@section('preloader')
    <i class="fas fa-atom fa-spin fa-10x text-primary"></i>
    <h3 class="mt-3 text-secondary">Please wait...</h3>
@stop

{{-- plugins --}}
@section('iCheckBoostrap', true)
@section('Sweetalert2', true)
@section('Select2', true)

{{-- Add common Javascript/Jquery code --}}

@push('js')
<script>window.offlineSyncEnabled = @json(config('laravelpwa.offline_sync_enabled', true));</script>
@vite(['resources/js/app.js'])
<script>
    $(function() {
        // Dark mode toggle
        $('#darkModeToggle').on('click', function(e) {
            e.preventDefault();
            $('body').toggleClass('dark-mode');
            $(this).find('i').toggleClass('fa-moon').toggleClass('fa-sun');

            $('body').find('.main-header')
                .toggleClass('navbar-dark navbar-light')
                .toggleClass('navbar-dark navbar-dark', !$('body').find('.main-header').hasClass('navbar-dark navbar-dark'));
        });

        $('body').on('click','#btn-online-users', function(e) {
            e.preventDefault();
            $('#online-users-modal').modal('show');
        });
    });


    document.addEventListener('toast-message', event => {

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });

        Toast.fire({
            icon: event.detail[0].type,
            title: event.detail[0].message
        });
    });

</script>
@endpush

{{-- Add common CSS customizations --}}

@push('css')
@laravelPWA
{{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7/css/flag-icons.min.css"/> --}}
@auth
    @php $userSkin = auth()->user()->skin ?? 'default'; @endphp
    @if($userSkin === 'neumorphic')
        <link rel="stylesheet" href="{{ asset('/css/neumorphic.css') }}"/>
    @elseif($userSkin === 'glass')
        <link rel="stylesheet" href="{{ asset('/css/custom.css') }}"/>
    @elseif($userSkin === 'claymorphic')
        <link rel="stylesheet" href="{{ asset('/css/claymorphic.css') }}"/>
    @endif
@else
    @if(config('adminlte.features.neumorphic_skin'))
        <link rel="stylesheet" href="{{ asset('/css/neumorphic.css') }}"/>
    @elseif(config('adminlte.features.glass_skin'))
        <link rel="stylesheet" href="{{ asset('/css/custom.css') }}"/>
    @elseif(config('adminlte.features.claymorphic_skin'))
        <link rel="stylesheet" href="{{ asset('/css/claymorphic.css') }}"/>
    @endif
@endauth

<style type="text/css">
    img {
        display: inline;
    }
</style>
@endpush
