@extends('layouts.app')

{{-- Customize layout sections --}}

@section('subtitle', __('adminlte::adminlte.welcome'))
@section('content_header_title', __('adminlte::adminlte.home'))
@section('content_header_subtitle', __('adminlte::adminlte.welcome'))

{{-- Content body: main page content --}}

@section('content_body')
    <p>{{ __('adminlte::adminlte.welcome_message') }}</p>

    @auth
        <a href="{{ url('/home') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ __('adminlte::adminlte.home') }}</a>
    @else
        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ __('adminlte::adminlte.log_in') }}</a>

        @if (Route::has('register'))
            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ __('adminlte::adminlte.register') }}</a>
        @endif
    @endauth
@stop

{{-- Push extra CSS --}}

@push('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@endpush

{{-- Push extra scripts --}}

@push('js')
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then((registrations) => {
            for (const registration of registrations) {
            registration.unregister().then((success) => {
                if (success) {
                console.log('Service worker unregistered');
                }
            });
            }
        });
    }
</script>
@endpush