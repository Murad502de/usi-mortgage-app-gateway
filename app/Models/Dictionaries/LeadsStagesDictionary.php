<?php

namespace App\Models\Dictionaries;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadsStagesDictionary extends Model
{
    use HasFactory;

    protected $fillable = [
        'amo_id',
        'name',
        'leads_pipelines_dictionary_id',
    ];
}
