@extends('layouts.app')

@section('title', __('adminlte::users.recycle_bin_users'))
@section('content_header_title', __('adminlte::utilities.recycle_bin'))
@section('content_header_subtitle', __('adminlte::users.deleted_users'))

@section('content_body')
<div class="row">
    <div class="col-12">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-trash-alt mr-2"></i> {{ __('adminlte::users.deleted_users') }}
                </h3>
                <div class="card-tools">
                    <a href="{{ route('user.index') }}" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-left"></i> {{ __('adminlte::users.back_to_users') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('adminlte::utilities.name') }}</th>
                            <th>{{ __('adminlte::utilities.email') }}</th>
                            <th>{{ __('adminlte::users.deleted_at') }}</th>
                            <th class="text-right">{{ __('adminlte::utilities.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <img src="{{ $user->adminlte_image() }}" alt="Profile" class="mr-2" style="width:30px;height:30px;object-fit:cover;border-radius:50%;">
                                    {{ $user->name }}
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->deleted_at->format('M d, Y h:i A') }}</td>
                                <td class="text-right">
                                    {{-- Restore Button --}}
                                    <form action="{{ route('user.restore', encrypt($user->id)) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="{{ __('adminlte::users.restore_user') }}" onclick="return confirm('{{ __('adminlte::users.confirm_restore', ['name' => addslashes($user->name)]) }}');">
                                            <i class="fas fa-trash-restore"></i> {{ __('adminlte::users.restore') }}
                                        </button>
                                    </form>

                                    {{-- Force Delete Button --}}
                                    <form action="{{ route('user.force_delete', encrypt($user->id)) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="{{ __('adminlte::users.permanently_delete') }}" onclick="return confirm('{{ __('adminlte::users.confirm_destroy', ['name' => addslashes($user->name)]) }}');">
                                            <i class="fas fa-times-circle"></i> {{ __('adminlte::users.destroy') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-success d-block"></i>
                                    {{ __('adminlte::utilities.trash_empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="card-footer d-flex justify-content-end">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection