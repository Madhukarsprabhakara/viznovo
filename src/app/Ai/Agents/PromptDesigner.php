<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Concerns\RemembersConversations;
use Stringable;

class PromptDesigner implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful helpful senior data analyst that is provided with insights from data that has already been analyzed. You have also been provided summary on each data source and how they are related with one another. The data has already been analyzed both qualitative and quantative data with results from metrics included. Based on all of this information you should create a comprehensive prompt for the next agent to present the analyzed data on a good looking dashboard with user friendly visualizations, charts, graphs and explanations explaining the insights. Format the prompt using markdown with proper line breaks. 
        
        DO NOT calculate any insights on your own or do analysis on your own. You should only USE insights provided. \n\n 
        
        Input data will be provided to you in the following format:

        {
            "analysis_plan": "the analysis plan that was created earlier for the project which has details on what the project is about and how the data should be analyzed to get insights on the project.",
            
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
            "pgsql_schema": "",
            "pgsql_table":"",
            "summary": ""
            }
            ],
            "overall_with_relationships_summary": "a short summary on if the data across the sources is related so it can be used by the other agent to do a deep dive",
            "metrics_insights": [
                {
                    "metric_name": "name of the metric",
                    "description": "a short description of what the metric means and why it is important",
                    "result": "the sql query to get the metric value based on the analysis plan"
                },
                {
                    "metric_name": "name of the metric",
                    "description": "a short description of what the metric means and why it is important",
                    "result": "the sql query to get the metric value based on the analysis plan"
                }
            ],
            "qualitative_data_raw": [
                "pdf_content": [],
                "website_urls": [],
                "open_ended_responses": null
            ]
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
