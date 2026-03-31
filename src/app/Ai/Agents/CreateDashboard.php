<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Concerns\RemembersConversations;
use Stringable;

class CreateDashboard implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
      public function instructions(): Stringable|string
    {
        return 'You are a helpful assistant. You should follow any instructions provided in the input to you\n\n. 
        
        - Data will be provided to you in the following format:

            {
            
            "qualitative_data_raw": {
                "pdf_content": [],
                "website_urls": [],
            },
            
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
            "qualitative_insights": [
                "pdf_content": [],
                "website_urls": [],
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
                
                ]
            ]
        }



       - Give your response in the "prompt_response" key in the json object shown below. \n\nDo not give additional explanation outside of the json object. \n\n Do not return array in prompt_response key \n\n




                    [
                        {
                            "prompt_response": ""
                            
                        }
                    ]

    
    
# Formatting
- Provide me a well designed dashboard in HTML. \n\n

- Just the HTML code should be returned that can be rendered directly on browser. \n\n

- Return raw, browser-renderable HTML only — not a JSON string, not an escaped string, and not wrapped in quotes. Do not escape double quotes in attributes (use `class="..."`, not `class=\"...\"`). Output a complete HTML document beginning with `<!doctype html>` and ending with `</html>`.  \n\n

- Double check that it is renderable on the browser as is without any modifications.\n\n

- USE ONLY tailwindcss. \n\n

- Do NOT show raw JSON records or results.\n\n
 
- Each section, card, and stats block should be modular and copyable as its own block.\n\n

- Each block or card should be one below the other so it looks good even on small screen sizes.\n\n

- All key ideas, stats, recommendations, and testimonials are styled for clear distractions.\n\n

- Make it mobile responsive so it works on all screen sizes.\n\n

- Use lucide vue next svgs for icons.\n\n

- Do not use any other css.\n\n

- DO NOT give me code fence. \n\n

- DO NOT return any line breaks such as \n or \r in the html code.\n\n

- DO NOT calculate any insights on your own or do analysis on your own. You should only USE insights provided.\n\n

- Return valid JSON. Escaping required by JSON (e.g. for quotes inside strings) is allowed.\n\n
- Do not wrap the entire JSON response in quotes (no double-encoding).\n\n

- DO NOT use script or style tags.\n\n

- Make it visually appealing.\n\n
       
- DO NOT use DARK colors but you can use different shades of light colors to create contrast and visual interest.\n\n

- USE blocks, spacing, and typography to enhance the design.';
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
