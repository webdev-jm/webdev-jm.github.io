<div>
    @if(!empty($msg))
        <div class="alert alert-success" wire:transition>
            {{$msg}}
        </div>
    @endif

    <form class="form-horizontal" wire:submit.prevent="submitForm">
        @if($type == 'profile')
            <div class="form-group row">
                <label for="current" class="col-sm-3 col-form-label">{{__('adminlte::profile.current_password')}}</label>
                <div class="col-sm-9">
                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current" placeholder="{{__('adminlte::profile.current_password')}}" wire:model="current_password" autocomplete="current_password">
                    @error('current_password')
                        <small class="text-danger">{{$message}}</small>
                    @enderror
                    @if(!empty($password_error))
                        <small class="text-danger">{{$password_error}}</small>
                    @endif
                </div>
            </div>
        @endif
        <div class="form-group row">
            <label for="new" class="col-sm-3 col-form-label">{{__('adminlte::profile.new_password')}}</label>
            <div class="col-sm-9">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="new" placeholder="{{__('adminlte::profile.new_password')}}" wire:model.lazy="password" autocomplete="new_password">
                @error('password')
                    <small class="text-danger">{{$message}}</small>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label for="confirm" class="col-sm-3 col-form-label">{{__('adminlte::profile.confirm_password')}}</label>
            <div class="col-sm-9">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="confirm" placeholder="{{__('adminlte::profile.confirm_password')}}" wire:model.lazy="password_confirmation" autocomplete="confirm_password">
                @error('password')
                    <small class="text-danger">{{$message}}</small>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <div class="offset-sm-3 col-sm-9">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <i class="fa fa-save mr-1"></i>
                    {{__('adminlte::utilities.save')}}
                </button>
            </div>
        </div>
    </form>
</div>
