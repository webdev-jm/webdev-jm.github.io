<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{__('adminlte::roles.role_users')}}</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-striped table-hover bg-white mb-0">
                <thead class="tex-center bg-dark">
                    <tr class="text-center">
                        <th>{{__('adminlte::utilities.name')}}</th>
                        <th>{{__('adminlte::utilities.email')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="align-middle text-center">
                                {{$user->name}}
                            </td>
                            <td class="align-middle text-center">
                                {{$user->email}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$users->links()}}
        </div>
    </div>
</div>
