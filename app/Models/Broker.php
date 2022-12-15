<?php

namespace App\Models;

use App\Traits\Model\generateUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broker extends Model
{
    use HasFactory;
    use generateUuid;

    protected $fillable = [
        'uuid',
        'amo_user_ids',
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
    public function updateBroker(array $broker)
    {
        $mortgage = Mortgage::getByUuid($broker['mortgage_uuid']);

        return self::update(array_merge($broker, [
            'mortgage_id'  => $mortgage ? $mortgage->id : null,
            'amo_user_ids' => json_encode($broker['amo_user_ids']),
        ]));
    }
    public static function createBroker(array $broker): ?Broker
    {
        $mortgage = Mortgage::getByUuid($broker['mortgage_uuid']);

        return self::create(array_merge($broker, [
            'mortgage_id'  => $mortgage ? $mortgage->id : null,
            'amo_user_ids' => json_encode($broker['amo_user_ids']),
        ]));
    }
}
