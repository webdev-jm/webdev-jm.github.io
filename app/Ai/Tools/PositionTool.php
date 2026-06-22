<?php

namespace App\Ai\Tools;

use App\Models\Position;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class PositionTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Use this tool to manage positions (find, list, update, or create).';
    }

    public function handle(Request $request): Stringable|string
    {
        $action = $request['action'] ?? null;
        $name = $request['name'] ?? null;
        $id = $request['id'] ?? null;

        if ($action === 'list') {
            $positions = Position::all();
            if ($positions->isEmpty()) return 'No positions found.';
            return "Positions:\n" . $positions->map(fn($p) => "- [{$p->id}] {$p->position}")->join("\n");
        }

        if ($action === 'find') {
            if (!$name) return 'Error: A name is required to find a position.';
            $position = Position::where('position', 'like', "%{$name}%")->first();
            return $position
                ? "Found: [{$position->id}] {$position->position}"
                : 'Position not found.';
        }

        if ($action === 'create') {
            if (!$name) return 'Error: A name is required to create a position.';
            return $this->createPosition($name);
        }

        if ($action === 'update') {
            if (!$id) return 'Error: An ID is required to update a position.';
            if (!$name) return 'Error: A new name is required to update a position.';
            return $this->updatePosition($id, $name);
        }

        return 'Invalid action provided.';
    }

    protected function createPosition(string $name): string
    {
        if (Position::where('position', $name)->exists()) {
            return "Error: The position '{$name}' already exists. Use the list action to see all current positions.";
        }

        $position = Position::create(['position' => $name]);
        return "Success: Position '{$position->position}' has been created with ID {$position->id}.";
    }

    protected function updatePosition(string $id, string $name): string
    {
        $position = Position::find($id);
        if (!$position) return "Error: Position with ID {$id} not found.";

        $position->update(['position' => $name]);
        return "Success: Position updated to '{$name}'.";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()
                ->description('The operation to perform: list, find, create, or update')
                ->required(),
            'name' => $schema->string()
                ->description('The name of the position (required for find, create, update)'),
            'id' => $schema->string()
                ->description('The ID of the position (required for update)'),
        ];
    }
}
