
    <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="fas fa-ticket-alt"></i>
            @if($count > 0)
                <span class="badge badge-warning navbar-badge">{{ $count }}</span>
            @endif
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <span class="dropdown-item dropdown-header">
                {{ trans_choice('adminlte::tickets.open_tickets', $count, ['count' => $count]) }}
            </span>
            <div class="dropdown-divider"></div>

            @forelse($recentTickets as $ticket)
                <a href="{{ route('tickets.show', encrypt($ticket->id)) }}" class="dropdown-item">
                    <div class="media">
                        <div class="media-body">
                            <h3 class="dropdown-item-title font-weight-bold text-truncate" style="max-width:220px;">
                                {{ $ticket->title }}
                            </h3>
                            <p class="text-sm mb-0">
                                <span class="badge {{ $ticket->status->badgeClass() }}">{{ $ticket->status->label() }}</span>
                                <span class="badge {{ $ticket->priority->badgeClass() }} ml-1">{{ $ticket->priority->label() }}</span>
                            </p>
                            <p class="text-sm text-muted mb-0">
                                <i class="far fa-clock mr-1"></i>{{ $ticket->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </a>
                <div class="dropdown-divider"></div>
            @empty
                <span class="dropdown-item text-muted">{{ __('adminlte::tickets.no_tickets_yet') }}</span>
                <div class="dropdown-divider"></div>
            @endforelse

            <div class="d-flex">
                <a href="{{ route('tickets.index') }}" class="dropdown-item dropdown-footer text-center" style="flex:1;">
                    {{ __('adminlte::tickets.view_all') }}
                </a>
                @can('ticket access')
                    <a href="{{ route('tickets.create') }}" class="dropdown-item dropdown-footer text-center border-left" style="flex:1;">
                        {{ __('adminlte::tickets.new_ticket') }}
                    </a>
                @endcan
            </div>
        </div>
    </li>
