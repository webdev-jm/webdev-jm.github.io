<?php

namespace App\Http\Controllers;

use App\Http\Traits\SettingTrait;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    use SettingTrait;

    public function index(Request $request)
    {
        $search = trim($request->get('search') ?? '');

        $activities = Activity::with('causer')
            ->where('causer_type', 'App\Models\User')
            ->when(! empty($search), function ($query) use ($search) {
                $query->where(function ($qry) use ($search) {
                    $qry->where('description', 'like', '%'.$search.'%')
                        ->orWhere('log_name', 'like', '%'.$search.'%')
                        ->orWhereHas('causer', function ($qry1) use ($search) {
                            $qry1->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate($this->getDataPerPage(), ['*'], 'activity-page')
            ->onEachSide(1);

        // Pre-fetch all User records needed to resolve 'user_id' changes, avoiding N+1
        $neededUserIds = [];
        foreach ($activities as $activity) {
            if ($activity->log_name !== 'updated') {
                continue;
            }
            $changes = $activity->properties['changes'] ?? [];
            $old = $activity->properties['old'] ?? [];
            if (array_key_exists('user_id', $changes)) {
                if (! empty($old['user_id'])) {
                    $neededUserIds[] = $old['user_id'];
                }
                if (! empty($changes['user_id'])) {
                    $neededUserIds[] = $changes['user_id'];
                }
            }
        }
        $usersMap = ! empty($neededUserIds)
            ? User::whereIn('id', array_unique($neededUserIds))->pluck('name', 'id')
            : collect();

        $updates = [];
        foreach ($activities as $activity) {
            if ($activity->log_name == 'updated') {
                $old = $activity->properties['old'];
                $changes = $activity->properties['changes'];

                $models = [
                    'user_id' => ['map' => $usersMap],
                ];

                foreach ($changes as $key => $update) {
                    if ($key === 'updated_at') {
                        continue;
                    }

                    $old_val = $old[$key];
                    $new_val = $update;

                    if (isset($models[$key])) {
                        $map = $models[$key]['map'];

                        $old_val = ! empty($old_val) ? $map->get($old_val) ?? '-' : '-';
                        $new_val = ! empty($new_val) ? $map->get($new_val) ?? '-' : '-';
                    }

                    if ($key === 'arr') {
                        if (is_array($old_val) && is_array($new_val)) {
                            $removed = array_diff($old_val, $new_val);
                            $added = array_diff($new_val, $old_val);

                            // Highlight removed items in red
                            foreach ($old_val as &$val) {
                                if (in_array($val, $removed)) {
                                    $val = '<span class="text-danger font-weight-bold">'.$val.'</span>';
                                }
                            }

                            // Highlight added items in green
                            foreach ($new_val as &$val) {
                                if (in_array($val, $added)) {
                                    $val = '<span class="text-success font-weight-bold">'.$val.'</span>';
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
                            'new' => $new_val,
                        ];
                    }
                }
            }
        }

        return view('pages.system-logs.index')->with([
            'activities' => $activities,
            'updates' => $updates,
            'search' => $search,
        ]);
    }
}
