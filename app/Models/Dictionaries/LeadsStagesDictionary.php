<?php

namespace App\Models\Dictionaries;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\generateUuid;

class LeadsStagesDictionary extends Model
{
    use HasFactory;
    use generateUuid;

    protected $fillable = [
        'amo_id',
        'name',
        'leads_pipelines_dictionary_id',
        'uuid',
    ];
    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'leads_pipelines_dictionary_id',
    ];

    public function pipeline()
    {
        return $this->belongsTo(LeadsPipelinesDictionary::class);
    }
}
