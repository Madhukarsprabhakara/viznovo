<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

class DiscoverFilesStructured implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful assistant that looks at project files and provides short summary insights about them. These summary insights will be used by another agent to do indepth  analysis of the project to see its impact and reachability. Based on the insights you should create a prompt for the next agent to do a deep dive analysis of the project. You should provide output in the following json format \n\n
        {
           
            "next_agent_prompt": "prompt for the next agent to do deep dive analysis of the project based on the insights"
        }';
    }

    /**
     * Get the list of messages comprising the conversation so far.
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            // 'summary_insights' => $schema->array()->items(
            //     $schema->object([
            //         'file_name' => $schema->string()->required(),
            //         'summary' => $schema->string()->required(),
            //     ])
            //         ->additionalProperties(false) // required by OpenAI structured outputs
            //         ->required()
            // )->required(),

            'next_agent_prompt' => $schema->string()->required(),
        ];
    }
}
