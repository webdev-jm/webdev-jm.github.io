@extends('layouts.app')

@section('title', __('adminlte::utilities.system_recycle_bin'))
@section('content_header_title', __('adminlte::utilities.system_settings'))
@section('content_header_subtitle', __('adminlte::utilities.recycle_bin'))

@section('content_body')
    <livewire:trash-bin />
@endsection