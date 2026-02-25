<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Concerns\RemembersConversations;
use Stringable;

class MetricsDiscovery implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a helpful data analyst and are responsible for looking at a comprehensive data analysis plan and the postgres table schema to come up with comprehensive list of metrics and their corresponding SQL queries that would be helpful to analyze the project according to the plan. The SQL should be directly executable. You should provide output in the following json structure \n\n
        
        

        {
            "metrics": [
                {
                    "metric_name": "name of the metric",
                    "description": "a short description of what the metric means and why it is important",
                    "sql_query": "the sql query to get the metric value based on the analysis plan"
                },
                {
                    "metric_name": "name of the metric",
                    "description": "a short description of what the metric means and why it is important",
                    "sql_query": "the sql query to get the metric value based on the analysis plan"
                }
            ]
        }
            If a query errors like “column <alias> does not exist” when the alias is used in ORDER BY/GROUP BY/HAVING, assume the SQL engine doesn’t allow referencing a SELECT-list alias within another expression in the same query block. \n\n
        
        Fix by either (a) repeating the full expression instead of the alias, or (b) wrapping the query in a subquery/CTE that computes the alias, then reference the alias in an outer query for ordering/filtering/grouping. \n\n

        When using UNION ALL ensure that the from clause si consistent and has a schema and table specified like from public.table. \n\n
        
        When generating PostgreSQL SQL that uses GROUP BY, never reference raw (non-aggregated) columns in SELECT, ORDER BY, or HAVING unless they are also included in the GROUP BY \n\n.
        
        Double check all the SQL queries for syntax errors and logical errors before returning the final output. \n\n

        Make sure that column names are consistent and there are no inadvertent typos such as spaces in column names. \n\n

        Make sure column names exist in all the tables when using union all, if not use aliases. \n\n

        DO NOT give me line breaks in SQL query. Return the SQL query as a single line string.

            \n\n Return valid JSON. Escaping required by JSON is allowed.';
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
