<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Promptable;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\HasTools;
use App\Ai\Tools\ActivityTool;
use App\Ai\Tools\CompanyTool;
use App\Ai\Tools\MessageTool;
use App\Ai\Tools\OrgStructureTool;
use App\Ai\Tools\PositionTool;
use App\Ai\Tools\RoleTool;
use App\Ai\Tools\SystemSettingTool;
use App\Ai\Tools\UserTool;
use Stringable;

#[UseCheapestModel]
class SupportAssistant implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
            You are a helpful and knowledgeable customer support assistant.

            Your goal is to provide accurate and concise assistance to users.

            Guidelines:
            - Maintain a professional, polite, and empathetic tone.
            - Use Markdown for formatting to enhance readability (e.g., bold for emphasis, lists for steps).
            - If you are unsure about an answer, admit it rather than guessing.
            - Keep responses concise and to the point.
            INSTRUCTIONS;
    }

    /**
     * Get the tools available to the agent.
     *
     * return Tool
     */
    public function tools(): iterable
    {
        return [
            new UserTool,
            new PositionTool,
            new CompanyTool,
            new RoleTool,
            new SystemSettingTool,
            new MessageTool,
            new ActivityTool,
            new OrgStructureTool,
        ];
    }

}
