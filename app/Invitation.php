<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    public static function findByCode($code)
    {
    	return static::where('code', $code)->first();
    }
}
