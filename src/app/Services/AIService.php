<?php

namespace App\Services;

use Aws\BedrockRuntime\BedrockRuntimeClient;

class AIService
{

    public $standardHeader = 'You are an analyst or a reseracher who has been asket to extract insights from the data can be pdf or csv file content. \n\n. ';
    public $standardFooter = 'Give your response in the "prompt_response" key in the json object shown below. \n\nDo not give additional explanation outside of the json object. \n\n Do not return array in prompt_response key \n\n




                    [
                        {
                            "prompt_response": ""
                            
                        }
                    ]
                        
                 

                     ' . '\n\n' .

        'here is the response' . '\n\n';

    public $standardHtmlFormat = 'Provide me a well formatted HTML page.

        I am using tailwindcss. 

        Do not use any other css.

        DO NOT give me code fence. Just the HTML should be returned.

        DO NOT return any line breaks such as \n or \r in the html code.

        DO NOT return escaped characters such as \", \', \\ etc.

        DO NOT use script or style tags.

        Make it visually appealing.';

    public function getCompletePrompt($json_object, $rules)
    {
        return $this->standardHeader . $json_object . '\n\n' . $rules . $this->standardHtmlFormat . $this->standardFooter . '> ```json';
    }
    public function getOpenAIReport($prompt, $jsonData)
    {

        $client = \OpenAI::client(config('services.openai.key'));

        $result = $client->chat()->create([
            'model' => 'gpt-5',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $this->getCompletePrompt($jsonData, $prompt),
                ],
            ],
            'max_completion_tokens' => 128000,
        ]);
        // Get the output

        $output = $result->choices[0]->message->content ?? '';



        $data['output'] = json_decode($output, true);
        \Log::info('PromptResponse Raw:', ['promptResponse' => $data['output']]);
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
            \Log::info('PromptResponse slashes stripped:', ['promptResponse' => stripslashes($promptResponse)]);
            // $data = str_replace(array("\r\n", "\n", "\r",'\\'), '', $promptResponse);

            // \Log::info('PromptResponse Raw:', ['slashes_stripped' => json_encode(stripslashes($data))]);
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
    }
}
