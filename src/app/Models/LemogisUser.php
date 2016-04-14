<?php

namespace Elchroy\Lemogis\Models;

use Elchroy\Lemogis\LemogisModel as Emogis;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Elchroy\Lemogis\Models\LemogisUser as User;

class LemogisUser extends Eloquent
{
    /**
     * Define the fillable properties of a user.
     */
    protected $fillable = ['username', 'password'];

    /**
     * Define the database table for users.
     */
    public $table = 'users';

    /**
     * Deactivate timstamps
     */
    public $timestamps = [];

}