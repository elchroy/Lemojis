<?php

namespace Elchroy\Lemojis\Models;

use Elchroy\Lemojis\Models\LemojisUser as User;
use Illuminate\Database\Eloquent\Model as Eloquent;

class LemojisUser extends Eloquent
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
     * Deactivate timstamps.
     */
    public $timestamps = [];
}
