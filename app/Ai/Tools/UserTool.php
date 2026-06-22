<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use App\Models\User;
use App\Models\Company;
use App\Models\Position;
use Illuminate\Support\Facades\Hash;

class UserTool implements Tool
{
    public function description(): Stringable|string
    {
        return "Use this tool to find, list, update, or create new users (email, company, position).";
    }

    public function handle(Request $request): Stringable|string
    {
        $action = (string) $request['action'];

        // 1. Handle Listing
        if ($action === 'list') {
            $limit = $request['limit'] ?? 10;
            $users = User::with(['company', 'position'])->latest()->limit($limit)->get();

            return $users->isEmpty()
                ? "No users found."
                : "User List:\n" . $users->map(fn($u) => "- {$u->name} ({$u->email}) | Company: " . ($u->company?->name ?? 'N/A') . " | Position: " . ($u->position?->position ?? 'N/A'))->join("\n");
        }

        // 2. Handle Creation
        if ($action === 'create') {
            $name  = (string) ($request['name'] ?? '');
            $email = (string) ($request['email'] ?? '');

            if (empty($name) || empty($email)) {
                return "Error: Name and Email are required to create a user.";
            }

            $emailExists = User::where('email', $email)->exists();
            if ($emailExists) {
                return "Error: A user with email {$email} already exists.";
            }

            $email_arr = explode('@', $email);
            $password = Hash::make(reset($email_arr).'123!');

            $data = [
                'name'     => $name,
                'email'    => $email,
                'password' => $password,
            ];

            $resolved = $this->resolveRelations($request);
            if (is_string($resolved)) return $resolved; // Return error string if resolution fails

            $user = User::create(array_merge($data, $resolved));
            return "Successfully created user: {$user->name} (ID: {$user->id}).";
        }

        // 3. Handle Get/Update
        $user = $this->getTargetUser($request);
        if (!$user) {
            return "User not found.";
        }

        if ($action === 'get') {
            $user->load(['company', 'position']);
            return "Profile: {$user->name}, Email: {$user->email}, Company: " . ($user->company?->name ?? 'N/A') . ", Position: " . ($user->position?->position ?? 'N/A');
        }

        if ($action === 'update') {
            $updates = array_filter([
                'name'  => $request['name'] ?? null,
                'email' => $request['email'] ?? null,
            ]);
            $resolved = $this->resolveRelations($request);
            if (is_string($resolved)) return $resolved;

            $user->update(array_merge($updates, $resolved));
            return "Successfully updated {$user->name}.";
        }

        return "Invalid action.";
    }

    /**
     * Helper to resolve Company and Position names to IDs
     */
    protected function resolveRelations(Request $request): array|string
    {
        $ids = [];

        if (!empty($request['company'])) {
            $company = Company::where('name', 'like', "%".$request['company']."%")->first();
            if (!$company) return "Error: Company ".$request['company']." not found.";
            $ids['company_id'] = $company->id;
        }

        if (!empty($request['position'])) {
            $position = Position::where('position', 'like', "%".$request['position']."%")->first();
            if (!$position) return "Error: Position ".$request['position']." not found.";
            $ids['position_id'] = $position->id;
        }

        return $ids;
    }

    protected function getTargetUser(Request $request): ?User
    {
        if (!empty($request['search_term'])) {
            return User::where('name', 'like', "%".$request['search_term']."%")
                ->orWhere('email', $request['search_term'])
                ->first();
        }
        return User::find(auth()->id());
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->enum(['get', 'update', 'list', 'create'])->required()
                ->description('The action to perform.'),
            'search_term' => $schema->string()->description('Target user for get/update.'),
            'name' => $schema->string()->description('Required for creation.'),
            'email' => $schema->string()->description('User email.'),
            'company' => $schema->string()->description('Company name.'),
            'position' => $schema->string()->description('Position title.'),
            'limit' => $schema->integer()->description('Listing limit.'),
        ];
    }
}
