<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SYSTEM SETTINGS</h3>
        </div>
        <div class="card-body">
        
            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        {{ html()->label('DATA PER PAGE', 'data_per_page')->class(['mb-0']) }}
                        {{ html()->number('data_per_page', $data_per_page)->class(['form-control', 'form-control-sm', 'is-invalid' => $errors->has('data_per_page')])->placeholder('Data per page')->attribute('wire:model', 'data_per_page') }}
                        <small class="text-center">{{$errors->first('data_per_page')}}</small>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            {{ html()->checkbox('email_sending', $email_sending, 1)->class('custom-control-input')->attribute('wire:model', 'email_sending') }}
                            {{ html()->label('Email sending', 'email_sending')->class('custom-control-label') }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary btn-sm" wire:click.prevent="saveSetting">
                <i class="fa fa-save"></i>
                SAVE SETTINGS
            </button>
        </div>
    </div>
</div>
