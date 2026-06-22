<?php

namespace App\Ai\Tools;

use App\Models\SystemSetting;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SystemSettingTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Use this tool to view or update system settings (records per page, email sending toggle).';
    }

    public function handle(Request $request): Stringable|string
    {
        $action = (string) $request['action'];

        $setting = SystemSetting::first();
        if (!$setting) return 'No system settings record found.';

        if ($action === 'get') {
            return "System Settings:\n- Data per page: {$setting->data_per_page}\n- Email sending: " . ($setting->email_sending ? 'Enabled' : 'Disabled');
        }

        if ($action === 'update') {
            $updates = [];

            $perPage      = $request['data_per_page'] ?? null;
            $emailSending = $request['email_sending'] ?? null;

            if ($perPage !== null) $updates['data_per_page'] = (int) $perPage;
            if ($emailSending !== null) $updates['email_sending'] = (bool) $emailSending;

            if (empty($updates)) return 'Error: At least one setting value must be provided.';

            $setting->update($updates);
            return 'Success: System settings updated.';
        }

        return 'Invalid action provided.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()
                ->description('The operation to perform: get or update')
                ->required(),
            'data_per_page' => $schema->integer()
                ->description('Number of records per page (for update)'),
            'email_sending' => $schema->boolean()
                ->description('Enable or disable email sending — true or false (for update)'),
        ];
    }
}
