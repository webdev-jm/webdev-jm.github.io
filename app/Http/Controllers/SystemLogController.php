<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;

use App\Http\Traits\SettingTrait;

class SystemLogController extends Controller
{
    use SettingTrait;

    public function index(Request $request) {
        $search = trim($request->get('search') ?? '');

        $activities = Activity::with('causer')
            ->where('causer_type', 'App\Models\User')
            ->when(!empty($search), function($query) use($search) {
                $query->where(function($qry) use($search) {
                    $qry->where('description', 'like', '%'.$search.'%')
                    ->orWhere('log_name', 'like', '%'.$search.'%')
                    ->orWhereHas('causer', function($qry1) use($search) {
                        $qry1->where('name', 'like', '%'.$search.'%');
                    });
                });
            })
            ->orderByDesc('created_at')
            ->paginate($this->getDataPerPage(), ['*'], 'activity-page')
            ->onEachSide(1);

            $updates = [];
            foreach($activities as $activity) {
                if($activity->log_name == 'updated') {
                    $old = $activity->properties['old'];
                    $changes = $activity->properties['changes'];
                    
                    // Check if the model exists in the array
                    $models = [
                        'user_id' => [
                            'class'     => \App\Models\User::class,
                            'column'    => 'name'
                        ]
                    ];
    
                    foreach($changes as $key => $update) {
                        if($key === 'updated_at') {
                            continue;
                        }
            
                        $old_val = $old[$key];
                        $new_val = $update;
            
                        if (isset($models[$key])) {
                            $class = $models[$key]['class'];
                            $column = $models[$key]['column'];
            
                            // Check if old value exists
                            $old_val = !empty($old_val)
                                ? $class::find($old_val)[$column] ?? '-'
                                : '-';
            
                            // Check if new value exists
                            $new_val = !empty($new_val)
                                ? $class::find($new_val)[$column] ?? '-'
                                : '-';
                        }
            
                        if($key === 'arr') {
                            if(is_array($old_val) && is_array($new_val)) {
                                $removed = array_diff($old_val, $new_val);
                                $added   = array_diff($new_val, $old_val);

                                // Highlight removed items in red
                                foreach ($old_val as &$val) {
                                    if (in_array($val, $removed)) {
                                        $val = '<span class="text-danger font-weight-bold">' . $val . '</span>';
                                    }
                                }

                                // Highlight added items in green
                                foreach ($new_val as &$val) {
                                    if (in_array($val, $added)) {
                                        $val = '<span class="text-success font-weight-bold">' . $val . '</span>';
                                    }
                                }

                                unset($val); // clean up reference
                            }

                            $updates[$activity->id][$key] = [
                                'old' => implode(', ', $old_val),
                                'new' => implode(', ', $new_val),
                            ];
                        } else {
                            $updates[$activity->id][$key] = [
                                'old' => $old_val,
                                'new' => $new_val
                            ];
                        }
                    }
                }
            }

        return view('pages.system-logs.index')->with([
            'activities' => $activities,
            'updates' => $updates,
            'search' => $search
        ]);
    }
}
