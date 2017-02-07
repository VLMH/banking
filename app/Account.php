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

    public function getBalanceAttribute()
    {
        return $this->toCurrencyUnit($this->attributes['balance']);
    }

    public function deposit($amount)
    {
        $this->attributes['balance'] += $this->toMinorUnit($amount);
        return $this;
    }

    public function canWithdraw($amount)
    {
        return $this->attributes['balance'] >= $this->toMinorUnit($amount);
    }

    public function withdraw($amount)
    {
        if (!$this->canWithdraw($amount)) {
            throw new \Exception('Not enough balance');
        }

        return $this->deposit(-$amount);
    }

    public function isSameOwner(Account $account)
    {
        return $this->user_id == $account->user_id;
    }

    private function toMinorUnit($number)
    {
        return (int)floor($number * self::CURRENCY_MINOR_UNIT);
    }

    private function toCurrencyUnit($number)
    {
        return (float)($number / self::CURRENCY_MINOR_UNIT);
    }
}
