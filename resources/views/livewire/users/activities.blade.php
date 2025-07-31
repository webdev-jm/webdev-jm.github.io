<div>
    <div class="row">
        <div class="col-lg-4">
            <div class="form-group">
                <label for="search">{{__('adminlte::utilities.search')}}</label>
                <input type="text" class="form-control form-control-sm" id="search" placeholder="{{__('adminlte::utilities.search')}}" wire:model.live="search">
            </div>
        </div>

        <div class="col-12">
            <table class="table table-sm table-striped border">
                <thead>
                    <tr class="text-center">
                        <th class="align-middle">{{__('adminlte::systemlogs.log_name')}}</th>
                        <th class="align-middle">{{__('adminlte::systemlogs.log_description')}}</th>
                        <th class="align-middle">{{__('adminlte::systemlogs.changes')}}</th>
                        <th class="align-middle">{{__('adminlte::systemlogs.timestamp')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                        <tr>
                            <td>{{$activity->log_name}}</td>
                            <td>{{$activity->description}}</td>
                            <td class="p-1 text-xs">
                                @if($activity->log_name == 'updated' && !empty($updates[$activity->id]))
                                    <ul class="list-group">
                                        @foreach($updates[$activity->id] as $column => $data)
                                            <li class="list-group-item p-0">
                                                <p class="m-0 p-0">
                                                    <b>{{$column}}:</b> {!!$data['old']!!}
                                                </p>
                                                <p class="m-0 p-0 d-inline">
                                                    <b>to:</b> {!!$data['new']!!}
                                                </p>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td>{{date('F j, Y H:i:s a', strtotime($activity->created_at))}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-12">
            {{$activities->links()}}
        </div>
    </div>
</div>
