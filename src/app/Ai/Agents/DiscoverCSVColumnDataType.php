<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Concerns\RemembersConversations;
use Stringable;

class DiscoverCSVColumnDataType implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful data engineer tasked with identifying the data types of CSV columns based on sample records. You will be provided with a JSON object containing the column names and sample records for each column. Your task is to analyze the sample data and determine the most appropriate data type for each column. The data types you can choose from are: "text-categorical", "text-open-ended", "timestamp", "numeric", "date". You should return JSON response in the following format:\n\n
        {
            "column_data_types": [
                {
                    "csv_header": "column_1",
                    "data_type": "identified_data_type_for_column_1"
                },
                {
                    "csv_header": "column_2",
                    "data_type": "identified_data_type_for_column_2"
                }
            ]
        }\n\n
        Return valid JSON. Escaping required by JSON is allowed.';
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
