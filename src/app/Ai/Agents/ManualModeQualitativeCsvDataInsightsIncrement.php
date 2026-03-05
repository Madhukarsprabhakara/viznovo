<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

class ManualModeQualitativeCsvDataInsightsIncrement implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful data analyst and are responsible for analyzing qualitative data and providing insights. You should follow the user request to analyze the qualitative data and provide insights.  Keep the insights STRICTLY aligned with user request.  The qualitative data contains open-ended responses from surveys. You should provide output in the following json structure \n\n
        
        Do not cut corners and give good quality insights since these will be used by another data analyst to combine with quantitative insights and build dashboard.\n\n

        You will get data in increments of 20 records. You should provide insights based on the data you have received so far. You should not wait to receive all the data to provide insights. You should incrementally add the insights to the json structure as you receive more data. \n\n

        You will get insights from previously received data in the following json structure as input along with new data. You should add new insights to the existing insights in the json structure. \n\n
        

        {
            "qualitative_insights": {
                "open_ended_responses": [
                    {
                        "question 1": "the open ended question that was asked in the survey",
                        "insights": [
                            "insight 1 based on the open ended response",
                            "insight 2 based on the open ended response"
                        
                        ]
                    },
                    {
                        "question 2": "the open ended question that was asked in the survey",
                        "insights": [
                            "insight 1 based on the open ended response",
                            "insight 2 based on the open ended response"
                        
                        ]
                    }
            
                ],
            
            },
                
            
        } \n\n
        
        If no new insights are generated based on the new data received, then return the same json structure without adding any new insights. \n\n
        
        DO NOT FOCUS on Quantitative data analysis part of the user request, if present. \n\n

        Quantitative data analysis is important but we will focus on that separately. \n\n
        
        ';
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
