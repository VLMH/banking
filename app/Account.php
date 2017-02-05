<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

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

    public function deposit($amount)
    {
        $this->attributes['balance'] += $this->toMinorUnit($amount);
        return $this;
    }

    private function toMinorUnit($number)
    {
        return (int)floor($number * self::CURRENCY_MINOR_UNIT);
    }
}
