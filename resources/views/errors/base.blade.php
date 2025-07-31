@extends('layouts.app')

@section('subtitle', 'Error')
@section('content_header_title', $code ?? 'Error')
@section('content_header_subtitle', $message ?? 'Oops! Something went wrong.')

@section('content_body')
    <div class="container py-5 text-center animate__animated animate__fadeInDown">
        {{-- Error Image --}}
        <img src="https://cdn-icons-png.flaticon.com/512/6134/6134065.png" 
             alt="Error Image" 
             class="img-fluid mb-4" 
             style="max-width: 200px;">

        {{-- Error Code --}}
        <h1 class="display-1 text-danger fw-bold">{{ $code ?? 'Error' }}</h1>

        {{-- Error Message --}}
        <p class="fs-4">{{ $message ?? 'An unexpected error occurred.' }}</p>

        {{-- Buttons --}}
        @if (!empty($back))
            <a href="{{ $back }}" class="btn btn-outline-secondary mt-4">
                <i class="bi bi-arrow-left-circle"></i> Go Back
            </a>
        @else
            <a href="{{ url('/') }}" class="btn btn-primary mt-4">
                <i class="bi bi-house-door"></i> Go Home
            </a>
        @endif
    </div>
@stop

@push('css')
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Animate.css --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
@endpush

@push('js')
@endpush