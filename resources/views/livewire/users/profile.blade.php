<div>
    <div class="row">
        <div class="col-lg-12">
            <div class="text-left">
                @if(!empty($profile_pic))
                    <img class="img-fluid profile-img"
                        src="{{$profile_pic->temporaryUrl()}}"
                        alt="User profile picture"
                        style="object-fit:cover;border-radius:50%;aspect-ratio:1/1;">
                @else
                    <img class="img-fluid profile-img"
                        src="{{$user->adminlte_image()}}"
                        alt="User profile picture"
                        style="object-fit:cover;border-radius:50%;aspect-ratio:1/1;">
                @endif
            </div>
        </div>
        <div class="col-lg-4">
            <div class="form-group">
                <label for="profile_pic">{{__('adminlte::profile.profile_picture')}}</label>
                <input type="file" class="form-control form-control-sm" id="profile_pic" wire:model.live="profile_pic">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <button class="btn btn-primary" wire:click.prevent="changeProfile">
                <i class="fa fa-save mr-1"></i>
                {{__('adminlte::utilities.save')}}
            </button>
        </div>
    </div>
</div>
