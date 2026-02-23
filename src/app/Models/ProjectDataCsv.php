<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDataCsv extends Model
{
    //
    protected $guarded = [];

    public function csvDataType()
    {
        return $this->hasOne(CsvDataType::class, 'id', 'csv_data_type_id' );
    }
}
