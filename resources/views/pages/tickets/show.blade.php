@extends('layouts.app')

@section('subtitle', __('adminlte::tickets.view_ticket'))
@section('content_header_title', __('adminlte::tickets.tickets'))
@section('content_header_subtitle', __('adminlte::tickets.view_ticket'))

@section('content_body')
    <div class="row">
        {{-- Ticket detail --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header py-2 d-flexalign-items-center">
                    <code class="text-muted small mr-2">{{ $ticket->ticket_number }}</code>
                    <strong class="text-lg">{{ $ticket->title }}</strong>
                    @can('ticket responder')
                    <div class="card-tools">
                        <a href="{{ route('tickets.edit', encrypt($ticket->id)) }}" class="btn btn-success btn-sm float-right">
                            <i class="fa fa-pen-alt"></i> {{ __('adminlte::utilities.edit') }}
                        </a>
                    </div>
                    @endcan
                </div>
                <div class="card-body">
                    <p class="text-muted" style="white-space: pre-wrap;">{{ $ticket->description }}</p>
                </div>

                {{-- Attachments --}}
                @if($ticket->attachments->isNotEmpty())
                <div class="card-footer p-0">
                    <div class="px-3 py-2">
                        <strong class="small">{{ __('adminlte::tickets.attachments') }} ({{ $ticket->attachments->count() }})</strong>
                        <div class="mt-2">
                            @foreach($ticket->attachments as $attachment)
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fa fa-paperclip text-muted mr-2"></i>
                                    <a href="{{ Storage::url($attachment->path) }}" target="_blank" class="flex-grow-1 text-sm">
                                        {{ $attachment->original_name }}
                                    </a>
                                    <small class="text-muted mr-2">{{ number_format($attachment->size / 1024, 1) }} KB</small>
                                    @if(auth()->id() === $attachment->user_id || auth()->user()->can('ticket responder'))
                                        <form method="POST" action="{{ route('tickets.attachment.destroy', [encrypt($ticket->id), encrypt($attachment->id)]) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger"
                                                onclick="return confirm('{{ __('adminlte::tickets.delete_attachment_confirm') }}')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Attach file --}}
                @if(auth()->id() === $ticket->user_id || auth()->user()->can('ticket responder'))
                <div class="card-footer py-2">
                    <form method="POST" action="{{ route('tickets.attachment.store', encrypt($ticket->id)) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group input-group-sm">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="attachment" name="attachment">
                                <label class="custom-file-label" for="attachment">{{ __('adminlte::tickets.choose_file') }}</label>
                            </div>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fa fa-upload mr-1"></i>{{ __('adminlte::tickets.attach_file') }}
                                </button>
                            </div>
                        </div>
                        @error('attachment')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </form>
                </div>
                @endif

                {{-- Comments --}}
                <div class="card-footer p-0">
                    <livewire:tickets.ticket-thread :ticket="$ticket" />
                </div>
            </div>
        </div>

        {{-- Sidebar meta --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header py-2">
                    <strong>{{ __('adminlte::tickets.ticket_info') }}</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th class="pl-3">{{ __('adminlte::tickets.ticket_number') }}</th>
                            <td><code>{{ $ticket->ticket_number }}</code></td>
                        </tr>
                        <tr>
                            <th class="pl-3">{{ __('adminlte::utilities.status') }}</th>
                            <td>
                                <span class="badge {{ $ticket->status->badgeClass() }}">
                                    {{ $ticket->status->label() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="pl-3">{{ __('adminlte::tickets.priority') }}</th>
                            <td>
                                <span class="badge {{ $ticket->priority->badgeClass() }}">
                                    {{ $ticket->priority->label() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="pl-3">{{ __('adminlte::tickets.category') }}</th>
                            <td>{{ $ticket->category->label() }}</td>
                        </tr>
                        <tr>
                            <th class="pl-3">{{ __('adminlte::tickets.submitted_by') }}</th>
                            <td>{{ $ticket->user->name }}</td>
                        </tr>
                        <tr>
                            <th class="pl-3">{{ __('adminlte::tickets.assigned_to') }}</th>
                            <td>
                                @can('ticket responder')
                                    <form method="POST" action="{{ route('tickets.assignee.update', encrypt($ticket->id)) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select name="assigned_to" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="">{{ __('adminlte::tickets.unassigned') }}</option>
                                            @foreach($responders as $user)
                                                <option value="{{ $user->id }}" {{ $ticket->assigned_to == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    {{ $ticket->assignee?->name ?? '—' }}
                                @endcan
                            </td>
                        </tr>
                        <tr>
                            <th class="pl-3">{{ __('adminlte::tickets.submitted') }}</th>
                            <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <th class="pl-3">{{ __('adminlte::tickets.last_updated') }}</th>
                            <td>{{ $ticket->updated_at->diffForHumans() }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Status actions: open/in_progress/resolved — responder only --}}
            @can('ticket responder')
            <div class="card">
                <div class="card-header py-2">
                    <strong>{{ __('adminlte::tickets.update_status') }}</strong>
                </div>
                <div class="card-body">
                    @foreach (\App\Enums\TicketStatus::cases() as $status)
                        @if ($status->value !== 'closed' && $ticket->status !== $status)
                            <form method="POST" action="{{ route('tickets.status.update', encrypt($ticket->id)) }}" class="mb-2">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $status->value }}">
                                <button type="submit" class="btn btn-sm btn-block {{ $status->badgeClass() === 'badge-primary' ? 'btn-primary' : ($status->badgeClass() === 'badge-warning' ? 'btn-warning' : 'btn-success') }}">
                                    {{ __('adminlte::tickets.mark_as', ['status' => $status->label()]) }}
                                </button>
                            </form>
                        @endif
                    @endforeach
                </div>
            </div>
            @endcan

            {{-- Close ticket — creator or responder --}}
            @if($ticket->status->value !== 'closed' && (auth()->id() === $ticket->user_id || auth()->user()->can('ticket responder')))
            <form method="POST" action="{{ route('tickets.status.update', encrypt($ticket->id)) }}" class="mb-2">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="closed">
                <button type="submit" class="btn btn-sm btn-block btn-secondary">
                    {{ __('adminlte::tickets.mark_as', ['status' => \App\Enums\TicketStatus::Closed->label()]) }}
                </button>
            </form>
            @endif

            <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-sm btn-block">
                <i class="fa fa-arrow-left"></i> {{ __('adminlte::tickets.back_to_tickets') }}
            </a>
        </div>
    </div>
@stop

@push('js')
<script>
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            var fileName = e.target.files[0]?.name ?? 'Choose file';
            e.target.nextElementSibling.innerText = fileName;
        });
    });
</script>
@endpush
