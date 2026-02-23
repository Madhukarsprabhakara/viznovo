<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Concerns\RemembersConversations;
use Stringable;

class AnalysisPlanning implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful data analyst that is provided with summary of data and sample data on different sources. You are also given how the data across the sources are related. Based on the insights you should create a comprehensive plan on what would you analyze and how you would go about doing it. Format the plan using markdown with proper line breaks. 
        
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
            },
            {
            "pgsql_schema": "schema_name",
            "pgsql_table": "table_name",
            "summary": "summary of pgsql table data"
            },
            ],
            "overall_with_relationships_summary": "a short summary on if the data across the sources is related so it can be used by the other agent to do a deep dive"
        }
        
        You should provide output in the following json structure \n\n
        
        {
            "analysis_plan": "prompt formatted in markdown for the next agent to do deep dive analysis of the project based on the summaries"
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
