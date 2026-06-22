<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\SettingTrait;

use App\Models\Position;
use App\Http\Requests\PositionAddRequest;
use App\Http\Requests\PositionEditRequest;

class PositionController extends Controller
{
    use SettingTrait;

    public function index(Request $request) {
        $search = trim($request->get('search'));

        $positions = Position::orderBy('created_at', 'DESC')
            ->when(!empty($search), function($query) use($search) {
                $query->where('position', 'LIKE', '%'.$search.'%');
            })
            ->paginate($this->getDataPerPage())
            ->appends(request()->query());

        return view('pages.positions.index')->with([
            'search' => $search,
            'positions' => $positions
        ]);
    }

    public function create() {
        return view('pages.positions.create');
    }

    public function store(PositionAddRequest $request) {
        $position = new Position([
            'position' => $request->position
        ]);
        $position->save();

        // logs
        activity('created')
            ->performedOn($position)
            ->log(':causer.name created a new position :subject.position');

        return redirect()->route('position.index')->with([
            'message_success' => __('adminlte::positions.position_create_success')
        ]);
    }

    public function show($id) {
        $position = Position::findOrFail(decrypt($id));

        return view('pages.positions.show')->with([
            'position' => $position
        ]);
    }

    public function edit($id) {
        $position = Position::findOrFail(decrypt($id));

        return view('pages.positions.edit')->with([
            'position' => $position
        ]);
    }

    public function update(PositionEditRequest $request, $id) {
        $position = Position::findOrFail(decrypt($id));

        $changes_arr['old'] = $position->getOriginal();

        $position->update([
            'position' => $request->position
        ]);

        $changes_arr['changes'] = $position->getChanges();

        // logs
        activity('updated')
            ->performedOn($position)
            ->withProperties($changes_arr)
            ->log(':causer.name has created a position :subject.position');

        return back()->with([
            'message_success' => __('adminlte::positions.position_update_success')
        ]);
    }
}
