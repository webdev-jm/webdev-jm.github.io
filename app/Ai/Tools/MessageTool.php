<?php

namespace App\Ai\Tools;

use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class MessageTool implements Tool
{
    public function description(): Stringable|string
    {
        return "Use this tool to view a user's inbox or outbox, or to send a message to another user.";
    }

    public function handle(Request $request): Stringable|string
    {
        $action = (string) $request['action'];
        $limit  = (int) ($request['limit'] ?? 10);

        if ($action === 'inbox' || $action === 'outbox') {
            $searchTerm = (string) ($request['user'] ?? '');
            if (empty($searchTerm)) return 'Error: A user name or email is required.';

            $user = User::where('name', 'like', "%{$searchTerm}%")->orWhere('email', $searchTerm)->first();
            if (!$user) return "Error: User '{$searchTerm}' not found.";

            if ($action === 'inbox') {
                $messages = Message::with('sender')->where('receiver_id', $user->id)->latest()->limit($limit)->get();
                if ($messages->isEmpty()) return "{$user->name} has no inbox messages.";
                return "Inbox for {$user->name}:\n" . $messages->map(fn($m) => "- From {$m->sender->name}: {$m->message}")->join("\n");
            }

            $messages = Message::with('receiver')->where('sender_id', $user->id)->latest()->limit($limit)->get();
            if ($messages->isEmpty()) return "{$user->name} has no outbox messages.";
            return "Outbox for {$user->name}:\n" . $messages->map(fn($m) => "- To {$m->receiver->name}: {$m->message}")->join("\n");
        }

        if ($action === 'send') {
            $toTerm = (string) ($request['to'] ?? '');
            $body   = (string) ($request['message'] ?? '');

            if (empty($toTerm)) return 'Error: A recipient (to) is required.';
            if (empty($body)) return 'Error: A message body is required.';

            $sender   = User::find(auth()->id());
            $receiver = User::where('name', 'like', "%{$toTerm}%")->orWhere('email', $toTerm)->first();

            if (!$sender) return 'Error: No authenticated user to send from.';
            if (!$receiver) return "Error: Recipient '{$toTerm}' not found.";
            if ($sender->id === $receiver->id) return 'Error: Cannot send a message to yourself.';

            Message::create([
                'sender_id'   => $sender->id,
                'receiver_id' => $receiver->id,
                'message'     => $body,
                'is_read'     => false,
            ]);

            return "Success: Message sent to {$receiver->name}.";
        }

        return 'Invalid action provided.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()
                ->description('The operation to perform: inbox, outbox, or send')
                ->required(),
            'user' => $schema->string()
                ->description('Name or email of the user whose messages to view (for inbox/outbox)'),
            'to' => $schema->string()
                ->description('Name or email of the message recipient (for send)'),
            'message' => $schema->string()
                ->description('The message body text (for send)'),
            'limit' => $schema->integer()
                ->description('Maximum number of messages to return, default 10 (for inbox/outbox)'),
        ];
    }
}
