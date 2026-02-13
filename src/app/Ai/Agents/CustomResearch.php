<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\WebFetch;
use Stringable;

class CustomResearch implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful assistant. You should follow any instructions provided in the input to you\n\n. 
        
        - Data will be provided to you in the following format:

            {
  "pdf_content": [],
  "csv_content": [],
  "website_content": [
    {
      "website_url": "https://malyaconsultants.com.au/services",
      "website_html": "<html>...</html>"
    },
    {
      "website_url": "https://viznovo.com",
        "website_html": "<html>...</html>"
    },
    {
      "website_url": "https://15timesbetter.com.au/",
        "website_html": "<html>...</html>"
    }
  ]
}



       - Give your response in the "prompt_response" key in the json object shown below. \n\nDo not give additional explanation outside of the json object. \n\n Do not return array in prompt_response key \n\n




                    [
                        {
                            "prompt_response": ""
                            
                        }
                    ]

       - Provide me a well designed HTML page.

       - I am using tailwindcss. 

        - Do not use any other css.

       - DO NOT give me code fence. Just the HTML  should be returned.

       - DO NOT return any line breaks such as \n or \r in the html code.

       - DO NOT return escaped characters such as \", \', \\ etc.

       - DO NOT use script or style tags.

       - Make it visually appealing.';
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
