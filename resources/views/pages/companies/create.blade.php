@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('adminlte::companies.new_company'))
@section('content_header_title', __('adminlte::companies.companies'))
@section('content_header_subtitle', __('adminlte::companies.new_company'))

{{-- Content body: main page content --}}
@section('content_body')
    {{ html()->form('POST', route('company.store'))->open() }}

        <div class="card">
            <div class="card-header py-2">
                <div class="row">
                    <div class="col-lg-6 align-middle">
                        <strong class="text-lg">{{__('adminlte::companies.new_company')}}</strong>
                    </div>
                    <div class="col-lg-6 text-right">
                        <a href="{{route('company.index')}}" class="btn btn-secondary btn-xs">
                            <i class="fa fa-caret-left"></i>
                            {{__('adminlte::utilities.back')}}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">

                <div class="row">
                    
                    <div class="col-lg-4">
                        <div class="form-group">
                            {{ html()->label(__('adminlte::utilities.name'), 'name')->class(['mb-0']) }}
                            {{ 
                                html()->text('name', '')
                                ->class(['form-control', 'form-control-sm', 'is-invalid' => $errors->has('name')])
                                ->placeholder(__('adminlte::companies.company_name'))
                            }}
                            <small class="text-danger">{{$errors->first('name')}}</small>
                        </div>
                    </div>

                </div>

            </div>
            <div class="card-footer text-right">
                {{ html()->submit('<i class="fa fa-save"></i> '.__('adminlte::companies.save_company'))->class(['btn', 'btn-primary', 'btn-sm']) }}
            </div>
        </div>

    {{ html()->form()->close() }}
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