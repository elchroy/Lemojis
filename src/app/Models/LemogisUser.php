<?php

namespace Elchroy\Lemogis\Models;

use Elchroy\Lemogis\LemogisModel as Emogis;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Elchroy\Lemogis\Models\LemogisUser as User;

class LemogisUser extends Eloquent
{

    protected $fillable = ['username', 'password'];

    public $table = 'users';

    public $timestamps = [];

    public function emogis()
    {
        return $this->hasMany('Emogis');
    }
}