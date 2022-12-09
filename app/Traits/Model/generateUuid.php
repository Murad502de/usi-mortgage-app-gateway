<?php

namespace App\Traits\Model;

use Ramsey\Uuid\Uuid;

trait generateUuid
{
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }
}
