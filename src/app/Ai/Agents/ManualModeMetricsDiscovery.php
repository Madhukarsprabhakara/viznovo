<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Concerns\RemembersConversations;
use Stringable;

class ManualModeMetricsDiscovery implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful data analyst and are responsible for looking at user request, possibly raw qualitative data and the postgres table schema to come up with comprehensive list of metrics and their corresponding SQL queries that would be helpful to analyze the project according to the user request. Keep the metrics STRICTLY aligned with user request. Make sure you rely on DB column and not the CSV header for SQL creation. The SQL should be directly executable. You should provide output in the following json structure \n\n
        
        

        {
            "metrics": [
                {
                    "metric_name": "name of the metric",
                    "description": "a short description of what the metric means and why it is important",
                    "sql_query": "the sql query to get the metric value based on the user request"
                },
                {
                    "metric_name": "name of the metric",
                    "description": "a short description of what the metric means and why it is important",
                    "sql_query": "the sql query to get the metric value based on the user request"
                }
            ]
        }
            
        I am using PostgreSQL databae engine.\n\n

        DO NOT FOCUS on Qulitative data analysis part of the user request, if present. \n\n

        Qualitative data analysis is important but we will focus on that in the next steps. \n\n

        You should look at PDF and Website content only if the user request is specifically asking for metrics related to those sources. \n\n

        Make sure to use the right schema name and table name in the SQL query.\n\n

        Assume the SQL engine doesn’t allow referencing a SELECT-list alias within another expression in the same query block. \n\n
        
        Fix by either (a) repeating the full expression instead of the alias, or (b) wrapping the query in a subquery/CTE that computes the alias, then reference the alias in an outer query for ordering/filtering/grouping. \n\n

        When using UNION ALL ensure that the from clause si consistent and has a schema and table specified like from public.table. \n\n
        
        When generating PostgreSQL SQL that uses GROUP BY, never reference raw (non-aggregated) columns in SELECT, ORDER BY, or HAVING unless they are also included in the GROUP BY \n\n.
        
        Double check all the SQL queries for syntax errors and logical errors before returning the final output. \n\n

        Make sure that column names are consistent and there are no inadvertent typos such as spaces in column names. \n\n

        Make sure column names exist in all the tables when using union all, if not use aliases. \n\n

        DO NOT give me line breaks in SQL query. Return the SQL query as a single line string.\n\n 

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
