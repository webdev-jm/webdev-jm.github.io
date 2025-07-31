@include('errors.base', [
    'code' => '404',
    'message' => 'The page you are looking for could not be found.',
    'back' => url()->previous()
])