<div>
    <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-bell"></i>
            @if($count > 0)
                <span class="badge badge-danger navbar-badge">{{$count}}</span>
            @endif
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <span class="dropdown-item dropdown-header">{{$count}} Notification{{$count > 1 ? 's' : ''}}</span>
            <div class="dropdown-divider"></div>
            @if(!empty($notifications))
                @foreach($notifications as $notification)
                    <a href="#" class="dropdown-item" wire:click.prevent="readNotif('{{$notification->id}}')">
                        
                        <div class="media">
                            <div class="media-body">
                                <h3 class="dropdown-item-title font-weight-bold">
                                    <i class="fas fa-envelope mr-2"></i>
                                    {{$notification->data['title']}}
                                </h3>
                                <p class="text-sm">{{$notification->data['message']}}</p>
                                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> {{$notification->created_at->diffForHumans()}}</p>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                @endforeach
            @endif

            <a href="{{route('notifications')}}" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
    </li>
</div>
