<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Concerns\RemembersConversations;

use Stringable;


class DiscoverFiles implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful assistant that looks at project files and url contents and provides short summary about the data in each of these sources. These summaries will be used by another agent to do indepth  analysis of the project. Based on the summaries you should also provide a short summary on if the data across the sources is related so it can be used by the other agent to do a deep dive. You should provide output in the following json structure \n\n
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
            "overall_with_relationships_summary": "a short summary on if the data across the sources is related so it can be used by the other agent to do a deep dive"
        } 
            \n\n Return valid JSON. Escaping required by JSON is allowed.';
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
}
