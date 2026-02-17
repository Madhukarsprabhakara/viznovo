<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;


class CustomResearch implements Agent, Conversational, HasTools
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

    
    
# Formatting
- Provide me a well designed dashboard in HTML.

- Just the HTML code should be returned that can be rendered directly on browser.

 - USE ONLY tailwindcss. 

- Each section, card, and stats block should be modular and copyable as its own block.

- Each block or card should be one below the other so it looks good even on small screen sizes.

- All key ideas, stats, recommendations, and testimonials are styled for clear distractions.

- Make it mobile responsive so it works on all screen sizes.

- Use lucide vue next svgs for icons.

- Do not use any other css.

- DO NOT give me code fence. 

- DO NOT return any line breaks such as \n or \r in the html code.

- Return valid JSON. Escaping required by JSON (e.g. for quotes inside strings) is allowed.
- Do not wrap the entire JSON response in quotes (no double-encoding).

- DO NOT use script or style tags.

- Make it visually appealing.
       
- Use light pastel colors, blocks, spacing, and typography to enhance the design.';
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
