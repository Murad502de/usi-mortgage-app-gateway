<?php

namespace App\Models;

use App\Traits\Model\generateUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;
    use generateUuid;

    protected $fillable = [
        'uuid',
        'amo_id',
        'amo_status_id',
        'amo_pipeline_id',
        'is_mortgage',
        'lead_id',
    ];
    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    /* ENTITY RELATIONS */
    public function lead()
    {
        return $this->hasOne(Lead::class);
    }

    /* CRUD METHODS */
    public static function createLead(array $lead): ?Lead
    {
        return self::create(array_merge($lead, []));
    }
    public static function getByUuid(string $uuid): ?Lead
    {
        return self::whereUuid($uuid)->first();
    }
    public function updateLead(array $lead)
    {
        return $this->update(array_merge($lead, []));
    }
}
