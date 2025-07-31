<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Notifications\TestNotification;

class NotificationController extends Controller
{
    public function index(Request $request) {
        $search = trim($request->get('search'));

        $notifications = auth()->user()->notifications()
            ->orderBy('created_at', 'DESC')
            ->when(!empty($search), function($query) use($search) {
                $query->where('data', 'LIKE', '%'.$search.'%');
            })
            ->paginate(10)
            ->onEachSide(1);

        return view('notifications')->with([
            'notifications' => $notifications,
            'search' => $search
        ]);
    }

    public function testNotification() {
        auth()->user()->notify(new TestNotification());

        return back();
    }
}
