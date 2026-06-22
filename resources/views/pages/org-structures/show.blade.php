@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('adminlte::org-structures.org_structure_details'))
@section('content_header_title', __('adminlte::org-structures.org_structures'))
@section('content_header_subtitle', __('adminlte::org-structures.org_structure_details'))

{{-- Content body: main page content --}}
@section('content_body')
<div class="row">
    {{-- Header / Details --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header py-2">
                <div class="row">
                    <div class="col-lg-6 align-middle">
                        <strong class="text-lg">{{ $org_structure->type }}</strong>
                    </div>
                    <div class="col-lg-6 text-right">
                        <a href="{{ route('org-structure.index') }}" class="btn btn-secondary btn-xs">
                            <i class="fa fa-caret-left"></i>
                            {{ __('adminlte::utilities.back') }}
                        </a>
                        @can('position edit')
                            <a href="{{ route('org-structure.edit', encrypt($org_structure->id)) }}" class="btn btn-success btn-xs">
                                <i class="fa fa-pen-alt"></i>
                                {{ __('adminlte::utilities.edit') }}
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Org Chart --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header py-2">
                <strong class="text-lg">{{ __('adminlte::org-structures.org_structure_details') }}</strong>
            </div>
            <div class="card-body">
                <div id="chart-container"></div>
            </div>
        </div>
    </div>

    {{-- Maintenance --}}
    <div class="col-12">
        <livewire:org-structures.maintenance :org_structure="$org_structure" />
    </div>
</div>
@stop

{{-- Push extra CSS --}}
@push('css')
    <link rel="stylesheet" href="{{ asset('/vendor/orgchart/src/css/jquery.orgchart.css') }}">
    <style>
        #chart-container {
            position: relative;
            height: 420px;
            border: 1px solid #aaa;
            margin: 0.5rem;
            overflow: auto;
            text-align: center;
        }
        #chart-container .title,
        #chart-container .content {
            width: 100% !important;
            min-width: 160px;
        }
    </style>
@endpush

{{-- Push extra scripts --}}
@push('js')
    <script src="{{ asset('/vendor/orgchart/src/js/jquery.orgchart.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        var orgChartOptions = {
            depth: 2,
            nodeTitle: 'name',
            nodeContent: 'title',
            exportButton: true,
            exportFileExtension: 'pdf',
            exportFilename: 'OrgChart-{{ $org_structure->type }}',
            pan: true,
            zoom: true
        };

        function renderChart(data) {
            $('#chart-container').empty().orgchart($.extend({ data: data }, orgChartOptions));
        }

        $(function() {
            renderChart(@php echo json_encode($chart_data); @endphp);
        });

        document.addEventListener('livewire:init', () => {
            Livewire.on('refresh-org-chart', ({ new_data }) => renderChart(new_data));
        });
    </script>
@endpush
