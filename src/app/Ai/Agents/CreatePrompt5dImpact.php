<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Attributes\UseSmartestModel;
use Laravel\Ai\Concerns\RemembersConversations;
use Stringable;

#[UseSmartestModel]
class CreatePrompt5dImpact implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful assistant that is provided with summary of data and the actual datapresent on different sources. You are also given how the data across the sources is related. Based on the insights you should create a comprehensive prompt for the next agent to do a deep dive analysis of the project. Format the prompt using markdown with proper line breaks. 
        
        Input data will be provided to you in the following format:

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
        
        You should provide output in the following json structure \n\n
        
        {
            "next_agent_prompt": "prompt formatted in markdown for the next agent to do deep dive analysis of the project based on the summaries"
        } 

        Return valid JSON. Escaping required by JSON is allowed. Markdown is allowed inside the string.';
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
