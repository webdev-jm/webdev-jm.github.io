@extends('layouts.app')

@section('subtitle', __('adminlte::tickets.ticket_list'))
@section('content_header_title', __('adminlte::tickets.tickets'))
@section('content_header_subtitle', __('adminlte::tickets.ticket_list'))

@section('content_body')
    <div class="card">
        <div class="card-header py-2">
            <div class="row">
                <div class="col-lg-6 align-middle">
                    <strong class="text-lg">
                        @can('ticket responder') {{ __('adminlte::tickets.all_tickets') }} @else {{ __('adminlte::tickets.my_tickets') }} @endcan
                    </strong>
                </div>
                <div class="col-lg-6 text-right">
                    @can('ticket access')
                        <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> {{ __('adminlte::tickets.new_ticket') }}
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead class="text-center bg-dark">
                        <tr>
                            <th class="text-left pl-3">#</th>
                            <th class="text-left">{{ __('adminlte::tickets.title') }}</th>
                            <th>{{ __('adminlte::tickets.category') }}</th>
                            <th>{{ __('adminlte::tickets.priority') }}</th>
                            <th>{{ __('adminlte::utilities.status') }}</th>
                            @can('ticket responder')
                                <th>{{ __('adminlte::tickets.submitted_by') }}</th>
                                <th>{{ __('adminlte::tickets.assigned_to') }}</th>
                            @endcan
                            <th>{{ __('adminlte::tickets.date') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td class="align-middle pl-3 text-nowrap">
                                    <code class="text-muted small">{{ $ticket->ticket_number }}</code>
                                </td>
                                <td class="align-middle">{{ $ticket->title }}</td>
                                <td class="align-middle text-center">{{ $ticket->category->label() }}</td>
                                <td class="align-middle text-center">
                                    <span class="badge {{ $ticket->priority->badgeClass() }}">
                                        {{ $ticket->priority->label() }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge {{ $ticket->status->badgeClass() }}">
                                        {{ $ticket->status->label() }}
                                    </span>
                                </td>
                                @can('ticket responder')
                                    <td class="align-middle text-center">{{ $ticket->user->name }}</td>
                                    <td class="align-middle text-center">{{ $ticket->assignee?->name ?? '—' }}</td>
                                @endcan
                                <td class="align-middle text-center">{{ $ticket->created_at->format('M d, Y') }}</td>
                                <td class="align-middle text-right pr-2">
                                    <a href="{{ route('tickets.show', encrypt($ticket->id)) }}" class="btn btn-info btn-xs">
                                        <i class="fa fa-list"></i> {{ __('adminlte::utilities.view') }}
                                    </a>
                                    @can('ticket responder')
                                        <a href="{{ route('tickets.edit', encrypt($ticket->id)) }}" class="btn btn-success btn-xs">
                                            <i class="fa fa-pen-alt"></i> {{ __('adminlte::utilities.edit') }}
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">{{ __('adminlte::tickets.no_tickets_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $tickets->links() }}
        </div>
    </div>
@stop
