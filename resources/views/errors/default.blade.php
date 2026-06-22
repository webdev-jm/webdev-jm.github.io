@include('errors.base', [
    'code' => $code ?? __('adminlte::errors.error'),
    'message' => $message ?? __('adminlte::errors.unexpected_error'),
])