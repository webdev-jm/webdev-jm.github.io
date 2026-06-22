<?php

namespace App\Ai\Tools;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ActivityTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Use this tool to view activity logs. Optionally filter by user, event type (created, updated, deleted), or model type.';
    }

    public function handle(Request $request): Stringable|string
    {
        $action    = (string) $request['action'];
        $userTerm  = (string) ($request['user'] ?? '');
        $event     = (string) ($request['event'] ?? '');
        $modelType = (string) ($request['model'] ?? '');
        $limit     = (int) ($request['limit'] ?? 15);

        if ($action !== 'list') return 'Invalid action provided. Use: list';

        $query = Activity::with('causer')->latest()->limit($limit);

        if (!empty($userTerm)) {
            $user = User::where('name', 'like', "%{$userTerm}%")->orWhere('email', $userTerm)->first();
            if (!$user) return "Error: User '{$userTerm}' not found.";
            $query->where('causer_id', $user->id)->where('causer_type', User::class);
        }

        if (!empty($event)) {
            $query->where('event', $event);
        }

        if (!empty($modelType)) {
            $query->where('subject_type', 'like', "%{$modelType}%");
        }

        $logs = $query->get();
        if ($logs->isEmpty()) return 'No activity logs found.';

        return "Activity Logs:\n" . $logs->map(fn($log) => sprintf(
            '- [%s] %s %s %s',
            $log->created_at->format('Y-m-d H:i'),
            $log->causer?->name ?? 'System',
            $log->event ?? 'acted on',
            class_basename($log->subject_type ?? 'unknown')
        ))->join("\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()
                ->description('The operation to perform: list')
                ->required(),
            'user' => $schema->string()
                ->description('Filter logs by user name or email'),
            'event' => $schema->string()
                ->description('Filter by event type (created, updated, deleted)'),
            'model' => $schema->string()
                ->description('Filter by subject model type (e.g., User, Company, Position)'),
            'limit' => $schema->integer()
                ->description('Maximum number of logs to return, default 15'),
        ];
    }
}
