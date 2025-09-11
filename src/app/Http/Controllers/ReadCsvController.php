<?php

namespace App\Http\Controllers;
use League\Csv\Reader;
use Illuminate\Http\Request;

class ReadCsvController extends Controller
{
    public $standardHeader ='Here is the data collected on a survey in the form of JSON object. \n\n. '; 
    public $standardFooter='Give your response in the "prompt_response" key in the json object shown below. \n\nDo not give additional explanation outside of the json object. \n\n Do not return array in prompt_response key \n\n




                    [
                        {
                            "prompt_response": ""
                            
                        }
                    ]
                        
                 

                     '.'\n\n'. 
                     
                     'here is the response'. '\n\n';
                     
    public $standardHtmlFormat = 'Provide me a well formatted HTML page.

        I am using tailwindcss 4.1.1. 

        Do not use any other css.

        DO NOT give me code fence. Just the HTML should be returned.

        Make it visually appealing.';   
    //
    public function readCSV()
    {
        
        
        try {

            // Path relative to storage/app/public
        $filePath = 'Girls code-pre-mid-post.csv';

        // Get the full path
        $filePath = storage_path('app/public/Girls code-pre-mid-post.csv');
        $csv = Reader::createFromPath($filePath, 'r');


        $csv->setHeaderOffset(0);

        $header = $csv->getHeader(); //returns the CSV header record

       // Get all records as associative arrays
        $records = array_values(iterator_to_array($csv->getRecords()));

        $json_object=response()->json($records);
            // Initialize OpenAI client
             $client = \OpenAI::client(config('services.openai.key'));
            $rules = 'Please generate a comprehensive impact survey report with THIS SPECIFIC STRUCTURE, VISUAL STYLE, and VOICE, using ONLY the provided pre,mid, post survey data:

# Brand & Layout Instructions
Use a modern, clear, encouraging, and visionary tone (think: confident tech/growth/innovation brand).
Headings: Bold, large, dark slate color, using "Jakarta" or similar font.
Body: Lighter gray, modern ("Geist" or similar), easy to scan.
Major stats: Large, bold numbers in #0E7490 (teal-cyan brand), paired with short explanations below in lighter text.
Cards and highlights: White/very light backgrounds, rounded corners, drop shadows, small icons for emphasis (bar-chart, check, warning, star, etc).
Lists:  check-circle icons for positives, warning/exclamation icons for challenges.
Feature “blocks” or “cards” for modularity and visual structure—sections should all stand alone.
Consistency in spacing, margins, and grid alignment.

Report Structure & Section Instructions
1. Executive Summary
Set title of the report to "Girls code program Impact Report"
Large, motivating headline
1–2 sentences summarizing the key survey outcome.
2–3 core stats displayed as bold numbers, brand color, with short explanatory subtext (side-by-side or in a row).

2. Key Program Insights
Grid/list of 3–5 highlight cards.
Each: Icon, bold headline, 1–2 sentence explanation (e.g., “Bar-chart: Rapid Skills Growth – 90% completed coding projects.”)
Card layout (white background, icon left/top, headline, text).

3. Participant Experience
List or split grid:
Positive Experiences (check icons for each, use testimonials/quotes in callout bubbles; brief, direct).
Challenges or Negative Feedback (exclamation/warning icons, concise, separated visually from positives).

4. Improvements in Confidence & Skills
Grid or mini-section:
Compare pre, mid and post results indicated by the STAGE field.
User percentages and numbers instead of Many/Some etc.
Use callouts highlighting confidence/skills gains (big percent or chart, with supportive quotes or concrete milestones).

5. Opportunities to Improve
List of 3–6 specific, actionable suggestions.
Each in its own “card” with arrow-up, star, or “growth” icon.
Use clear, concise phrasing; modular so each suggestion stands on its own.

6. FAQ or “Need to Know”
3–5 Q&A entries formatted as collapsible cards, details, or grid.
Bold question, concise supportive answer,  icons for each.

7. Overall Summary
Provide an overall assessment of the program and its impact on young girls.

8. Footer
Add a footer with words "Powered by Sopact, Inc"

# Formatting
Each section, card, and stats block should be modular and copyable as its own block.
All key ideas, stats, recommendations, and testimonials are styled for clear distractions.
Use semantic HTML structure and Tailwind (or similar) utility classes when outputting code or HTML-like sections.
NO new sections—stick only to the above, and in this order.
Make it mobile responsive so it works on all screen sizes.
Use heroicons svgs for icons.';
            // Prepare the prompt
            // $prompt = $this->standardHeader . $question_text . "\n\n" . $rules . $this->standardFooter . $response . "> ```json";
            $prompt = $this->standardHeader. $json_object.'\n\n'. $rules. $this->standardHtmlFormat.$this->standardFooter.'> ```json';
            // Call OpenAI API
            $result = $client->chat()->create([
                'model' => 'gpt-5',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_completion_tokens' => 128000,
            ]);
            // Get the output
            $output = $result->choices[0]->message->content ?? '';
            
            
            
            $data['output'] = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::info('JSON Error:', ['error' => json_last_error_msg()]);
            }

            // Check if output key exists and is an array
            if (!isset($data['output']) || !is_array($data['output'])) {
                \Log::info('JSON Error:', ['error' => 'Output key does not exist or is not an array']); 
                exit;
            }

            // Check if output array is not empty and first element has prompt_response key
            if (empty($data['output']) || !isset($data['output'][0]['prompt_response'])) {
                \Log::info('JSON Error:', ['error' => 'Output array is empty or prompt_response key does not exist']); 
                exit;
            }

            // If all checks pass, extract the prompt_response data
            $promptResponse = $data['output'][0]['prompt_response'];
            // \Log::info('JSON Output:', ['prompt_response' => $promptResponse]); 
            if ($promptResponse) {
            // \Log::info('JSON Output:', ['success' => 'sending clean data']); 
            return [
                        'status' => 'success',
                        'message' => 'Response generated successfully.',
                        'data' => stripslashes($promptResponse),
                    ];

            }
           

            

            return [
                'status' => 'fail',
                'message' => 'Unable to parse AI response into an array.',
                'data' => null,
            ];
        } catch (\Exception $e) {
            
            return [
                'status' => 'error',
                'message' => 'An exception occurred: ' . $e->getMessage(),
                'data' => null,
            ];
        }

        
    }
}
