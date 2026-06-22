@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@php
    if (config('adminlte.features.neumorphic_skin') || config('adminlte.features.glass_skin')) {
        config([
            'adminlte.classes_auth_card' => '',
            'adminlte.classes_auth_body' => '',
            'adminlte.classes_auth_icon' => '',
        ]);
    }
@endphp

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@push('css')
    @if(config('adminlte.features.neumorphic_skin'))
        <link rel="stylesheet" href="{{ asset('css/neumorphic.css') }}">
    @elseif(config('adminlte.features.glass_skin'))
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    @endif
@endpush

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )
@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $register_url = $register_url ? route($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $register_url = $register_url ? url($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('auth_header', __('adminlte::adminlte.login_message'))

@section('auth_body')
    <div class="login-welcome">
        <h2>Welcome back</h2>
        <p>Sign in to continue to your account</p>
    </div>

    <form action="{{ $login_url }}" method="post">
        @csrf

        {{-- Email field --}}
        <div class="login-field-group">
            <label>Email address</label>
            <div class="input-group">
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}" autofocus>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-envelope {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        {{-- Password field --}}
        <div class="login-field-group">
            <label>Password</label>
            <div class="input-group">
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                       placeholder="{{ __('adminlte::adminlte.password') }}">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        {{-- Server type --}}
        <div class="login-field-group">
            <label>Server</label>
            <div class="input-group">
                <select name="type" class="form-control @error('type') is-invalid @enderror">
                    <option value="live" selected>LIVE SERVER</option>
                    <option value="test">TEST SERVER</option>
                </select>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-database {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
                @error('type')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        {{-- Remember me + Forgot password --}}
        <div class="login-actions-row">
            <div class="icheck-primary" title="{{ __('adminlte::adminlte.remember_me_hint') }}">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">
                    {{ __('adminlte::adminlte.remember_me') }}
                </label>
            </div>
            @if ($password_reset_url)
                <a href="{{ $password_reset_url }}" class="login-forgot-link">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit" class="btn login-submit-btn">
            <span class="fas fa-sign-in-alt me-2"></span>
            {{ __('adminlte::adminlte.sign_in') }}
        </button>

    </form>
@stop

@section('auth_footer')
    @if (config('adminlte.gmail_login', false))
        <div class="login-divider"><span>or</span></div>
        <a href="{{ route('google.login') }}" class="btn login-google-btn">
            <i class="fab fa-google"></i>
            Continue with Google
        </a>
    @endif
@stop
