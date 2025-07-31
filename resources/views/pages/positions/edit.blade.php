@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('adminlte::positions.update_position'))
@section('content_header_title', __('adminlte::positions.positions'))
@section('content_header_subtitle', __('adminlte::positions.update_position'))

{{-- Content body: main page content --}}
@section('content_body')
    {{ html()->form('POST', route('position.update', encrypt($position->id)))->open() }}

        <div class="card">
            <div class="card-header py-2">
                <div class="row">
                    <div class="col-lg-6 align-middle">
                        <strong class="text-lg">{{__('adminlte::positions.update_position')}}</strong>
                    </div>
                    <div class="col-lg-6 text-right">
                        <a href="{{route('position.index')}}" class="btn btn-secondary btn-xs">
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
                            {{ html()->label(__('adminlte::positions.position'), 'position')->class(['mb-0']) }}
                            {{ 
                                html()->text('position', $position->position)
                                ->class(['form-control', 'form-control-sm', 'is-invalid' => $errors->has('position')])
                                ->placeholder(__('adminlte::positions.position'))
                            }}
                            <small class="text-danger">{{$errors->first('position')}}</small>
                        </div>
                    </div>

                </div>

            </div>
            <div class="card-footer text-right">
                {{ html()->submit('<i class="fa fa-save"></i> '.__('adminlte::positions.update_position'))->class(['btn', 'btn-primary', 'btn-sm']) }}
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