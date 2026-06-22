<?php

namespace App\Ai\Tools;

use App\Models\OrgStructureTree;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class OrgStructureTool implements Tool
{
    public function description(): Stringable|string
    {
        return "Use this tool to explore the org chart. List all org nodes or find a user's position, who they report to, and their direct reports.";
    }

    public function handle(Request $request): Stringable|string
    {
        $action     = (string) $request['action'];
        $searchTerm = (string) ($request['user'] ?? '');

        if ($action === 'list') {
            $nodes = OrgStructureTree::with('user')->get();
            if ($nodes->isEmpty()) return 'No org structure nodes found.';

            return "Org Chart Nodes:\n" . $nodes->map(fn($n) => sprintf(
                '- [%s] %s | Title: %s | Reports to node: %s',
                $n->id,
                $n->user?->name ?? 'Unassigned',
                $n->title ?? 'N/A',
                $n->reports_to_id ?? 'None (top level)'
            ))->join("\n");
        }

        if ($action === 'find') {
            if (empty($searchTerm)) return 'Error: A user name or email is required.';

            $user = User::where('name', 'like', "%{$searchTerm}%")->orWhere('email', $searchTerm)->first();
            if (!$user) return "Error: User '{$searchTerm}' not found.";

            $node = OrgStructureTree::where('user_id', $user->id)->first();
            if (!$node) return "{$user->name} is not placed in any org structure.";

            $reportsTo = 'Nobody (top level)';
            if ($node->reports_to_id) {
                $superior  = OrgStructureTree::with('user')->find($node->reports_to_id);
                $reportsTo = $superior?->user?->name ?? 'Unknown';
            }

            $directReports = OrgStructureTree::with('user')
                ->where('reports_to_id', $node->id)
                ->get()
                ->map(fn($n) => $n->user?->name ?? 'Unknown')
                ->join(', ');

            return implode("\n", array_filter([
                "User: {$user->name}",
                "Title: " . ($node->title ?? 'N/A'),
                "Reports to: {$reportsTo}",
                $directReports ? "Direct reports: {$directReports}" : null,
            ]));
        }

        return 'Invalid action provided.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()
                ->description("The operation to perform: list (all nodes) or find (a user's position)")
                ->required(),
            'user' => $schema->string()
                ->description('User name or email to look up in the org chart (required for find)'),
        ];
    }
}
