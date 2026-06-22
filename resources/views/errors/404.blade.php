@include('errors.base', [
    'code' => '404',
    'message' => __('adminlte::errors.404'),
    'back' => url()->previous()
])