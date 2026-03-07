<?php

namespace App\Services;


class CsvQualitativeDataInsights
{
    

    public function createJobsFromOpenEndedResponseChunks()
    {
        //get all the open ended responses for a project
        //chunk them into 50 records
        //create a job for each chunk to process the insights
    }
    public function processQualitativeData($projectDataId, $reportId, $allRecords)
    {
        //process 50 records at a time
        //incrementally add the qualitative results to the json
        //store the json records
        //return json insights
    }
    
}