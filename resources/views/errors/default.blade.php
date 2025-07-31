@include('errors.base', [
    'code' => $code ?? 'Error',
    'message' => $message ?? 'An unexpected error has occurred.'
])