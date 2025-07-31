@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('adminlte::org-structures.org_structure_list'))
@section('content_header_title', __('adminlte::org-structures.org_structures'))
@section('content_header_subtitle', __('adminlte::org-structures.org_structure_list'))

{{-- Content body: main page content --}}
@section('content_body')
@stop

{{-- Push extra CSS --}}
@push('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@endpush

{{-- Push extra scripts --}}
@push('js')
    <script>
        $(function() {
        });
    </script>
@endpush