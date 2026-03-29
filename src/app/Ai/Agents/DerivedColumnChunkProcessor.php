<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Concerns\RemembersConversations;
use Stringable;

class DerivedColumnChunkProcessor implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful assistant. Just follow the instrcutions to analyze the chunk of records and add calculated column values to each record in the chunk based on the instructions provided for each calculated column. \n\n
        
        Return the chunk records in the following json structure with the calculated column values added to each record. \n\n
        
        {
            "db_column" : "original db column value",
            "records": [
                {
                    "derived_db_column": "response based on prompt instructions",
                    "id": "id value"
                },
                {
                    "derived_db_column": "response based on prompt instructions",
                    "id": "id value"
                }
               
            ]
        } \n\n

        - For example:

        The input chunk record will look like this: \n\n

            {
                "prompt": "Read the feedback text and assign the primary theme into one of these buckets only: learning_gain, program_quality, engagement, mixed_other, unknown. Use learning_gain when the feedback emphasizes learning, knowledge, skills, coding concepts, or phrases like learned a lot. Use program_quality when the feedback mainly evaluates the program overall, such as fantastic program, good program, bad program. Use engagement when the feedback focuses on enjoyment, interest, boredom, or excitement. Use mixed_other when multiple themes appear equally or the text is interpretable but does not fit one primary theme. Use unknown when the response is blank, null, or too vague to infer a theme.",
                "derived_db_column": "feedback_theme",
                "db_column": "feedback",
                "records": [
                    {
                    "id": 1,
                    "feedback": "Fantastic program and I learned a lot about performant systems in this"
                    },
                    {
                    "id": 2,
                    "feedback": "It was a ok program"
                    }
                ]
            }

        The response would look like this: \n\n

        {
            "db_column": "feedback",
            "records": [
                {
                "feedback_theme": "mixed_other",
                "id": 1
                },
                {
                "feedback_theme": "program_quality",
                "id": 2
                }
            ]
        }
        
        
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
