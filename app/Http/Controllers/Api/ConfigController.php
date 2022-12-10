<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ConfigController extends Controller
{
    public function leadcard()
    {
        return [
            'mortgage_form_id' => 1234567890,
        ];
    }
}
