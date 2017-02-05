<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    const CURRENCY_MINOR_UNIT = 100;

    protected $table = 'account';

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function balance()
    {
        return $this->balance / self::CURRENCY_MINOR_UNIT;
    }
}
