<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Concerns\RemembersConversations;
use Stringable;

class ManualModeQualitativeDataInsights implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful data analyst and are responsible for analyzing qualitative data and providing insights. You should follow the user request to analyze the qualitative data and provide insights.  Keep the insights STRICTLY aligned with user request.  The qualitative data may include PDF content, open-ended responses from surveys, and website URL content. You should provide output in the following json structure \n\n
        
        Do not cut corners and give good quality insights since these will be used by another data analyst to combine with quantitative insights and build dashboard.\n\n

        {
            "qualitative_insights": [
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
                "pdf_content": [
                    {
                        "file_name": "file_1",
                        "insights": [
                            "insight 1 based on the content of file_1",
                            "insight 2 based on the content of file_1"
                        
                        ]
                    },
                    {
                        "file_name": "file_2",
                        "insights": [
                            "insight 1 based on the content of file_2",
                            "insight 2 based on the content of file_2"
                        
                        ]
                    }
                ],
                "website_urls": [
                    {
                        "url": "url_1",
                        "insights": [
                            "insight 1 based on the content of url_1",
                            "insight 2 based on the content of url_1"
                        
                        ]
                    },
                    {
                        "url": "url_2",
                        "insights": [
                            "insight 1 based on the content of url_2",
                            "insight 2 based on the content of url_2"
                        
                        ]
                    }
                ]
            
            ],
                
            
        } \n\n
        
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
