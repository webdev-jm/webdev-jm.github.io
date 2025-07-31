<div>
    @if(!empty($msg))
        <div class="alert alert-success" wire:transition>
            {{$msg}}
        </div>
    @endif

    <form class="form-horizontal" wire:submit.prevent="saveSettings">
        <div class="form-group row">
            <label for="name" class="col-sm-3 col-form-label">{{__('adminlte::utilities.name')}}</label>
            <div class="col-sm-9">
                <input type="text" class="form-control form-control-sm{{$errors->has('name') ? ' is-invalid' : ''}}" id="name" placeholder="{{__('adminlte::utilities.name')}}" wire:model="name">
            </div>
        </div>
        <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">{{__('adminlte::utilities.email')}}</label>
            <div class="col-sm-9">
                <input type="email" class="form-control form-control-sm{{$errors->has('email') ? ' is-invalid' : ''}}" id="email" placeholder="{{__('adminlte::utilities.email')}}" wire:model="email">
            </div>
        </div>
        <div class="form-group row">
            <div class="offset-sm-3 col-sm-9" wire:dirty>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save mr-1"></i>
                    {{__('adminlte::utilities.save')}}
                </button>
            </div>
        </div>
        
    </form>
    
</div>
