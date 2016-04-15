<?php

namespace Elchroy\Lemojis\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class LemojisModel extends Eloquent
{
    /**
     * Define the table to be used for the emojis.
     */
    public $table = 'lemojis';

    /**
     * Define all the fillable properties of an emoji.
     */
    protected $fillable = ['name', 'chars', 'keywords', 'category', 'date_created', 'date_modified', 'created_by'];

    /**
     * Deactivate the timstamps for the emoji.
     * Timestamps have already been set up as properties of the emoji.
     */
    public $timestamps = [];
}
