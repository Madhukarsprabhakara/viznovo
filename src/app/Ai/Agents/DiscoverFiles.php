<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\WebFetch;
use Stringable;

class DiscoverFiles implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful assistant that looks at project files and urls and provides short summary insights about them. These summary insights will be used by another agent to do indepth  analysis of the project to see its impact and reachability. Based on the insights you should create a prompt for the next agent to do a deep dive analysis of the project. Format the prompt using markdown. You should provide output in the following json structure \n\n
        {
            "summary_insights" : [
            {
            "file_name": "file_1",
            "summary": "summary of file_1"
            },
            {
            "file_name": "file_2",
            "summary": "summary of file_2"
            },
            {
            "url": "url_1",
            "summary": "summary of url_1"
            },
            {
            "url": "url_2",
            "summary": "summary of url_2"
            }
            ],
            "next_agent_prompt": "prompt for the next agent to do deep dive analysis of the project based on the insights"
        } 
            \n\n DO NOT return any line breaks such as \n or \r in the response.

        DO NOT return escaped characters such as \", \', \\ etc.\n\n';
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
        return [
            new WebFetch,
        ];
    }
}
