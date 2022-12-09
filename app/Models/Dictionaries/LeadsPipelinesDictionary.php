<?php

namespace App\Models\Dictionaries;

use App\Models\Services\amoCRM;
use App\Services\amoAPI\amoAPIHub;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\generateUuid;

// use Illuminate\Support\Facades\Log;

class LeadsPipelinesDictionary extends Model
{
    use HasFactory;
    use generateUuid;

    protected $fillable = [
        'amo_id',
        'name',
        'uuid',
    ];
    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    public static function fetchPipelines()
    {
        // Log::info(__METHOD__); //DELETE

        $amoAPI = new amoAPIHub(amoCRM::getAuthData());

        $response = $amoAPI->fetchPipelines();

        if ($response) {
            foreach (self::all() as $pipeline) {
                $pipeline->delete();
            }

            foreach ($response['body']['_embedded']['pipelines'] as $pipeline) {
                // Log::info(__METHOD__, [$pipeline['id'] . ' : ' . $pipeline['name']]); //DELETE

                $createdPipeline = self::create([
                    'amo_id' => (int) $pipeline['id'],
                    'name'   => $pipeline['name'],
                ]);

                // Log::info(__METHOD__, ['createdPipeline']); //DELETE
                // Log::info(__METHOD__, [json_encode($createdPipeline)]); //DELETE

                foreach ($pipeline['_embedded']['statuses'] as $stage) {
                    // Log::info(__METHOD__, [$stage['id'] . ' : ' . $stage['name'] . ' : ' . $stage['pipeline_id']]); //DELETE

                    LeadsStagesDictionary::create([
                        'amo_id'                        => (int) $stage['id'],
                        'name'                          => $stage['name'],
                        'leads_pipelines_dictionary_id' => (int) $createdPipeline->id,
                    ]);
                }
            }
        }
    }

    public function stages()
    {
        return $this->hasMany(LeadsStagesDictionary::class);
    }
}
