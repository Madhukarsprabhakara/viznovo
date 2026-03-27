<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

class DerivedColumnChunkProcessor implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful assistant. Just follow the instrcutions to analyze the chunk of records and add calculated column values to each record in the chunk based on the instructions provided for each calculated column. \n\n
        
        Return the junk records in the following json structure with the calculated column values added to each record. \n\n
        
        {
            "chunk_index": "the index of the chunk starting from 0",
            "total_chunks": "",
            "db_column" : "original db column value",
            "records": [
                {
                    "derived_db_column": "value1",
                    "id": "id value"
                },
                {
                    "derived_db_column": "value1",
                    "id": "id value"
                }
               
            ]
        } \n\n
        
        
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
