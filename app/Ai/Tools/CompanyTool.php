<?php

namespace App\Ai\Tools;

use App\Models\Company;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CompanyTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Use this tool to manage companies (list, find, create, or update).';
    }

    public function handle(Request $request): Stringable|string
    {
        $action = (string) $request['action'];
        $name   = (string) ($request['name'] ?? '');
        $id     = (string) ($request['id'] ?? '');

        if ($action === 'list') {
            $companies = Company::withCount('users')->get();
            if ($companies->isEmpty()) return 'No companies found.';
            return "Companies:\n" . $companies->map(fn($c) => "- [{$c->id}] {$c->name} ({$c->users_count} users)")->join("\n");
        }

        if ($action === 'find') {
            if (empty($name)) return 'Error: A name is required to find a company.';
            $company = Company::where('name', 'like', "%{$name}%")->withCount('users')->first();
            return $company
                ? "Found: [{$company->id}] {$company->name} ({$company->users_count} users)"
                : 'Company not found.';
        }

        if ($action === 'create') {
            if (empty($name)) return 'Error: A name is required to create a company.';
            if (Company::where('name', $name)->exists()) {
                return "Error: A company named '{$name}' already exists.";
            }
            $company = Company::create(['name' => $name]);
            return "Success: Company '{$company->name}' created with ID {$company->id}.";
        }

        if ($action === 'update') {
            if (empty($id)) return 'Error: An ID is required to update a company.';
            if (empty($name)) return 'Error: A new name is required to update a company.';
            $company = Company::find($id);
            if (!$company) return "Error: Company with ID {$id} not found.";
            $company->update(['name' => $name]);
            return "Success: Company updated to '{$name}'.";
        }

        return 'Invalid action provided.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()
                ->description('The operation to perform: list, find, create, or update')
                ->required(),
            'name' => $schema->string()
                ->description('The company name (required for find, create, update)'),
            'id' => $schema->string()
                ->description('The ID of the company (required for update)'),
        ];
    }
}
