<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Account extends Model
{
    use SoftDeletes;

    const CURRENCY_MINOR_UNIT = 100;
    const TRANSFER_SERVICE_FEE = 100; // in currency unit
    const DAILY_TRANSFER_LIMIT = 10000; // in currency unit

    protected $table = 'account';

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getBalanceAttribute()
    {
        return $this->toCurrencyUnit($this->attributes['balance']);
    }

    public function getTransferQuotaAttribute()
    {
        $transferedAt = $this->attributes['last_transfered_at'];

        return ($transferedAt && $transferedAt->gte(Carbon::today()))
        ? $this->toCurrencyUnit($this->attributes['transfer_quota'])
        : self::DAILY_TRANSFER_LIMIT;
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

    public function isEnoughTransferQuota($amount)
    {
        return $this->transfer_quota >= $amount;
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
