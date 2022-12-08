<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    private const GOLD_LABEL = '85';

    protected $fillable = [
        'number',
    ];
    protected $hidden = [
        'id',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    /* FUNCTIONS */
    public static function isGold(string $cardNumber): bool
    {
        return substr($cardNumber, -2) === self::GOLD_LABEL;
    }
}
