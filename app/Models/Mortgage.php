<?php

namespace App\Models;

use App\Traits\Model\generateUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mortgage extends Model
{
    use HasFactory;
    use generateUuid;

    protected $fillable = [
        'uuid',
        'amo_mortgage_id',
        'amo_mortgage_creation_stage_id',
        'amo_mortgage_applying_stage_id',
        'amo_mortgage_before_applying_stage_ids',
        'amo_mortgage_after_applying_stage_ids',
        'amo_mortgage_approved_stage_id',
    ];
    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function pipelines()
    {
        return $this->hasMany(Pipeline::class);
    }

    public static function createMortgage(array $mortgage): Mortgage
    {
        return self::create(array_merge($mortgage, [
            'amo_mortgage_before_applying_stage_ids' => json_encode($mortgage['amo_mortgage_before_applying_stage_ids']),
            'amo_mortgage_after_applying_stage_ids'  => json_encode($mortgage['amo_mortgage_after_applying_stage_ids']),
        ]));
    }
}
