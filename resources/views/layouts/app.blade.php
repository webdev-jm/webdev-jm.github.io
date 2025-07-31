@extends('adminlte::page')

{{-- Extend and customize the browser title --}}

@section('title')
    {{ config('adminlte.title') }}
    @hasSection('subtitle') | @yield('subtitle') @endif
@stop

@auth
    @section('content_top_nav_right')

        <!-- Online Users -->
        <li class="nav-item">
            <a href="#" class="nav-link" id="btn-online-users">
                <i class="fa fa-user"></i>
                <span class="navbar-badge">
                    <i class="fa fa-circle text-success"></i>
                </span>
            </a>
        </li>

        <!-- language toggle -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                @php
                    $locale = app()->getLocale();
                    $flags = ['en' => 'us', 'ja' => 'jp', 'zh-CN' => 'cn'];
                @endphp
                <span class="fi fi-{{ $flags[$locale] ?? 'us' }}"></span> {{ strtoupper($locale) }}
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

        {{-- Dark mode toggle --}}
        <livewire:darkmode-toggle />

        <!-- Notifications Dropdown Menu -->
        <livewire:notification/>
    @endsection
@endauth

{{-- Extend and customize the page content header --}}

@section('content_header')
    @hasSection('content_header_title')
        <h1 class="text-muted">
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

{{-- Add common Javascript/Jquery code --}}

@push('js')
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

    
</script>
@endpush

{{-- Add common CSS customizations --}}

@push('css')
@laravelPWA
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7/css/flag-icons.min.css"/>
<style type="text/css">


</style>
@endpush