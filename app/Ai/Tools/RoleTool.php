<?php

namespace App\Ai\Tools;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class RoleTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Use this tool to list roles, find a role, or assign/revoke roles for users.';
    }

    public function handle(Request $request): Stringable|string
    {
        $action     = (string) $request['action'];
        $roleName   = (string) ($request['role'] ?? '');
        $searchTerm = (string) ($request['user'] ?? '');

        if ($action === 'list') {
            $roles = Role::withCount('users')->get();
            if ($roles->isEmpty()) return 'No roles found.';
            return "Roles:\n" . $roles->map(fn($r) => "- {$r->name} ({$r->users_count} users)")->join("\n");
        }

        if ($action === 'find') {
            if (empty($roleName)) return 'Error: A role name is required.';
            $role = Role::where('name', 'like', "%{$roleName}%")->withCount('users')->first();
            return $role
                ? "Found: {$role->name} ({$role->users_count} users)"
                : 'Role not found.';
        }

        if (empty($roleName)) return 'Error: A role name is required.';
        if (empty($searchTerm)) return 'Error: A user name or email is required.';

        $role = Role::where('name', $roleName)->first();
        if (!$role) return "Error: Role '{$roleName}' not found.";

        $user = User::where('name', 'like', "%{$searchTerm}%")->orWhere('email', $searchTerm)->first();
        if (!$user) return "Error: User '{$searchTerm}' not found.";

        if ($action === 'assign') {
            if ($user->hasRole($roleName)) return "{$user->name} already has the '{$roleName}' role.";
            $user->assignRole($roleName);
            return "Success: Role '{$roleName}' assigned to {$user->name}.";
        }

        if ($action === 'revoke') {
            if (!$user->hasRole($roleName)) return "{$user->name} does not have the '{$roleName}' role.";
            $user->removeRole($roleName);
            return "Success: Role '{$roleName}' revoked from {$user->name}.";
        }

        return 'Invalid action provided.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()
                ->description('The operation to perform: list, find, assign, or revoke')
                ->required(),
            'role' => $schema->string()
                ->description('The role name (required for find, assign, revoke)'),
            'user' => $schema->string()
                ->description('The user name or email (required for assign, revoke)'),
        ];
    }
}
