<?php

namespace App\Models;

use App\Traits\Model\generateUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pipeline extends Model
{
    use HasFactory;
    use generateUuid;

    protected $fillable = [
        'uuid',
        'amo_pipeline_id',
        'amo_pipeline_booking_stage_id',
        'mortgage_id',
    ];
    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function mortgage()
    {
        return $this->belongsTo(Mortgage::class);
    }
    public function updatePipeline(array $pipeline)
    {
        $mortgage = Mortgage::getByUuid($pipeline['mortgage_uuid']);

        return $this->update(array_merge($pipeline, [
            'mortgage_id' => $mortgage ? $mortgage->id : null,
        ]));
    }
    public static function createPipeline(array $pipeline): ?Pipeline
    {
        $mortgage = Mortgage::getByUuid($pipeline['mortgage_uuid']);

        return self::create(array_merge($pipeline, [
            'mortgage_id' => $mortgage ? $mortgage->id : null,
        ]));
    }
}
