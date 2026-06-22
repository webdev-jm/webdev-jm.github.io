@extends('adminlte::master')

@php
    $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home');

    if (config('adminlte.use_route_url', false)) {
        $dashboard_url = $dashboard_url ? route($dashboard_url) : '';
    } else {
        $dashboard_url = $dashboard_url ? url($dashboard_url) : '';
    }

    $bodyClasses = ($auth_type ?? 'login') . '-page';

    if (! empty(config('adminlte.layout_dark_mode', null))) {
        $bodyClasses .= ' dark-mode';
    }
@endphp

@section('adminlte_css')
    @stack('css')
    @yield('css')
    <style>
        .login-logo,
        .register-logo {
            text-align: center;
            margin-bottom: 1.5rem !important;
        }
        .login-logo a,
        .register-logo a {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 0.65rem;
            text-decoration: none !important;
        }
        .auth-logo-img-wrap {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--glass-bg-hover);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 2px solid var(--glass-border);
            box-shadow:
                0 8px 32px rgba(99, 102, 241, 0.22),
                inset 0 0 0 1px rgba(255,255,255,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: box-shadow 0.3s, transform 0.3s;
        }
        .login-logo a:hover .auth-logo-img-wrap,
        .register-logo a:hover .auth-logo-img-wrap {
            box-shadow:
                0 12px 40px rgba(99, 102, 241, 0.35),
                inset 0 0 0 1px rgba(255,255,255,0.6);
            transform: translateY(-2px);
        }
        .auth-logo-img-wrap img {
            width: 52px;
            height: 52px;
            object-fit: contain;
            border-radius: 50%;
            display: block;
        }
        .auth-logo-text {
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: none;
            line-height: 1;
        }
        body.dark-mode .auth-logo-img-wrap {
            box-shadow:
                0 8px 32px rgba(99, 102, 241, 0.3),
                inset 0 0 0 1px rgba(255,255,255,0.08);
        }
    </style>
@stop

@section('iCheckBoostrap', true)

@section('classes_body'){{ $bodyClasses }}@stop

@section('body')
    <div class="{{ $auth_type ?? 'login' }}-box">

        {{-- Logo --}}
        <div class="{{ $auth_type ?? 'login' }}-logo">
            <a href="{{ $dashboard_url }}">

                {{-- Logo Image --}}
                <div class="auth-logo-img-wrap">
                    @if (config('adminlte.auth_logo.enabled', false))
                        <img src="{{ asset(config('adminlte.auth_logo.img.path')) }}"
                             alt="{{ config('adminlte.auth_logo.img.alt') }}">
                    @else
                        <img src="{{ asset(config('adminlte.logo_img')) }}"
                             alt="{{ config('adminlte.logo_img_alt') }}">
                    @endif
                </div>

                {{-- Logo Label --}}
                <span class="auth-logo-text">{!! config('adminlte.logo', '<b>Admin</b>LTE') !!}</span>

            </a>
        </div>

        {{-- Card Box --}}
        <div class="card {{ config('adminlte.classes_auth_card', 'card-outline card-primary') }}">

            {{-- Card Header --}}
            @hasSection('auth_header')
                <div class="card-header {{ config('adminlte.classes_auth_header', '') }}">
                    <h3 class="card-title float-none text-center">
                        @yield('auth_header')
                    </h3>
                </div>
            @endif

            {{-- Card Body --}}
            <div class="card-body {{ $auth_type ?? 'login' }}-card-body {{ config('adminlte.classes_auth_body', '') }}">
                @yield('auth_body')
            </div>

            {{-- Card Footer --}}
            @hasSection('auth_footer')
                <div class="card-footer {{ config('adminlte.classes_auth_footer', '') }}">
                    @yield('auth_footer')
                </div>
            @endif

        </div>

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
