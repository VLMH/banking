<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'user';
    protected $fillable = ['hkid'];

    public function accounts()
    {
        return $this->hasMany('App\Account');
    }

    public function activeAccounts()
    {
        return $this->accounts()->whereNull('deleted_at');
    }
}
