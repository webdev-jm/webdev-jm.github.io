@extends('layouts.app')

@section('subtitle', __('adminlte::tickets.edit_ticket'))
@section('content_header_title', __('adminlte::tickets.tickets'))
@section('content_header_subtitle', __('adminlte::tickets.edit_ticket'))

@section('content_body')
    <livewire:tickets.edit-form :ticket="$ticket" />
@stop
