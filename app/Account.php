<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'account';

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
