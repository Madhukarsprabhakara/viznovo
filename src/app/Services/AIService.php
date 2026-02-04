<?php

namespace App\Services;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\json;

class AIService
{

  public $standardHeader = 'You are an analyst or a researcher who has been asked to extract insights from the data which can be pdf or csv file content. \n\n. ';
  public $exampleResponse = '{
  "schema_version": "1.0",
  "user_query": "What are the top 3 issues that respondents have highlighted?",
  "csv_filename": "",
  "chunk_info": {
    "chunk_id": "",
    "records_in_chunk": "",
    "record_offset": "",
    "done": ""
  },
  "selected_columns": [
    "Response ID",
    "Are there any issues, even minor ones, that you would like to change about our service?",
    "How significant are the impact(s) of the issue(s) right now?"
  ],
  "issue_taxonomy": [
    "cm_communication",
    "camera_setup_or_notifications",
    "tablet_device_problem",
    "geocomm_usage",
    "reporting_visibility",
    "scheduling_change",
    "none"
  ],
  "cumulative_issue_counts_in": {
    "cm_communication": "",
    "camera_setup_or_notifications": "",
    "tablet_device_problem": "",
    "geocomm_usage": "",
    "reporting_visibility": "",
    "scheduling_change": "",
    "none": "",
    "total_records_processed": ""
  },
  "chunk_issue_counts": {
    "cm_communication": "",
    "camera_setup_or_notifications": "",
    "tablet_device_problem": "",
    "geocomm_usage": "",
    "reporting_visibility": "",
    "scheduling_change": "",
    "none": ""
  },
  "evidence_samples": {
    "cm_communication": [
      {
        "response_id": "...",
        "excerpt": "..."
      }
    ],
    "camera_setup_or_notifications": [
      {
        "response_id": "...",
        "excerpt": "..."
      }
    ],
    "tablet_device_problem": [
      {
        "response_id": "...",
        "excerpt": "..."
      }
    ]
  },
  "cumulative_issue_counts_out": {
    "cm_communication": "",
    "camera_setup_or_notifications": "",
    "tablet_device_problem": "",
    "geocomm_usage": "",
    "reporting_visibility": "",
    "scheduling_change": "",
    "none": "",
    "total_records_processed": ""
  },
  "top_issues_so_far": [
    {
      "issue": "",
      "count": ""
    },
    {
      "issue": "",
      "count": ""
    },
    {
      "issue": "",
      "count": ""
    }
  ]
}';
  // public $standardBatchHeader ='You are an engineer that needs to determine the json response structure based on the data and user query provided. \n\n. Since this is a large csv file, you will be provided data in a batch request with multiple requests. \n\n Each request is going to be responded to with the same json structure that will be identified by you in this request. \n\n Subsequently, responses to requests will be processed independently to extract relevant insights based on the user query. \n\n After processing all requests within the batch, the response from individual requests will be consolidated to create a final report that answers the user query. \n\n. Here is the sample data from the csv file in json format and the user query: \n\n';
  // public $standardBatchHeader = 'You are supposed to answer the user query. \n\n You are being provided only 25 records out of 10000 records exisitng in the csv. \n\n.   To accurately answer the user query all 10000 records need to be processed. \n\n Therefore,  Identify response json  structure based on sample csv data and user query provided so that analysis can happen incrementally using multiple requests. \n\n. All requests will follow the same response structure that you identify . \n\n Subsequently, the responses from individual requests will be consolidated to accurately answer the use query. \n\n. Do not analyze the data provided in this request. \n\n Generate just the json response structure. \n\n It should be a valid json structure';
  public $standardBatchHeader = 'List all the steps you would take to accurately answer user query based on the data provided \n\n. Dont analyze data, just list steps in bullet points. Here is the data and the user query: \n\n';
  public $standardBatchFooter = 'Give your response in the "prompt_response" key in the json object shown below. 




                    [
                        {
                            "prompt_response": ""
                            
                        }
                    ]
                        
                 

                     ' . '\n\n' . 'DO NOT return any line breaks such as \n or \r in the response.

        DO NOT return escaped characters such as \", \', \\ etc.\n\n';
  public $standardFooter = 'Give your response in the "prompt_response" key in the json object shown below. \n\nDo not give additional explanation outside of the json object. \n\n Do not return array in prompt_response key \n\n




                    [
                        {
                            "prompt_response": ""
                            
                        }
                    ]
                        
                 

                     ' . '\n\n' .

    'here is the data in json format' . '\n\n';

  public $standardHtmlFormat = 'Provide me a well designed HTML page.

        I am using tailwindcss. 

        Do not use any other css.

        DO NOT give me code fence. Just the HTML  should be returned.

        DO NOT return any line breaks such as \n or \r in the html code.

        DO NOT return escaped characters such as \", \', \\ etc.

        DO NOT use script or style tags.

        Make it visually appealing.';

  public function getCompletePrompt($json_object, $rules)
  {
    return $this->standardHeader . $json_object . '\n\n' . $rules . $this->standardHtmlFormat . $this->standardFooter . '> ```json';
  }
  public function getCompletePromptWithoutJson($json_object, $rules)
  {
    return $this->standardHeader . $json_object . '\n\n' . $rules . $this->standardHtmlFormat . $this->standardFooter;
  }
  public function getCompleteBatchPrompt($json_object, $rules)
  {
    return $this->standardBatchHeader . $json_object . '\n\n' . $rules . $this->standardBatchFooter . '\n\n';
  }
  public function getOpenAIReport($prompt, $jsonData)
  {
    //call the right model
    return $this->getOpenAI($prompt, $jsonData);
  }
  public function getGeminiAI($prompt, $jsonData)
  {
    $gemini_key = config('services.gemini.key');
    try {
      $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-pro-preview:generateContent';

      $payload = [
        'contents' => [
          [
            'parts' => [
              [
                'text' => $this->getCompletePromptWithoutJson($jsonData, $prompt),
              ],
            ],
          ],
        ],
      ];

      $response = Http::withHeaders([
        'x-goog-api-key' => $gemini_key,
        'Content-Type' => 'application/json',
      ])->timeout(1200)->post($url, $payload);

      if ($response->failed()) {
        return [
          'status' => 'error',
          'message' => 'Gemini API request failed.',
          'data' => $response->body(),
          'status_code' => $response->status(),
        ];
      }

      $output = json_encode($response->json());
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
      if (empty($data['output']) || !isset($data['output']['candidates'][0]['content']['parts'][0]['text'])) {
        \Log::info('JSON Error:', ['error' => 'Output array is empty or prompt_response key does not exist']);
        exit;
      }

      $decoded_data = json_decode($data['output']['candidates'][0]['content']['parts'][0]['text']);


      return [
        'status' => 'success',
        'message' => 'Response generated successfully.',
        'data' => stripslashes($decoded_data[0]->prompt_response),
      ];
    } catch (\Exception $e) {
      return $e->getMessage();
    }
  }
  public function getOpenAI($prompt, $jsonData)
  {
    try {
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
    } catch (\Exception $e) {
      return $e->getMessage();
    }
  }
  public function getOpenAIBatch($prompt, $jsonData)
  {
    try {
      $client = \OpenAI::client(config('services.openai.key'));

      $result = $client->chat()->create([
        'model' => 'gpt-5',
        'messages' => [
          [
            'role' => 'user',
            'content' => $this->getCompleteBatchPrompt($jsonData, $prompt),
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
          'data' =>  stripslashes($promptResponse),
        ];
      }




      return [
        'status' => 'fail',
        'message' => 'Unable to parse AI response into an array.',
        'data' => null,
      ];
    } catch (\Exception $e) {
      return [
        'status' => 'success',
        'message' => 'Response generated successfully.',
        'data' =>  $e->getMessage(),
      ];
      \Log::error('JSON Encoding Error:', ['error' => $e->getMessage()]);
      throw $e;
    }
  }
}
